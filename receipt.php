<?php
// Event Reciption for Poster
// $Id: receipt.php,v 1.11 2005/11/18 17:08:03 nobu Exp $

include 'header.php';
include 'perm.php';

$op = param('op', 'view');
$rvid=param('rvid');
$eid= param('eid');
$exid= param('sub');

if ($rvid) {
    if ($op=='view') $op = 'one';
    $result = $xoopsDB->query('SELECT * FROM '.RVTBL." WHERE rvid=$rvid");
    $data = $xoopsDB->fetchArray($result);
    $eid = $data['eid'];
    if ($op=='save') {
	$xoopsDB->query("UPDATE ".RVTBL." SET email='$email', status='$status', info='$info' WHERE rvid=$rvid");
	update_reserv($eid);
	$op='one';
    } elseif ($op=='delete') {
	$xoopsDB->query("DELETE FROM ".RVTBL." WHERE rvid=$rvid AND eid=$eid");
	update_reserv($eid);
	redirect_header("receipt.php?eid=$eid",2,_MD_DBUPDATED);
	exit;
    }
    $backurl = '<a href="receipt.php?eid='.$data['eid'].($data['exid']?'&amp;sub='.$data['exid']:'').'">'._MD_RESERV_RETURN.'</a>';
}

$result = $xoopsDB->query("SELECT * FROM ".OPTBL." WHERE eid=$eid");
$opts = $xoopsDB->fetchArray($result);
$extents = get_extents($eid, $exid);

$result = $xoopsDB->query("SELECT edate, title, uid FROM ".EGTBL." WHERE eid=$eid");
$head = $xoopsDB->fetchArray($result);
if (count($extents)==1) $edate = $extents[0]['exdate'];
else $edate = $head['edate'];

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
    foreach (array_keys($_POST) as $i) {
	if (preg_match('/^act\d+$/', $i)) {
	    $rvid = $_POST[$i];
	    $result=$xoopsDB->query("SELECT * FROM ".RVTBL." WHERE rvid=$rvid");
	    $data = $xoopsDB->fetchArray($result);
	    if ($data) {
		$xoopsMailer =& getMailer();
		$xoopsMailer->useMail();
		$xoopsMailer->setSubject("Re: $title");
		$xoopsMailer->setBody($msg);
		$xoopsMailer->setFromEmail($poster->email());
		$xoopsMailer->setFromName("Event Reservation");
		$xoopsMailer->setToEmails($data['email']);
		$xoopsMailer->assign("ORDER_MAIL", $data['email']);
		$xoopsMailer->assign("INFO", $data['info']);
		$xoopsMailer->assign("RESULT", $yesno==1?
				     _AM_RESERV_ACTIVE:_AM_RESERV_REFUSE);
		if ($xoopsMailer->send()) {
		    $xoopsDB->query("UPDATE ".RVTBL." SET status='$yesno' WHERE rvid=$rvid");
		    echo $xoopsMailer->getSuccess();
		} else {
		    echo $xoopsMailer->getErrors();
		}
	    }
	}
    }
    update_reserv($eid);
    break;

case 'edit':
    echo "<h4>"._MD_RESERV_EDIT."</h4>";
    echo "<form action='receipt.php' method='post'>
<table class='evtbl' width='100%'>\n";
    echo "<input type='hidden' name='op' value='save' />\n";
    echo "<input type='hidden' name='rvid' value='$rvid' />\n";
    $atr = "align='left' class='bg1'";
    echo "<tr><th $atr>"._MD_RVID."</th><td class='bg3'>$rvid</td></tr>\n";
    echo "<tr><th $atr>"._MD_ORDER_DATE."</th><td class='bg3'>".formatTimestamp($data['rdate'], _MD_TIME_FMT)."</td></tr>\n";
    echo "<tr><th $atr>"._MD_EMAIL."</th><td class='bg3'><input size='40' name='email' value='".$data['email']."' /></td></tr>\n";
    echo "<tr><th $atr>"._MD_STATUS."</th><td class='bg3'>\n";
    $s = $data['status'];
    echo "<select name='status'>\n";
    foreach ($rv_stats as $i => $v) {
	$ck = ($i==$s)?" selected":"";
	echo "<option value='$i'$ck>$v</option>\n";
    }
    echo "</select></td></tr>\n";
    echo "<tr><th $atr>"._MD_RESERV_ITEM."</th><td class='bg3'>\n";
    echo "<textarea name='info' cols='40' rows='5'>".
	htmlspecialchars($data['info'])."</textarea>\n";
    echo "</td></tr>\n";
    echo "<tr class='bg1'><td colspan='2' align='center'><input type='submit' value='"._MD_SAVECHANGE."' /></td></tr>\n";
    echo "</table>\n</form>\n";
    echo "<p>$backurl</p>\n";
    break;

case 'one':
    echo "<h4>"._MD_RESERV_REC."</h4>";
    $edit = "<a href='receipt.php?op=edit&amp;rvid=$rvid'>"._EDIT."</a>";
    $del ="<form action='receipt.php' method='post'><input type='checkbox' name='op' value='delete' /><input type='hidden' name='rvid' value='$rvid' />"._MD_RESERV_DEL." <input type='submit' value='"._DELETE."' /></form>";
    echo "<table class='evtbl'>\n";
    $atr = "align='left' class='bg1'";
    echo "<tr><th $atr>"._MD_RVID."</th><td class='bg3' nowrap>$rvid [$edit]</td></tr>\n";
    echo "<tr><th $atr>"._MD_ORDER_DATE."</th><td class='bg3'>".formatTimestamp($data['rdate'], _MD_TIME_FMT)."</td></tr>\n";
    echo "<tr><th $atr>"._MD_EMAIL."</th><td class='bg3'>".$data['email']."</td></tr>\n";
    echo "<tr><th $atr>"._MD_STATUS."</th><td class='bg3'>".$rv_stats[$data['status']]."</td></tr>\n";
    foreach (explodeinfo($data['info'], $item) as $lab => $v) {
	if (empty($v)) $v = '&nbsp;';
	echo "<tr><th $atr>$lab</th><td class='bg3'>".nl2br($v)."</td></tr>\n";
    }
    echo "<tr><td class='bg3' colspan='2'>$del</td></tr>\n";
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
    $mailmsg = str_replace($pat, $rep, _MD_RESERV_MSG);
    
    $max = $xoopsModuleConfig['max_item'];
    $xoopsTpl->assign(array('order_count'=>$nrec,
			    'lang_order_count'=>_MD_ORDER_COUNT,
			    'lang_edit_extent'=>_MD_EDIT_EXTENT,
			    'lang_print_date'=>_MD_PRINT_DATE,
			    'print_date'=>formatTimestamp(time(), _MD_POSTED_FMT),
			    'lang_operation'=>_MD_OPERATION,
			    'lang_order_date'=>_MD_ORDER_DATE,
			    'lang_email'=>_MD_EMAIL,
			    'labels'=>array_slice($item, 0, $max),
			    'lang_detail'=>_MD_DETAIL,
			    'lang_extent_date'=>_MD_EXTENT_DATE,
			    'lang_reserv_msg'=>$mailmsg,
			    'lang_reserv_msg_h'=>_MD_RESERV_MSG_H,
			    'lang_submit'=>_SUBMIT,
			    'operations'=>
			    array(_RVSTAT_RESERVED=>_MD_ACTIVATE,
				  _RVSTAT_REFUSED =>_MD_REFUSE),
			    'lang_cvs_out'=>_MD_CSV_OUT,
			    'lang_info_mail'=>_MD_INFO_MAIL,
			    'lang_print'=>_PRINT,
			    'lang_summary'=>_MD_SUMMARY,
			    'lang_sum_item'=>_MD_SUM_ITEM,
			    'lang_sum'=>_MD_SUM,
			    ));


    $citem = $list = array();
    $confirm = 0;
    while ($order = $xoopsDB->fetchArray($result)) {
	$order['confirm']= $cf = ($order['status']==_RVSTAT_ORDER);
	if ($cf) $confirm++;
	$order['date'] = formatTimestamp($order['rdate'], _MD_TIME_FMT);
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
	$xoopsDB->query("UPDATE ".EXTBL." SET reserved=$n WHERE exid=$exid");
    } else {
	$xoopsDB->query("UPDATE ".OPTBL." SET reserved=$n WHERE eid=$eid");
    }
}
?>