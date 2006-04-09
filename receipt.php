<?php
// Event Receiption for Poster
// $Id: receipt.php,v 1.15 2006/04/09 17:31:33 nobu Exp $

include 'header.php';
require 'perm.php';

$op = param('op', 'view');
$rvid=param('rvid');
$eid= param('eid');
$exid= param('sub');
$myts =& MyTextSanitizer::getInstance();
$nlab = $xoopsModuleConfig['label_persons'];

if ($rvid) {
    if ($op=='view') $op = 'one';
    $result = $xoopsDB->query('SELECT r.*, optfield FROM '.RVTBL.' r, '.OPTBL." o WHERE rvid=$rvid AND r.eid=o.eid");
    if (!$result || $xoopsDB->getRowsNum($result)==0) {
	redirect_header(XOOPS_URL.'/modules/eguide/', 2, _NOPERM);
	exit;
    }
    $data = $xoopsDB->fetchArray($result);
    $eid = $data['eid'];
    $exid = $data['eid'];
    $status = intval($_POST['status']);
    $email = $xoopsDB->quoteString(post_filter($_POST['email']));
    $info = $xoopsDB->quoteString(post_filter($_POST['info']));
    if ($op=='save') {
	$vals = explodeinfo($data['info'], $data['optfield']);
	$num = ($data['status']!=_RVSTAT_REFUSED)?
	     (isset($vals[$nlab])?$vals[$nlab]:1):0;
	$xoopsDB->query("UPDATE ".RVTBL." SET email=$email, status=$status, info=$info WHERE rvid=$rvid");
	$vals = explodeinfo($data['info'], $data['optfield']);
	$nnum = ($status!=_RVSTAT_REFUSED)?
	     (isset($vals[$nlab])?$vals[$nlab]:1):0;
	update_reserv($eid, $exid, $nnum-$num);
	redirect_header("receipt.php?op=one&rvid=$rvid",2,_MD_DBUPDATED);
	exit;
    }
    $backurl = '<a href="receipt.php?eid='.$data['eid'].($data['exid']?'&sub='.$data['exid']:'').'">'._MD_RESERV_RETURN.'</a>';
}

$result = $xoopsDB->query("SELECT * FROM ".OPTBL." WHERE eid=$eid");
$opts = $xoopsDB->fetchArray($result);

$result = $xoopsDB->query("SELECT IF(exdate,exdate,edate) edate, title, uid, summary, cdate, counter FROM ".EGTBL.
			  ' e LEFT JOIN '.EXTBL.
			  " x ON eid=eidref AND exid=$exid WHERE eid=$eid");
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

$result = $xoopsDB->query("SELECT IF(x.reserved,x.reserved,o.reserved) FROM ".
EGTBL.' e LEFT JOIN '.OPTBL.' o ON e.eid=o.eid LEFT JOIN '.EXTBL." x ON e.eid=eidref AND x.exid=$exid WHERE e.eid=$eid");
list($nrsv) = $xoopsDB->fetchRow($result);
$result = $xoopsDB->query("SELECT * FROM ".RVTBL." WHERE eid=$eid AND exid=$exid ORDER BY rvid");
$nrec = $xoopsDB->getRowsNum($result);

// output records in CSV format
$mo = $xoopsModuleConfig['member_only'];
if ($nrec && $op=='csv') {
    // field name
    $out = '"'._MD_ORDER_DATE.'","'.($mo?'':_MD_EMAIL.'","')._MD_UNAME.'"';
    if (count($item)) {
	$out .= ',"'.join('","', preg_replace('/\"/', '""',$item)).'"';
    }
    $out .= "\n";
    // body
    while ($a = $xoopsDB->fetchArray($result)) {
	$out .= '"'.formatTimestamp($a['rdate'], _MD_TIME_FMT).'","'.
	    ($mo?'':$a['email'].'","').
	    XoopsUser::getUnameFromId($a['uid']).'"';
	foreach (explodeinfo($a['info'], $item) as $lab => $v) {
	    if ($v) $v = '"'.preg_replace('/\"/', '""', $v).'"';
	    $out .= ",$v";
	}
	$out .= "\n";
    }

    $file = "eguide_".formatTimestamp(time(),"Ymd").".csv";
    header("Content-Type: text/plain; Charset="._MD_EXPORT_CHARSET);
    header('Content-Disposition:attachment;filename="'.$file.'"');
    if (_MD_EXPORT_CHARSET != _CHARSET) {
	if (function_exists("mb_convert_encoding")) {
	    $out = mb_convert_encoding($out, _MD_EXPORT_CHARSET, _CHARSET);
	} elseif (function_exists("iconv")) {
	    $out = iconv(_MD_EXPORT_CHARSET, $charset, $out);
	}
    }
    echo $out;
    exit;
}

include(XOOPS_ROOT_PATH."/header.php");

if (count($extents)>1) {
    $xoopsTpl->assign('extents', $extents);
}
$xoopsTpl->assign(array('title'=>$title,
			'eid'=>$eid, 'exid'=>$exid));
$evurl = XOOPS_URL."/modules/eguide/event.php?eid=$eid".($exid?"&sub=$exid":"");
switch ($op) {
case 'active':
    $result = $xoopsDB->query('SELECT optfield FROM '.OPTBL.' WHERE eid='.$eid);
    list($optfield) = $xoopsDB->fetchRow($result);
    $labs = explodeopts($optfield);
    $isnum = in_array($nlab, $labs);
    $cnt = 0;
    foreach ($_POST['act'] as $i) {
	$rvid = intval($i);
	$yesno = param('yesno');
	$result = $xoopsDB->query("SELECT * FROM ".RVTBL." WHERE rvid=$rvid AND status="._RVSTAT_ORDER);
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
		$uinfo = sprintf("%s: %s (%s)\n", _MD_UNAME,
				 $user->getVar('uname'),
				 $user->getVar('name'));
		$xoopsMailer->setToUsers($user);
	    } else {
		$xoopsMailer->setToEmails($data['email']);
		$uinfo = sprintf("%s: %s\n", _MD_EMAIL, $email);
	    }
	    $xoopsMailer->assign('REQ_UNAME', $xoopsUser->getVar('uname'));
	    $xoopsMailer->assign('REQ_NAME', $xoopsUser->getVar('name'));
	    $xoopsMailer->setFromName(_MD_FROM_NAME);
	    $xoopsMailer->assign("INFO", $uinfo.$data['info']);
	    $curl = XOOPS_URL."/modules/eguide/reserv.php?op=cancel&rvid=$rvid&key=".$data['confirm'];
	    $xoopsMailer->assign('RVID', $rvid);
	    $xoopsMailer->assign('CANCEL_URL', $curl);
	    if ($yesno==_RVSTAT_RESERVED) {
		$ret = _MD_RESERV_ACTIVE;
	    } else {
		$ret = _MD_RESERV_REFUSE;
		if ($isnum) {
		    $vals = explodeinfo($data['info'], $optfield);
		    $num = intval($vals[$nlab]);
		    if ($num<1) $num=1;
		    $cnt -= $num;
		} else $cnt--;
		if ($xoopsModuleConfig['use_plugins']) {
		    include_once 'plugins.php';
		    foreach ($hooked_function['cancel'] as $func) {
			if (!$func($data['eid'], $data['exid'], $data['uid'], $head['uid'])) {
			    echo "Cancel failed";
			}
		    }
		}
	    }
	    $xoopsMailer->assign("RESULT", $ret);
	    if ($xoopsMailer->send()) {
		$xoopsDB->query("UPDATE ".RVTBL." SET status='$yesno' WHERE rvid=$rvid");
		echo "<div>"._MD_INFO_MAILOK."</div>\n";
		echo $xoopsMailer->getSuccess();
	    } else {
		echo "<div>"._MD_INFO_MAILNG."</div>\n";
		echo $xoopsMailer->getErrors();
	    }
	    if ($data['uid']) echo $user->getVar('uname');
	}
    }
    update_reserv($eid, $exid, $cnt);
    echo "<p><a href='$evurl'>$title</a></p>\n";
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
    
    $xoopsOption['template_main'] = 'eguide_confirm.html';
    $xoopsTpl->assign('lang_title', _MD_RESERV_REC);
    $edit = "<a href='receipt.php?op=edit&rvid=$rvid'>"._EDIT."</a>";
    $del ="<a href='reserv.php?op=cancel&rvid=$rvid&back='>"._MD_RESERV_DEL."</a>";
    $values=array(_MD_RVID => "$rvid &nbsp; [$edit] &nbsp; [$del]",
		  _MD_ORDER_DATE => formatTimestamp($data['rdate'], _MD_TIME_FMT));
    if (!$mo) $values[_MD_EMAIL] = $myts->makeTareaData4Edit($data['email']);
    $values[_MD_STATUS] = $rv_stats[$data['status']];
    foreach (explodeinfo($data['info'], $data['optfield']) as $lab => $v) {
	if (empty($v)) $v = '&nbsp;';
	$values[$lab] = $myts->displayTarea($v);
    }
    $xoopsTpl->assign('xoops_module_header', HEADER_CSS);
    edit_eventdata($head);
    $xoopsTpl->assign('event', edit_eventdata($head));
    $xoopsTpl->assign('values', $values);
    $xoopsTpl->assign('form', $backurl);
    break;

default:
    $xoopsOption['template_main'] = 'eguide_receipt.html';

    $xoopsTpl->assign('lang_title', _MD_RESERVATION);

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
			    'labels'=>array_merge($mo?_MD_UNAME:_MD_EMAIL,
						  array_slice($item, 0, $max)),
			    'reserv_msg'=>$mailmsg,
			    'operations'=>
			    array(_RVSTAT_RESERVED=>_MD_ACTIVATE,
				  _RVSTAT_REFUSED =>_MD_REFUSE),
			    ));


    $citem = $list = $nitem = array();
    $confirm = 0;
    while ($order = $xoopsDB->fetchArray($result)) {
	$order['confirm']= $cf = ($order['status']==_RVSTAT_ORDER);
	if ($cf) $confirm++;
	$order['date'] = formatTimestamp($order['rdate'], _MD_TIME_FMT);
	$order['stat']=$rv_stats[$order['status']];
	$add=array();
	$ok = $order['status']==_RVSTAT_RESERVED;
	foreach (explodeinfo($order['info'], $item) as $lab => $v) {
	    if ($ok) {
		if (isset($nitem[$lab])) {
		    if ($nitem[$lab]!="") {
			if (preg_match('/^-?\d+$/', $v)) {
			    $nitem[$lab] += $v;
			} else {
			    $nitem[$lab] = ""; // include not numeric
			}
		    }
		} else {
		    $nitem[$lab] = preg_match('/^-?\d+$/', $v)?$v:"";
		}
		if ($v && isset($mc[$lab])) {
		    $mv = ($mc[$lab]=='checkbox')?explode(",",$v):array($v);
		    foreach ($mv as $i) {
			if (empty($i)) continue;
			$x="$lab/$i";
			if (isset($citem[$x])) $citem[$x]++;
			else $citem[$x]=1;
		    }
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
    $sl='('._MD_SUM.')';
    foreach ($nitem as $k => $v) {
	foreach (preg_grep('/^'.preg_quote($k.'/', '/').'\d+$/',
			   array_keys($citem)) as $ki) {
	    unset($citem[$ki]);
	}
	if ($v!="") $citem[$k.$sl] = $v;
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

function update_reserv($eid, $exid, $num) {
    global $xoopsDB;
    if ($num==0) return;
    if ($exid) {
	$xoopsDB->query("UPDATE ".EXTBL." SET reserved=reserved+($num) WHERE exid=$exid");
    } else {
	$xoopsDB->query("UPDATE ".OPTBL." SET reserved=reserved+($num) WHERE eid=$eid");
    }
}
?>