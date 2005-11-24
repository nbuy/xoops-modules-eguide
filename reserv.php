<?php
// reservation proceedings.
// $Id: reserv.php,v 1.12 2005/11/24 08:15:49 nobu Exp $
include 'header.php';

$op = param('op', "x");
$rvid = param('rvid');
$key = param('key');
$now=time();

if (isset($op)) {
    switch ($op) {
    case 'delete':
	$result = $xoopsDB->query('SELECT email,r.eid,r.exid,r.status,e.uid,IF(exdate,exdate,edate) edate, notify FROM '.RVTBL.' r LEFT JOIN '.EGTBL.' e ON r.eid=e.eid LEFT JOIN '.OPTBL.' o ON r.eid=o.eid LEFT JOIN '.EXTBL." x ON r.exid=x.exid WHERE rvid=$rvid AND edate<$now");
	if ($result && $xoopsDB->getRowsNum($result)) {
	    $reserv = $xoopsDB->fetchArray($result);
	    $eid = $reserv['eid'];
	    $exid = $reserv['exid'];

	    $result = $xoopsDB->query('DELETE FROM '.RVTBL." WHERE rvid=$rvid AND confirm=$key");
	} else {
	    redirect_header('index.php', 3, _MD_RESERV_NOTFOUND);
	    exit;
	}
	if ($result) {
	    if ($exid && $reserv['status']) {
		$xoopsDB->query('UPDATE '.EXTBL." SET reserved=reserved-1 WHERE exid=$exid");
	    } else {
		$xoopsDB->query('UPDATE '.OPTBL." SET reserved=reserved-1 WHERE eid=$eid");
	    }
	    if ($xoopsModuleConfig['use_plugins']) {
		include_once 'plugins.php';
		foreach ($hooked_function['cancel'] as $func) {
		    if (!$func($eid, $exid, $reserv['uid'])) {
			echo "Cancel failed";
		    }
		}
	    }
	    if ($reserv['notify']) {
		$poster = new XoopsUser($reserv['uid']);
		$title = eventdate($reserv['edate'])." ".$reserv['title'];

		$xoopsMailer =& getMailer();
		$xoopsMailer->useMail();
		$xoopsMailer->setSubject(_MD_RESERV_CANCELED);
		$xoopsMailer->setBody(sprintf(_MD_RESERV_NOTIFY,
					      _MD_RESERV_CANCELED, $reserv['email'], $title,
					      XOOPS_URL."/modules/eguide/event.php?eid=$eid"));
		$xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
		$xoopsMailer->setFromName("Event Notify");
		$xoopsMailer->setToUsers($poster);
		$xoopsMailer->send();
	    }
	    redirect_header("index.php",5,_MD_RESERV_CANCELED);
	} else {
	    redirect_header("event.php?eid=$eid",5,_MD_CANCEL_FAIL);
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
}

include(XOOPS_ROOT_PATH."/header.php");
$xoopsTpl->assign('xoops_module_header', HEADER_CSS);
$eid = param('eid');
switch($op) {
case 'order':
    echo "<div class='evform'>\n";
    echo "<h3>"._MD_RESERVATION."</h3>\n";
    $result = $xoopsDB->query('SELECT uid,o.* FROM '.EGTBL.' e,'.OPTBL." o WHERE e.eid=o.eid AND e.eid=$eid");
    $data = $xoopsDB->fetchArray($result);
    $exid = param('sub');
    $err = 0;
    $field = 0;
    $value = "";
    if ($exid) {
	$result = $xoopsDB->query('SELECT exdate FROM '.EXTBL." WHERE exid=$exid AND eidref=$eid");
	if ($xoopsDB->getRowsNum($result)==1) {
	    list($ldate) = $xoopsDB->fetchRow($result);
	} else {
	    $err++;
	    echo "<div class='error'>"._MD_RESERV_STOP."</div>\n";
	}
    } else {
	$result = $xoopsDB->query('SELECT exid FROM '.EXTBL." WHERE eidref=$eid");
	if ($xoopsDB->getRowsNum($result)) {
	    $err++;
	    echo "<div class='error'>"._MD_RESERV_STOP."</div>\n";
	}
    }
    if (empty($data['reservation'])) {
	$err++;
	echo "<div class='error'>"._MD_RESERV_STOP."</div>\n";
    }
    foreach (explode("\n", $data['optfield']) as $n) {
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
	if ($type == 'checkbox') {
	    $v = "";
	    for ($i=1; $i<=count($a); $i++) {
		$n = "opt${field}_$i";
		if (isset($_POST[$n])) {
		    $v .= (($v=="")?"":",").$_POST[$n];
		}
	    }
	} else {
	    $v = $_POST["opt$field"];
	    if ($type=='textarea') $v = "\\\n$v";
	}
	if (preg_match('/\*$/', $name)) {
	    // check for NULL
	    if (preg_match('/^\s*$/', $v)) {
		echo "<div class='error'>$name: $v - "._MD_NOITEM_ERR."</div>\n";
		$err++;
	    }
	} elseif (preg_match('/\#$/', $name)) {
	    // check Number
	    if (!preg_match('/^\d+$/', $v)) {
		echo "<div class='error'>$name: $v - "._MD_NUMITEM_ERR."</div>\n";
		$err++;
	    }
	}
	$name = preg_replace('/[\*#]$/', "", $name);
	$value .= sprintf("%s: %s\n", $name, $v);
    }
    // duplicate check
    if (!$xoopsModuleConfig['member_only']) {
	$email = param('email', '');
	if (!preg_match('/^[\w\-_\.]+@[\w\-_\.]+$/', $email)) {
	    echo "<div class='error'>"._MD_EMAIL.": $email - "._MD_MAIL_ERR."</div>\n";
	    $err++;
	}
	$ml = strtolower($email);
	$result = $xoopsDB->query('SELECT rvid FROM'.RVTBL." WHERE eid=$eid AND exid=$exid AND email=".$xoopsDB->quoteString($ml));
	if ($xoopsDB->getRowsNum($result)) {
	    echo "<div class='error'>$email - "._MD_DUP_ERR."</div>";
	    $err++;
	}
    } else {
	if (!is_object($xoopsUser)) redirect_header($_SERVER['HTTP_REFERER'],2);
	$email = $xoopsUser->getVar('email');
	$ml = strtolower($email);
    }

    if (!$err) {
	$accept = $data['autoaccept'];
	$strict = $data['strict'];
	$persons = $data['persons'];
	if ($accept && !count_reserved($eid, $exid, $strict, $persons)) {
	    echo "<div class='error'>"._MD_RESERV_FULL."</div>";
	    $err++;
	}

	// plugin reserved
	if (!$err && $xoopsModuleConfig['use_plugins']) {
	    include_once 'plugins.php';
	    foreach ($hooked_function['reserve'] as $func) {
		if (!$func($eid, $exid, $data['uid'])) {
		    echo "<div class='error'>"._MD_RESERV_PLUGIN_FAIL."</div>";
		    if ($accept) { // rollback
			count_reserved($eid, $exid, $strict, $persons, -1);
		    }
		    $err++;
		    break;
		}
	    }
	}
    }

    if ($err) {
	echo "<p><input type='button' value='"._MD_BACK."' onclick='javascript:history.go(-1);' /></p>";
    } else {
	srand();
	$conf = rand(10000,99999);
	$uid = 'NULL';
	if ($xoopsUser) {
	    if (strtolower($xoopsUser->getVar('email'))==$ml) {
		$uid = $xoopsUser->getVar('uid');
	    }
	}
	$ml = $xoopsDB->quoteString($ml);
	$xoopsDB->query('INSERT INTO '.RVTBL."
	(eid, exid, uid, rdate, email, info, status, confirm)
VALUES ($eid,$exid,$uid,$now,$ml, ".$xoopsDB->quoteString($value).",$accept,'$conf')");
	$rvid = $xoopsDB->getInsertId();
	$result = $xoopsDB->query('SELECT title, edate, uid FROM '.EGTBL." WHERE eid=$eid");
	$guide = $xoopsDB->fetchArray($result);
	if ($exid==0) $ldate = $guide['edate'];
	$title = eventdate($ldate)." ".$guide['title'];

	$poster = new XoopsUser($guide['uid']);
	$xoopsMailer =& getMailer();
	$xoopsMailer->useMail();
	$xoopsMailer->setTemplateDir(XOOPS_ROOT_PATH."/modules/eguide/language/".$xoopsConfig['language']."/");
	$xoopsMailer->setTemplate($accept?"accept.tpl":"order.tpl");
	$xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=$eid".($exid?"&sub=$exid":''));
	$xoopsMailer->assign("RVID", $rvid);
	$xoopsMailer->assign("CANCEL_URL", XOOPS_URL."/modules/eguide/reserv.php?op=cancel&rvid=$rvid&key=$conf");
	$xoopsMailer->assign("INFO", _MD_EMAIL.": ".$email."\n".$value);
	$xoopsMailer->assign("TITLE", $title);
	$xoopsMailer->setToEmails($email);
	if ($data['notify']) {
	    $xoopsMailer->setToEmails($poster->getVar('email'));
	}
	$xoopsMailer->setSubject(_MD_SUBJECT." - ".$title);
	$xoopsMailer->setFromEmail($poster->getVar('email'));
	$xoopsMailer->setFromName("Event Reservation");
	if ($xoopsMailer->send()) {
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
	    $xoopsDB->query('UPDATE '.OPTBL." SET reserved=reserved-1 WHERE eid=$eid");
	}
    }
    echo "</div>\n";
    break;

case 'cancel':
    $result = $xoopsDB->query('SELECT e.eid, cdate, title, summary, e.uid, e.status, style, counter, IF(exdate,exdate,edate) edate FROM '.RVTBL.' r LEFT JOIN '.EGTBL.' e ON r.eid=e.eid LEFT JOIN '.EXTBL." x ON r.exid=x.exid WHERE rvid=$rvid");
    echo "<div class='evform'>\n";
    if ($result && $xoopsDB->getRowsNum($result)) {
	$data = $xoopsDB->fetchArray($result);
	if ($edate>$now) {
	    echo "<div class='error'>"._MD_RESERV_NOCANCEL."</div>\n";
	} else {
	    $eid = $data['eid'];
	    edit_eventdata($data);
	    echo "<h2>".$data['title']."</h2>\n";
	    echo "<div class='evbody'>".$data['disp_summary']."</div>\n";
	    echo "<p /><div>"._MD_EMAIL." ".$data['email']."</div>";
	    echo "<h3>"._MD_RESERV_CANCEL."<h3>\n";
	    echo "<form action='reserv.php' method='post'>\n";
	    echo "<input type='hidden' name='op' value='delete' />\n";
	    echo "<input type='hidden' name='eid' value='".$data['eid']."' />\n";
	    echo "<input type='hidden' name='key' value='$key' />\n";
	    echo "<input type='hidden' name='rvid' value='$rvid' />\n";
	    echo "<input type='submit' value='"._SUBMIT."' />\n";
	    echo "</form>\n";
	}
    } else {
	echo "<div class='error'>"._MD_RESERV_NOTFOUND."</div>";
    }
    echo "</div>\n";
    break;

case 'register':
    OpenTable();
    $email = ($xoopsUser)?$xoopsUser->getVar('email'):"";
    echo "<h2>"._MD_NOTIFY_EVENT."</h2>\n";
    echo "<form action='reserv.php' method='post'>
<table class='evtbl' align='center'>\n";
    echo "<tr><th>"._MD_EMAIL."*</th><td><input size='40' name='email' value='$email'/> <input type='submit' value='"._REGISTER."'></td></tr>\n";
    echo "</table>\n";
    echo "<p align='center'>"._MD_NOTIFY_REQUEST."</p>";
    echo "<input type='hidden' name='op' value='notify' />\n</form>\n";
    CloseTable();
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
	$cond = "exid=$exid".($data['strict']?" AND reserved<".$data['persons']:"");
	$res = $xoopsDB->query('UPDATE '.EXTBL." SET reserved=reserved+$value WHERE $cond");
    } else {
	$cond = "eid=$eid".($data['strict']?" AND reserved<persons":"");
	$res = $xoopsDB->query('UPDATE '.OPTBL." SET reserved=reserved+$value WHERE $cond");
    }
    return $res && $xoopsDB->getAffectedRows();
}

?>