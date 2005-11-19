<?php
// reservation proceedings.
// $Id: reserv.php,v 1.11 2005/11/19 18:32:35 nobu Exp $
include 'header.php';

$op = param('op', "x");
$rvid = param('rvid');
$key = param('key');

if (isset($op)) {
    switch ($op) {
    case 'delete':
	$result = $xoopsDB->query('SELECT email,eid,status FROM '.RVTBL." WHERE rvid=$rvid");
	$reserv = $xoopsDB->fetchArray($result);
	$result = $xoopsDB->query('DELETE FROM '.RVTBL." WHERE rvid=$rvid AND confirm=$key");
	$eid = $reserv['eid'];
	if ($result) {
	    if ($eid) {
		$result = $xoopsDB->query('SELECT uid, notify, title, edate FROM '.EGTBL.' e LEFT JOIN '.OPTBL." o ON e.eid=o.eid WHERE e.eid=$eid");
		$data = $xoopsDB->fetchArray($result);
		if ($reserv['status']) {
		    $xoopsDB->query('UPDATE '.OPTBL." SET reserved=reserved-1 WHERE eid=$eid");
		}
		if ($data['notify']) {
		    $poster = new XoopsUser($data['uid']);
		    $title = eventdate($data['edate'])." ".$data['title'];

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
		$now = time();
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

$eid = param('eid');
switch($op) {
case 'order':
    OpenTable();
    echo "<p><b>"._MD_RESERVATION."</b></p>\n";
    $result = $xoopsDB->query('SELECT * FROM '.OPTBL." WHERE eid=$eid");
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
	if ($accept) {
	    if ($exid) {
		$cond = "exid=$exid".($data['strict']?" AND reserved<".$data['persons']:"");
		$res = $xoopsDB->query('UPDATE '.EXTBL." SET reserved=reserved+1 WHERE $cond");
	    } else {
		$cond = "eid=$eid".($data['strict']?" AND reserved<persons":"");
		$res = $xoopsDB->query('UPDATE '.OPTBL." SET reserved=reserved+1 WHERE $cond");
	    }
	    if (!$res || $xoopsDB->getAffectedRows()==0) { // in XOOPS2
		echo "<div class='error'>"._MD_RESERV_FULL."</div>";
		$err++;
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
	$now=time();
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
	$xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=$eid");
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
	    echo "<p class='evbody'>"._MD_RESERV_CONF."</p>";
	    echo "<blockquote class='evbody'>".nl2br($value)."</blockquote>";
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
    CloseTable();
    break;

case 'cancel':
    $result=$xoopsDB->query('SELECT * FROM '.RVTBL." WHERE rvid=$rvid AND confirm=$key");
    if ($xoopsDB->getRowsNum($result)) {
	$data = $xoopsDB->fetchArray($result);
	$eid = $data['eid'];
	$result = $xoopsDB->query('SELECT eid, cdate, edate, title, summary, uid, status, style, counter  FROM '.EGTBL." WHERE eid=$eid");
	if ($eid == 0 || $view = $xoopsDB->fetchArray($result)) {
	    OpenTable();
	    echo "<h1>".$view['title']."</h1>\n";
	    echo "<p>".$view['summary']."</p>\n";
	    echo "<p /><div>"._MD_EMAIL." ".$data['email']."</div>";
	    echo "<div>"._MD_RESERV_CANCEL;
	    echo "<form action='reserv.php' method='post'>\n";
	    echo "<input type='hidden' name='op' value='delete' />\n";
	    echo "<input type='hidden' name='eid' value='".$data['eid']."' />\n";
	    echo "<input type='hidden' name='key' value='$key' />\n";
	    echo "<input type='hidden' name='rvid' value='$rvid' />\n";
	    echo "<input type='submit' value='"._SUBMIT."' />\n</form></div>\n";
	    CloseTable();
	}
    } else {
	OpenTable();
	echo "<div class='error'>"._MD_RESERV_NOTFOUND."</div>";
	CloseTable();
    }
    break;

case 'register':
    OpenTable();
    $email = ($xoopsUser)?$xoopsUser->getVar('email'):"";
    echo "<div class='evtitle'>"._MD_NOTIFY_EVENT."</div><br />";
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

?>