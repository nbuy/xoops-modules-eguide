<?php
// Event Reciption for Poster
// $Id: receipt.php,v 1.14 2005/12/27 05:13:53 nobu Exp $

include 'header.php';
require 'perm.php';

$op = param('op', 'view');
$rvid=param('rvid');
$eid= param('eid');
$exid= param('sub');
$myts =& MyTextSanitizer::getInstance();

if ($rvid) {
    if ($op=='view') $op = 'one';
    $result = $xoopsDB->query('SELECT * FROM '.RVTBL." WHERE rvid=$rvid");
    $data = $xoopsDB->fetchArray($result);
    $eid = $data['eid'];
    $status = intval($_POST['status']);
    $email = $xoopsDB->quoteString(post_filter($_POST['email']));
    $info = $xoopsDB->quoteString(post_filter($_POST['info']));
    if ($op=='save') {
	$xoopsDB->query("UPDATE ".RVTBL." SET email=$email, status=$status, info=$info WHERE rvid=$rvid");
	update_reserv($eid, $exid);
	redirect_header("receipt.php?op=one&rvid=$rvid",2,_MD_DBUPDATED);
	exit;
    }
    $backurl = '<a href="receipt.php?eid='.$data['eid'].($data['exid']?'&amp;sub='.$data['exid']:'').'">'._MD_RESERV_RETURN.'</a>';
}

$result = $xoopsDB->query("SELECT * FROM ".OPTBL." WHERE eid=$eid");
$opts = $xoopsDB->fetchArray($result);

$result = $xoopsDB->query("SELECT edate, title, uid FROM ".EGTBL." WHERE eid=$eid");
$head = $xoopsDB->fetchArray($result);
$edate = $head['edate'];
if ($exid) {
    $extents = array();
    $result = $xoopsDB->query("SELECT exdate FROM ".EXTBL." WHERE exid=$exid");
    list($edate) = $xoopsDB->fetchRow($result);
} else {
    $extents = get_extents($eid, true);
}

$title = eventdate($edate)." ".htmlspecialchars($head['title']);
$poster = new XoopsUser($head['uid']);

if (empty($op)) $op = 'view';
$print = $op=='print';

// make optional field and countable list.
if ($eid) {
    $result = $xoopsDB->query("SELECT optfield FROM ".OPTBL." WHERE eid=$eid");
    $opts = $xoopsDB->fetchArray($result);
    $item = array();
    foreach (explode("\n",preg_replace('/\r/','',$opts['optfield'])) as $ln) {
	// comment line
	if (preg_match('/^\s*#/', $ln)||preg_match('/^\s*$/', $ln)) continue;
	$fld = explode(",", $ln);
	$lab = preg_replace('/^!\s*/', '',
			    preg_replace('/[\*#]$/', "", array_shift($fld)));
	$type = isset($fld[0])?strtolower($fld[0]):false;
	if ($type=="checkbox" || $type=="radio" || $type=="select") {
	    $mc[$lab]=$type;	// it's countable
	}
	$item[] =  $lab;
    }
}

$result = $xoopsDB->query("SELECT count(rvid) FROM ".RVTBL." WHERE eid=$eid AND exid=$exid AND status="._RVSTAT_RESERVED);
list($nrsv) = $xoopsDB->fetchRow($result);
$result = $xoopsDB->query("SELECT * FROM ".RVTBL." WHERE eid=$eid AND exid=$exid ORDER BY rvid");
$nrec = $xoopsDB->getRowsNum($result);

// output records in CSV format
if ($nrec && $op=='csv') {
    // force charset SJIS in Japanese Windows
    $charset = $xoopsConfig['language']=='japanese'?"Shift_JIS":_CHARSET;
    if (isset($_GET['charset'])) {
	$charset = $_GET['charset'];
	if ($charset != _CHARSET) $charset = "Shift_JIS";
    }
    // field name
    $out = '"'._MD_ORDER_DATE.'","'._MD_EMAIL.'","UNAME"';
    if (count($item)) {
	$out .= ',"'.join('","', preg_replace('/\"/', '""',$item)).'"';
    }
    $out .= "\n";
    // body
    while ($a = $xoopsDB->fetchArray($result)) {
	$out .= '"'.formatTimestamp($a['rdate'], _MD_TIME_FMT).'","'.
	    $a['email'].'","'.XoopsUser::getUnameFromId($a['uid']).'"';
	foreach (explodeinfo($a['info'], $item) as $lab => $v) {
	    if ($v) $v = '"'.preg_replace('/\n/', '\n', (preg_replace('/\"/', '&quot;', $v))).'"';
	    $out .= ",$v";
	}
	$out .= "\n";
    }

    $file = "eguide_".formatTimestamp(time(),"Ymd").".csv";
    header("Content-Type: text/plain; Charset=$charset");
    header('Content-Disposition:attachment;filename="'.$file.'"');
    if (XOOPS_USE_MULTIBYTES && $charset != _CHARSET) {
	if (function_exists("mb_convert_encoding")) {
	    $out = mb_convert_encoding($out, $charset, _CHARSET);
	} elseif (function_exists("iconv")) {
	    $out = iconv(_CHARSET, $charset, $out);
	}
    }
    echo $out;
    exit;
}

include(XOOPS_ROOT_PATH."/header.php");

if (count($extents)>1) {
    $xoopsTpl->assign('extents', $extents);
}
$xoopsTpl->assign(array('lang_reserv_list'=>$title,
			'eid'=>$eid, 'exid'=>$exid));
switch ($op) {
case 'active':
    foreach ($_POST['act'] as $i) {
	$rvid = intval($i);
	$yesno = param('yesno');
	$result = $xoopsDB->query("SELECT * FROM ".RVTBL." WHERE rvid=$rvid");
	$data = $xoopsDB->fetchArray($result);
	if ($data) {
	    $msg = param('msg', '');
	    $xoopsMailer =& getMailer();
	    $xoopsMailer->useMail();
	    $xoopsMailer->setSubject("Re: ".$title);
	    $xoopsMailer->setBody($msg);
	    $xoopsMailer->setFromEmail($poster->email());
	    if ($data['uid']) {
		$user = new XoopsUser($data['uid']);
		$xoopsMailer->setToUsers($user);
		$xoopsMailer->assign("ORDER_MAIL", $user->getVar('email'));
	    } else {
		$xoopsMailer->setToEmails($data['email']);
		$xoopsMailer->assign("ORDER_MAIL", $data['email']);
	    }
	    $xoopsMailer->setFromName(_MD_FROM_NAME);
	    $xoopsMailer->assign("INFO", $data['info']);
	    $xoopsMailer->assign("RESULT", $yesno==1?
				 _MD_RESERV_ACTIVE:_MD_RESERV_REFUSE);
	    if ($xoopsMailer->send()) {
		$xoopsDB->query("UPDATE ".RVTBL." SET status='$yesno' WHERE rvid=$rvid");
		echo "<div>"._MD_INFO_MAILOK."</div>\n";
		echo $xoopsMailer->getSuccess();
	    } else {
		echo "<div>"._MD_INFO_MAILNG."</div>\n";
		echo $xoopsMailer->getErrors();
	    }
	}
    }
    update_reserv($eid, $exid);
    break;

case 'edit':
    echo "<h4>"._MD_RESERV_EDIT."</h4>";
    echo "<form action='receipt.php' method='post'>\n";
    echo "<input type='hidden' name='op' value='save' />\n";
    echo "<input type='hidden' name='rvid' value='$rvid' />\n";
    echo "<input type='hidden' name='eid' value='$eid' />\n";
    echo "<table class='outer'>\n";
    echo "<tr><th align='left'>"._MD_RVID."</th><td class='even'>$rvid</td></tr>\n";
    echo "<tr><th align='left'>"._MD_ORDER_DATE."</th><td class='odd'>".formatTimestamp($data['rdate'], _MD_TIME_FMT)."</td></tr>\n";
    echo "<tr><th align='left'>"._MD_EMAIL."</th><td class='even'><input size='40' name='email' value='".$data['email']."' /></td></tr>\n";
    echo "<tr><th align='left'>"._MD_STATUS."</th><td class='odd'>\n";
    $s = $data['status'];
    echo "<select name='status'>\n";
    foreach ($rv_stats as $i => $v) {
	$ck = ($i==$s)?" selected":"";
	echo "<option value='$i'$ck>$v</option>\n";
    }
    echo "</select></td></tr>\n";
    echo "<tr><th align='left'>"._MD_RESERV_ITEM."</th><td class='even'>\n";
    echo "<textarea name='info' cols='40' rows='5'>".
	htmlspecialchars($data['info'])."</textarea>\n";
    echo "</td></tr>\n";
    echo "<tr><th></th><td class='odd'><input type='submit' value='"._MD_SAVECHANGE."' /></td></tr>\n";
    echo "</table>\n</form>\n";
    echo "<p>$backurl</p>\n";
    break;

case 'one':
    echo "<h4>"._MD_RESERV_REC."</h4>";
    $edit = "<a href='receipt.php?op=edit&amp;rvid=$rvid'>"._EDIT."</a>";
    $del ="<a href='reserv.php?op=cancel&amp;rvid=$rvid'>"._MD_RESERV_DEL."</a>";
    echo "<table class='outer'>\n";
    $atr = "align='left'";
    echo "<tr><th $atr>"._MD_RVID."</th><td class='even' nowrap>$rvid &nbsp; [$edit] &nbsp; [$del]</td></tr>\n";
    echo "<tr><th $atr>"._MD_ORDER_DATE."</th><td class='odd'>".formatTimestamp($data['rdate'], _MD_TIME_FMT)."</td></tr>\n";
    echo "<tr><th $atr>"._MD_EMAIL."</th><td class='even'>".$data['email']."</td></tr>\n";
    echo "<tr><th $atr>"._MD_STATUS."</th><td class='odd'>".$rv_stats[$data['status']]."</td></tr>\n";
    foreach (explodeinfo($data['info'], $item) as $lab => $v) {
	if (empty($v)) $v = '&nbsp;';
	echo "<tr><th $atr>$lab</th><td class='even'>".nl2br($v)."</td></tr>\n";
    }
    echo "</table>\n";
    echo "<p>$backurl</p>\n";
    break;

default:
    $xoopsOption['template_main'] = 'eguide_receipt.html';

    $status = 0;
    $pat = $rep = array();
    $pat[] = '{TITLE}';
    $rep[] = $title;
    $pat[] ='{EVENT_URL}';
    $rep[] = XOOPS_URL."/modules/eguide/event.php?eid=$eid".($exid?"&sub=$exid":'');
    $mailmsg = htmlspecialchars(str_replace($pat, $rep, _MD_RESERV_MSG));
    
    $max = $xoopsModuleConfig['max_item'];
    $xoopsTpl->assign(array('order_count'=>$nrec,
			    'reserv_num'=>sprintf(_MD_RESERV_REG,$nrsv),
			    'print_date'=>formatTimestamp(time(), _MD_POSTED_FMT),
			    'labels'=>array_slice($item, 0, $max),
			    'reserv_msg'=>$mailmsg,
			    'operations'=>
			    array(_RVSTAT_RESERVED=>_MD_ACTIVATE,
				  _RVSTAT_REFUSED =>_MD_REFUSE),
			    ));


    $citem = $list = array();
    $confirm = 0;
    while ($order = $xoopsDB->fetchArray($result)) {
	$order['confirm']= $cf = ($order['status']==_RVSTAT_ORDER);
	if ($cf) $confirm++;
	$order['date'] = formatTimestamp($order['rdate'], _MD_TIME_FMT);
	$order['stat']=$rv_stats[$order['status']];
	$add=array();
	foreach (explodeinfo($order['info'], $item) as $lab => $v) {
	    if ($v && isset($mc[$lab])) {
		$mv = ($mc[$lab]=='checkbox')?explode(",",$v):array($v);
		foreach ($mv as $i) {
		    if (empty($i)) continue;
		    $x="$lab/$i";
		    if (isset($citem[$x])) $citem[$x]++;
		    else $citem[$x]=1;
		}
	    }
	    if ($v=="") $v="&nbsp;";
	    if (count($add) < $max) $add[] = $v;
	}
	$order['add'] = $add;
	if ($order['uid']) {
	    $order['uname'] = XoopsUser::getUnameFromId($order['uid']);
	}
	$list[] = $order;
    }
    $xoopsTpl->assign('list', $list);
    $xoopsTpl->assign('confirm', $confirm);
    ksort($citem);
    $xoopsTpl->assign('citem', $citem);
}

if ($print) {
    $xoopsTpl->display('db:eguide_receipt_print.html');
} else {
    include(XOOPS_ROOT_PATH."/footer.php");
}

function update_reserv($eid, $exid) {
    global $xoopsDB;
    $result = $xoopsDB->query("SELECT count(rvid) FROM ".RVTBL." WHERE eid=$eid AND exid=$exid AND status="._RVSTAT_RESERVED);
    list($n) = $xoopsDB->fetchRow($result);
    if ($exid) {
	echo "<div>exid: recount=$n</div>";
	$xoopsDB->query("UPDATE ".EXTBL." SET reserved=$n WHERE exid=$exid");
    } else {
	echo "<div>eid: recount=$n</div>";
	$xoopsDB->query("UPDATE ".OPTBL." SET reserved=$n WHERE eid=$eid");
    }
    echo $xoopsDB->error();
}
?>