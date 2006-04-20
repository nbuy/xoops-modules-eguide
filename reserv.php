<?php
// reservation proceedings.
// $Id: reserv.php,v 1.18 2006/04/20 07:20:47 nobu Exp $
include 'header.php';

$op = param('op', "x");
$rvid = param('rvid');
$key = param('key');
$now=time();
$nlab = $xoopsModuleConfig['label_persons'];
$myts =& MyTextSanitizer::getInstance();

$member_handler =& xoops_gethandler('member');
$notify_group = $member_handler->getGroup($xoopsModuleConfig['notify_group']);

if ($xoopsModuleConfig['member_only'] && !is_object($xoopsUser)) {
    redirect_header(XOOPS_URL."/user.php",2,_NOPERM);
    exit;
}

$isadmin = is_object($xoopsUser) && $xoopsUser->isAdmin($xoopsModule->getVar('mid'));

function reserv_permit($ruid, $euid, $confirm) {
    global $xoopsUser, $xoopsModule, $xoopsModuleConfig, $isadmin;
    if (!is_object($xoopsUser)) {
	if ($xoopsModuleConfig['member_only']) return false;
	return $confirm==param('key');
    }
    // administrator has permit
    if ($isadmin) return true;
    if ($xoopsModuleConfig['member_only']) {
	$uid = $xoopsUser->getVar('uid');
	// reservation person
	if ($uid==$ruid && $confirm==param('key')) return true;
	// event poster
	if ($uid==$euid) return true;
    }
    return false;
}

function template_dir($file='') {
    global $xoopsConfig;
    $lang = $xoopsConfig['language'];
    $dir = dirname(__FILE__).'/language/%s/mail_template/%s';
    $path = sprintf($dir,$lang, $file);
    if (file_exists($path)) {
	$path = sprintf($dir,$lang, '');
    } else {
	$path = sprintf($dir,'english', '');
    }
    return $path;
}

switch ($op) {
case 'delete':
    $result = $xoopsDB->query('SELECT email,r.eid,r.exid,r.status,e.uid,r.uid ruid, info, confirm, optfield, cdate, counter, style, persons, IF(exdate,exdate,edate) edate, notify, title, closetime,IF(x.reserved IS NULL,o.reserved,x.reserved) reserved FROM '.RVTBL.' r LEFT JOIN '.EGTBL.' e ON r.eid=e.eid LEFT JOIN '.OPTBL.' o ON r.eid=o.eid LEFT JOIN '.EXTBL." x ON r.exid=x.exid WHERE rvid=$rvid");
    if (!$result || $xoopsDB->getRowsNum($result)==0) {
	$result = false;
    } else {		// there is reservation
	$data = $xoopsDB->fetchArray($result);
	$evurl = XOOPS_URL.'/modules/eguide/event.php?eid='.$data['eid'].($data['exid']?'&sub='.$data['exid']:'');

	if (!reserv_permit($data['ruid'], $data['uid'], $data['confirm'])) {
	    redirect_header($evurl, 3, _MD_RESERV_NOTFOUND);
	    exit;
	}
    }
    if ($result) {
	$vals = explodeinfo($data['info'], $data['optfield']);
	$num = 1;
	if (isset($vals[$nlab])) {
	    $num = intval($vals[$nlab]);
	    if ($num<1) $num = 1;
	}
	if ($isadmin || $data['edate']-$data['closetime']>$now) {
	    edit_eventdata($data);
	    $eid = $data['eid'];
	    $exid = $data['exid'];
	    $result = $xoopsDB->query('DELETE FROM '.RVTBL." WHERE rvid=$rvid $conf");
	} else {
	    $result = false;
	}
    } else {
	redirect_header('index.php', 3, _MD_RESERV_NOTFOUND);
	exit;
    }
    if ($result) {
	$evurl = XOOPS_URL."/modules/eguide/event.php?eid=$eid".($exid?"&sub=$exid":"");
	if ($data['status']!=_RVSTAT_REFUSED) {
	    if ($exid) {
		$xoopsDB->query('UPDATE '.EXTBL." SET reserved=reserved-$num WHERE exid=$exid");
	    } else {
		$xoopsDB->query('UPDATE '.OPTBL." SET reserved=reserved-$num WHERE eid=$eid");
	    }
	    if ($xoopsModuleConfig['use_plugins']) {
		include_once 'plugins.php';
		foreach ($hooked_function['cancel'] as $func) {
		    if (!$func($eid, $exid, $data['ruid'], $data['uid'])) {
			echo "Cancel failed";
		    }
		}
	    }
	    if ($data['notify']) {
		$poster = new XoopsUser($data['uid']);
		$title = eventdate($data['edate'])." ".$data['title'];
		$email = $data['email'];

		$xoopsMailer =& getMailer();
		$xoopsMailer->useMail();

		if ($xoopsModuleConfig['member_only']) {
		    $user = new XoopsUser($data['ruid']);
		    $email = $user->getVar('email');
		    $uinfo = sprintf("%s: %s (%s)\n%s: %s\n", _MD_UNAME,
				     $user->getVar('uname'),
				     $user->getVar('name'),
				     _MD_EMAIL, $email);
		} else {
		    $uinfo = sprintf("%s: %s\n", _MD_EMAIL, $data['email']);
		}
		if (is_object($xoopsUser)) {
		    $xoopsMailer->assign("REQ_UNAME", $xoopsUser->getVar('uname'));
		    $xoopsMailer->assign("REQ_NAME", $xoopsUser->getVar('name'));
		} else {
		    $xoopsMailer->assign("REQ_UNAME", '*anonymous*');
		    $xoopsMailer->assign("REQ_NAME", $xoopsConfig['anonymous']);
		}
		$xoopsMailer->assign("TITLE", eventdate($data['edate'])." ".$data['title']);
		$xoopsMailer->assign("EVENT_URL", $evurl);
		$xoopsMailer->assign("INFO", $uinfo.$data['info']);
		$xoopsMailer->assign("RVID", $rvid);
		$xoopsMailer->setSubject(_MD_CANCEL.' - '.$title);
		$xoopsMailer->setTemplateDir(template_dir('cancel.tpl'));
		$xoopsMailer->setTemplate('cancel.tpl');
		$xoopsMailer->setToEmails($email);
		$xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
		$xoopsMailer->setFromName(_MD_FROM_NAME);
		if (!in_array($xoopsModuleConfig['notify_group'], $poster->groups())) {
		    $xoopsMailer->setToUsers($poster);
		}
		$xoopsMailer->setToGroups($notify_group);
		$xoopsMailer->send();
	    }
	}
	if (empty($_POST['back'])) {
	    $back = $evurl;
	} else {
	    $back = $myts->makeTboxData4Edit(trim($_POST['back']));
	}
	redirect_header($back,3,_MD_RESERV_CANCELED);
    } else {
	redirect_header($back,5,_MD_CANCEL_FAIL);
    }
    exit;

case 'notify':
    $email = param('email', '');
    if (preg_match('/^[\w\-_\.]+@[\w\-_\.]+$/', $email)) {
	$ml = $xoopsDB->quoteString(strtolower($email));
	$reg = $xoopsDB->query('SELECT rvid FROM '.RVTBL." WHERE email=$ml AND eid=0");
	if ($xoopsDB->getRowsNum($reg)==0) {
	    $conf = rand(10000,99999);
	    $uid = $xoopsUser?$xoopsUser->getVar('uid'):"NULL";
	    $xoopsDB->query('INSERT INTO '.RVTBL.
			    "(eid,uid,rdate,email,status,confirm) VALUES (0,$uid,$now,$ml,1,'$conf')");
	    $msg = _MD_REGISTERED;
	} else {
	    $msg = _MD_DUP_REGISTER;
	}
    } else {
	$msg = _MD_MAIL_ERR;
    }
    redirect_header("index.php",5,$msg);
    exit;

case 'register':
    if (empty($xoopsModuleConfig['user_notify'])) {
	redirect_header($_SERVER['HTTP_REFERER'],2,_NOPERM);
	exit;
    }
}

include(XOOPS_ROOT_PATH."/header.php");
$xoopsTpl->assign('xoops_module_header', HEADER_CSS);
$eid = param('eid');
$exid = param('sub');
$errs = array();

switch($op) {
case 'order':
    $data = fetch_event($eid, $exid);
    $vals = get_opt_values($data['optfield'], $errs);
    $errs = check_prev_order($data, $vals);
    $value = "";
    foreach ($vals as $name => $val) {
	if (preg_match('/\n/', $val)) {
	    $value .= "$name: \\\n$val\n";
	} else {
	    $value .= "$name: $val\n";
	}
    }
    if (!$xoopsModuleConfig['member_only']) {
	$email = param('email', '');
	$ml = strtolower($email);
    } else {
	$email = $xoopsUser->getVar('uname');
	$ml = '';
    }

    if (empty($errs)) {
	$accept = $data['autoaccept'];
	$strict = $data['strict'];
	$persons = $data['persons'];
	$num = 1;
	if ($nlab && isset($vals[$nlab])) {
	    $num =  intval($vals[$nlab]);
	    if ($num<1) $num = 1;
	}
	if (!count_reserved($eid, $exid, $strict, $persons, $num)) {
	    $a = '/^https?:'.preg_quote(preg_replace('/^https?:/','', XOOPS_URL), '/').'/';
	    // note: 
	    $errs[] = preg_match($a,$_SERVER['HTTP_REFERER'])?_MD_RESERV_FULL:_ERRORS;
	}

	// plugin reserved
	if (empty($errs) && $xoopsModuleConfig['use_plugins']) {
	    include_once 'plugins.php';
	    foreach ($hooked_function['reserve'] as $func) {
		if (!$func($eid, $exid, $data['edate'], $data['uid'])) {
		    $msg = $xoopsTpl->get_template_vars('message');
		    $errs[] = empty($msg)?_MD_RESERV_PLUGIN_FAIL:$msg;
		    count_reserved($eid, $exid, $strict, $persons, -$num);
		    break;
		}
	    }
	}
    }

    if (empty($errs)) {
	srand();
	$conf = rand(10000,99999);
	$uid = 'NULL';
	if (is_object($xoopsUser)) {
	    if ($ml == '' || strtolower($xoopsUser->getVar('email'))==$ml) {
		$uid = $xoopsUser->getVar('uid');
	    }
	}
	$ml = $xoopsDB->quoteString($ml);
	$accept = $data['autoaccept'];
	$xoopsDB->query('INSERT INTO '.RVTBL."
	(eid, exid, uid, rdate, email, info, status, confirm)
VALUES ($eid,$exid,$uid,$now,$ml, ".$xoopsDB->quoteString($value).",$accept,'$conf')");
	$rvid = $xoopsDB->getInsertId();
	$title = eventdate($data['edate']).": ".$data['title'];
	$url = XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/event.php?eid='.$eid.($exid?"&sub=$exid":'');
	$poster = new XoopsUser($data['uid']);
	$xoopsMailer =& getMailer();
	$xoopsMailer->useMail();
	$tplfile = $accept?"accept.tpl":"order.tpl";
	$xoopsMailer->setTemplateDir(template_dir($tplfile));
	$xoopsMailer->setTemplate($tplfile);
	$xoopsMailer->assign("EVENT_URL", $url);
	if ($xoopsModuleConfig['member_only']) {
	    $email = $xoopsUser->getVar('email');
	    $uinfo = sprintf("%s: %s (%s)\n%s: %s\n", _MD_UNAME,
			     $xoopsUser->getVar('uname'),
			     $xoopsUser->getVar('name'),
			     _MD_EMAIL, $email);
	} else {
	    $uinfo = sprintf("%s: %s\n", _MD_EMAIL, $email);
	}
	$xoopsMailer->assign("RVID", $rvid);
	$xoopsMailer->assign("CANCEL_KEY", $conf);
	$xoopsMailer->assign("CANCEL_URL", XOOPS_URL."/modules/eguide/reserv.php?op=cancel&rvid=$rvid&key=$conf");
	$xoopsMailer->assign("INFO", $uinfo.$value);
	$xoopsMailer->assign("TITLE", $title);
	$xoopsMailer->assign("SUMMARY", strip_tags($data['summary']));
	$xoopsMailer->setToEmails($email);
	if ($data['notify']) {
	    if (!in_array($xoopsModuleConfig['notify_group'], $poster->groups())) {
		$xoopsMailer->setToUsers($poster);
	    }
	    $xoopsMailer->setToGroups($notify_group);
	}
	$xoopsMailer->setSubject(_MD_SUBJECT.' - '.$title);
	$xoopsMailer->setFromEmail($poster->getVar('email'));
	$xoopsMailer->setFromName(_MD_FROM_NAME);
	if ($xoopsMailer->send()) {
	    echo "<div class='evform'>\n";
	    echo "<h3>"._MD_RESERVATION."</h3>\n";
	    echo "<p><b>"._MD_RESERV_ACCEPT."</b></p>";
	    if ($value) {
		echo "<h3>"._MD_RESERV_CONF."</h3>";
		echo "<blockquote class='evbody'>".nl2br($value)."</blockquote>";
	    }
	    //
	    // register user notify request
	    //
	    if ($xoopsModuleConfig['user_notify'] && param('notify')) {
		$reg = $xoopsDB->query('SELECT * FROM '.RVTBL." WHERE email=$ml AND eid=0");
		if ($xoopsDB->getRowsNum($reg)==0) {
		    $conf = rand(10000,99999);
		    $xoopsDB->query('INSERT INTO '.RVTBL." 
(eid,uid,rdate,email,status,confirm) VALUES (0,$uid,$now,$ml,1,'$conf')");
		} else {
		    echo "<div class='evnote'>"._MD_DUP_REGISTER."</div>\n";
		}
	    }
	} else {
	    echo "<div class='error'>"._MD_SEND_ERR."</div>\n";
	    // delete failer record.
	    $xoopsDB->query('DELETE FROM '.RVTBL." WHERE rvid=$rvid");
	    count_reserved($eid, $exid, $strict, $persons, -$num);
	}
	$evurl = XOOPS_URL."/modules/eguide/event.php?eid=$eid".($exid?"&sub=$exid":"");
	echo "<p><a href='$evurl'>$title</a></p>";
	echo "</div>\n";
    }
    if (empty($errs)) break;

case 'confirm':
    $xoopsOption['template_main'] = 'eguide_confirm.html';

    $data = fetch_event($eid, $exid);
    $opts = $data['optfield'];
    $vals = get_opt_values($opts, $errs);
    $errs = check_prev_order($data, $vals);

    $emhide = "";
    $num = 1;
    if (isset($_POST['email'])) {
	$email = $myts->makeTboxData4Edit($_POST['email']);
	$vals=array_merge(array(_MD_EMAIL=>$email), $vals);
	$emhide = "<input type='hidden' name='email' value='$email'/>\n";
	if (!empty($_POST['notify'])) {
	    $emhide .= "<input type='hidden' name='notify' value='".
		$myts->makeTboxData4Edit($_POST['notify'])."'/>\n";
	}
    }
    $xoopsTpl->assign('lang_title', eventdate($data['edate']).": ".$data['title']);
    $xoopsTpl->assign('event', edit_eventdata($data));

    $xoopsTpl->assign('errors', $errs);
    $xoopsTpl->assign('values', $vals);
    $form = "";
    if (!$errs) {
	$n = 0;
	$xoopsTpl->assign('submit',
	     "<form action='reserv.php?op=order' method='post'>".
	     "<input type='hidden' name='eid' value='$eid'/>\n".
	     $emhide.join("\n", get_opt_values($opts, $errs, true)).
	     "\n<input type='submit' value='"._MD_ORDER_CONF."'/>\n".
	     ($exid?"<input type='hidden' name='sub' value='$exid'/>\n":"").
	     "</form>");
    }
    $xoopsTpl->assign('cancel', "<form action='event.php?eid=$eid".
		      ($exid?'&sub='.$exid:''). "' method='post'>".$emhide.
		      join("\n", get_opt_values($opts, $errs, true)).
		      "\n<input type='submit' value='"._MD_BACK."'/>\n".
		      "</from>\n");
    break;

case 'cancel':
    $result = $xoopsDB->query('SELECT eid,exid FROM '.RVTBL.' WHERE rvid='.$rvid);
    if ($result) {
	if ($xoopsDB->getRowsNum($result)) {
	    list($eid, $exid) = $xoopsDB->fetchRow($result);
	} else $result = false;
    }
    if (!$result || $xoopsDB->getRowsNum($result)==0) {
	$result = false;
    } else {		// there is reservation
	$data = fetch_event($eid, $exid);
	$evurl = XOOPS_URL.'/modules/eguide/event.php?eid='.$data['eid'].($data['exid']?'&sub='.$data['exid']:'');
	if (!reserv_permit($data['ruid'], $data['uid'], $data['confirm'])) {
	    redirect_header($evurl,5,_MD_CANCEL_FAIL);
	    exit;
	}
    }
    if ($result) {
	if (!$isadmin && $data['edate']-$data['closetime']<$now) {
	    echo "<div class='evform'>\n";
	    echo "<div class='error'>"._MD_RESERV_NOCANCEL."</div>\n";
	    echo "</div>\n";
	} else {
	    $eid = $data['eid'];
	    $key = intval($_GET['key']);
	    edit_eventdata($data);
	    $xoopsOption['template_main'] = 'eguide_confirm.html';
	    $xoopsTpl->assign('event', $data);
	    if (isset($_GET['back'])) {
		$back =  $myts->stripSlashesGPC($_GET['back']);
	    } else {
		$back = isset($_SERVER['HTTP_REFERER'])?$myts->makeTboxData4Edit($_SERVER['HTTP_REFERER']):'';
	    }
	    $form = "<h3>"._MD_RESERV_CANCEL."</h3>\n".
		"<form action='reserv.php' method='post'>\n".
		"<input type='hidden' name='op' value='delete' />\n".
		"<input type='hidden' name='eid' value='".$data['eid'].
		"' />\n<input type='hidden' name='key' value='$key' />\n".
		"<input type='hidden' name='back' value='$back' />\n".
		"<input type='hidden' name='rvid' value='$rvid' />\n".
		"<input type='submit' value='"._SUBMIT."' />\n</form>\n";
	    $xoopsTpl->assign('submit', $form);
	}
    } else {
	echo "<div class='evform'>\n";
	echo "<div class='error'>"._MD_RESERV_NOTFOUND."</div>";
	echo "</div>\n";
    }
    break;

case 'register':
    $email = ($xoopsUser)?$xoopsUser->getVar('email'):"";
    echo "<h2>"._MD_NOTIFY_EVENT."</h2>\n";
    echo "<form action='reserv.php' method='post'>
<table class='evtbl' align='center'>\n";
    echo "<tr><th>"._MD_EMAIL."*</th><td><input size='40' name='email' value='$email'/> <input type='submit' value='"._REGISTER."'></td></tr>\n";
    echo "</table>\n";
    echo "<p align='center'>"._MD_NOTIFY_REQUEST."</p>";
    echo "<input type='hidden' name='op' value='notify' />\n</form>\n";
    break;
}
include(XOOPS_ROOT_PATH."/footer.php");
exit;

function getTitle($eid) {
    global $xoopsDB;

    $result = $xoopsDB->query("SELECT title, edate, uid FROM ".
			      $xoopsDB->prefix("eguide")." WHERE eid=$eid");
    return $xoopsDB->fetchArray($result);
}

function count_reserved($eid, $exid, $strict, $persons, $value=1) {
    global $xoopsDB;
    if ($exid) {
	$cond = "exid=$exid";
	$tbl = EXTBL;
    } else {
	$cond = "eid=$eid";
	$tbl = OPTBL;
    }
    $cond .= $strict?" AND reserved<=".($persons-$value):"";
    $res = $xoopsDB->query("UPDATE $tbl SET reserved=reserved+$value WHERE $cond");
    return $res && $xoopsDB->getAffectedRows();
}

function get_opt_values($optfield, &$errs, $hidden=false) {
    $myts =& MyTextSanitizer::getInstance();
    $result = array();
    $field = 0;
    foreach (explode("\n", $optfield) as $n) {
	$field++;
	if (preg_match('/^\s*#/', $n)) continue;
	if (preg_match('/^\s*$/', $n)) continue;
	$a = explode(",", preg_replace('/[\n\r]/',"", $n));
	$name = preg_replace('/^!\s*/', '', array_shift($a));
	$type = "text";
	if (isset($a[0])) {
	    switch (strtolower(array_shift($a))) {
	    case "checkbox":
		$type = 'checkbox';
		break;
	    case 'textarea':
		$type = 'textarea';
		break;
	    }
	}
	$input = "";
	if ($type == 'checkbox') {
	    $v = "";
	    for ($i=1; $i<=count($a); $i++) {
		$n = "opt${field}_$i";
		if (isset($_POST[$n])) {
		    $vv = $myts->stripSlashesGPC($_POST[$n]);
		    $v .= (($v=="")?"":",").$vv;
		    if ($hidden) {
			$input .= "<input type='hidden' name='$n' value='".
			    $myts->makeTboxData4Edit($vv)."'/>";
		    }
		}
	    }
	} else {
	    $v = $myts->stripSlashesGPC($_POST["opt$field"]);
	    if ($hidden) {
		$input .= "<input type='hidden' name='opt$field' value='".
		    $myts->makeTboxData4Edit($v)."'/>";
	    }
	    // remove control char except textarea
	    if ($type!='textarea') $v = preg_replace('/[\x00-\x1f]/', '', $v);
	}
	$mast = preg_match('/\*$/', $name);
	$nums = preg_match('/\#$/', $name);
	$name = preg_replace('/[\*\#]$/', "", $name);
	if ($mast) {
	    // check for NULL
	    if (preg_match('/^\s*$/', $v)) {
		$errs[] = "$name: $v - "._MD_NOITEM_ERR;
	    }
	} elseif ($nums) {
	    // check Number
	    if (!preg_match('/^-?\d+$/', $v)) {
		$errs[] = "$name: $v - "._MD_NUMITEM_ERR;
	    }
	}
	$result[$name] = $hidden?$input:$v;
    }
    return $result;
}

// fetch event data set
function fetch_event($eid, $exid) {
    global $xoopsDB;
    $result = $xoopsDB->query('SELECT 
e.eid, x.exid, uid, o.optfield, reservation, autoaccept, strict, 
IF(exdate,exdate,edate) edate, cdate, title, persons, summary,
if(x.reserved,x.reserved,o.reserved) reserved 
FROM '.EGTBL.' e LEFT JOIN '.OPTBL.' o ON e.eid=o.eid 
LEFT JOIN '.EXTBL." x ON e.eid=eidref AND x.exid=$exid WHERE e.eid=$eid");
    return $xoopsDB->fetchArray($result);
}

// check condition before entry event
// return errors (go on if empty)
function check_prev_order($data, $vals) {
    global $xoopsModuleConfig, $xoopsDB;
    $errs = array();
    $eid = $data['eid'];
    $exid = intval($data['exid']);
    // stopping if multiple event but no have exid (missing?)
    if (!empty($data) && empty($data['exid'])) {
	$result = $xoopsDB->query('SELECT exid FROM '.EXTBL." WHERE eidref=$eid");
	if ($xoopsDB->getRowsNum($result)>0) $errs[] = _MD_RESERV_STOP." (1)";
    }
    // stop reservation or limit over
    if (empty($data['reservation']) ||
	($data['edate']-$data['closetime'])<time()) {
	if (empty($errs)) $errs[] = _MD_RESERV_STOP;
    }

    // order duplicate check
    if (!$xoopsModuleConfig['member_only']) {
	$email = param('email', '');
	if (!preg_match('/^[\w\-_\.]+@[\w\-_\.]+$/', $email)) {
	    $errs[] =  _MD_EMAIL.": $email - "._MD_MAIL_ERR;
	}
	$ml = strtolower($email);
	$result = $xoopsDB->query('SELECT rvid FROM '.RVTBL." WHERE eid=$eid AND exid=$exid AND email=".$xoopsDB->quoteString($ml));
    } else {
	global $xoopsUser;
	if (!is_object($xoopsUser)) redirect_header($_SERVER['HTTP_REFERER'],2);
	$result = $xoopsDB->query('SELECT rvid FROM '.RVTBL." WHERE eid=$eid AND exid=$exid AND uid=".$xoopsUser->getVar('uid'));
	$email = $xoopsUser->getVar('uname');
	$ml = '';
    }
    if ($xoopsDB->getRowsNum($result)) {
	$errs[] = "$email - "._MD_DUP_ERR;
    }
    // checking is there any seat?
    $num = 1;			// how many orders?
    $nlab = $xoopsModuleConfig['label_persons'];
    if ($nlab && isset($vals[$nlab])) {
	$num =  intval($vals[$nlab]);
	if ($num<1) $num = 1;
    }
    if ($data['strict']) {
	if ($data['persons']<=$data['reserved']) {
	    $errs[] = _MD_RESERV_FULL;
	} elseif ($data['persons']<($data['reserved']+$num)) {
	    $errs[] = sprintf($nlab._MD_RESERV_TOMATCH, $num,$data['persons']-$data['reserved']);
	}
    }
    return $errs;
}
?>