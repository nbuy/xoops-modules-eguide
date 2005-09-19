<?php
// reservation proceedings.
// $Id: reserv.php,v 1.8 2005/09/19 07:05:58 nobu Exp $
include("header.php");

$tbl = $xoopsDB->prefix("eguide");
$opt = $xoopsDB->prefix("eguide_opt");
$rsv = $xoopsDB->prefix("eguide_reserv");

foreach (array("op","key","rvid") as $v) {
    if (isset($_GET[$v])) $$v = $_GET[$v];
}
foreach ($_POST as $i => $v) {
    $$i = post_filter($v);
}

if (isset($op)) {
    switch ($op) {
    case 'delete':
	$result = $xoopsDB->query("SELECT email,eid,status FROM $rsv WHERE rvid=$rvid");
	$reserv = $xoopsDB->fetchArray($result);
	$result = $xoopsDB->query("DELETE FROM $rsv WHERE rvid=$rvid AND confirm=$key");
	$eid = $reserv['eid'];
	if ($result) {
	    if ($eid) {
		$result = $xoopsDB->query("SELECT uid, notify, title, edate FROM $tbl t,$opt o WHERE t.eid=$eid AND o.eid=t.eid");
		if ($reserv['status']) {
		    $xoopsDB->query("UPDATE $opt SET reserved=reserved-1 WHERE $eid");
		}
		$data = $xoopsDB->fetchArray($result);
		if ($data['notify']) {
		    $poster = new XoopsUser($data['uid']);
		    $title = date(_MD_DATE_FMT, $data['edate'])." ".$data['title'];

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
	if (preg_match('/^[\w\-_\.]+@[\w\-_\.]+$/', $email)) {
	    $ml = strtolower($email);
	    $reg = $xoopsDB->query("SELECT rvid FROM $rsv WHERE email='$ml' AND eid=0");
	    if ($xoopsDB->getRowsNum($reg)==0) {
		$conf = rand(10000,99999);
		$now = time();
		$uid = $xoopsUser?$xoopsUser->uid():"NULL";
		$xoopsDB->query("INSERT INTO $rsv 
(eid,uid,rdate,email,status,confirm) VALUES (0,$uid,$now,'$ml',1,'$conf')");
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
	if (empty($eventConfig['user_notify'])) {
	    redirect_header($_SERVER['HTTP_REFERER'],2,_NOPERM);
	    exit;
	}
    }
}

include(XOOPS_ROOT_PATH."/header.php");

switch($op) {
case 'order':
    OpenTable();
    echo "<p><b>"._MD_RESERVATION."</b></p>\n";
    $result = $xoopsDB->query("SELECT * FROM $opt WHERE eid=$eid");
    $data = $xoopsDB->fetchArray($result);
    $err = 0;
    $field = 0;
    $value = "";
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
    if (!preg_match('/^[\w\-_\.]+@[\w\-_\.]+$/', $email)) {
	echo "<div class='error'>"._MD_EMAIL.": $email - "._MD_MAIL_ERR."</div>\n";
	$err++;
    }
    $ml = strtolower($email);
    $result = $xoopsDB->query("SELECT rvid FROM $rsv WHERE eid=$eid AND email='$ml'");
    if ($xoopsDB->getRowsNum($result)) {
	echo "<div class='error'>$email - "._MD_DUP_ERR."</div>";
	$err++;
    }
    if (!$err) {
	$accept = $data['autoaccept'];
	if ($accept) {
	    $cond = "eid=$eid".($data['strict']?" AND reserved<persons":"");
	    $res = $xoopsDB->query("UPDATE $opt SET reserved=reserved+1 WHERE $cond");
	    //if (!$res || $xoopsDB->getAffectedRows()==0) { // in XOOPS2
	    if (!$res || mysql_affected_rows($xoopsDB->conn)==0) {
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
	    if (strtolower($xoopsUser->email())==$ml) $uid = $xoopsUser->uid();
	}
	$now=time();
	$xoopsDB->query("INSERT INTO $rsv
	(eid, uid, rdate, email, info, status, confirm)
VALUES  ($eid,$uid,$now, '$ml', '".addslashes($value)."',$accept,'$conf')");
	$result = $xoopsDB->query("SELECT rvid FROM $rsv WHERE eid=$eid AND email='$ml' AND rdate=$now");
	$id = $xoopsDB->fetchArray($result);
	$rvid = $id['rvid'];
	$result = $xoopsDB->query("SELECT title, edate, uid FROM $tbl WHERE eid=$eid");
	$guide = $xoopsDB->fetchArray($result);
	$title = date(_MD_DATE_FMT, $guide['edate'])." ".$guide['title'];

	$poster = new XoopsUser($guide['uid']);
	$xoopsMailer =& getMailer();
	$xoopsMailer->useMail();
	$xoopsMailer->setTemplateDir(XOOPS_ROOT_PATH."/modules/eguide/language/".$xoopsConfig['language']."/");
	$xoopsMailer->setTemplate($accept?"accept.tpl":"order.tpl");
	$xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=$eid");
	$xoopsMailer->assign("CANCEL_URL", XOOPS_URL."/modules/eguide/reserv.php?op=cancel&rvid=$rvid&key=$conf");
	$xoopsMailer->assign("INFO", _MD_EMAIL.": ".$email."\n".$value);
	$xoopsMailer->assign("TITLE", $title);
	$xoopsMailer->setToEmails($email);
	if ($data['notify']) {
	    $xoopsMailer->setToEmails($poster->email());
	}
	$xoopsMailer->setSubject(_MD_SUBJECT." - ".$title);
	$xoopsMailer->setFromEmail($poster->email());
	$xoopsMailer->setFromName("Event Reservation");
	if ($xoopsMailer->send()) {
	    echo "<p><b>"._MD_RESERV_ACCEPT."</b></p>";
	    echo "<p class='evbody'>"._MD_RESERV_CONF."</p>";
	    echo "<blockquote class='evbody'>".nl2br($value)."</blockquote>";
	    // register user notify request
	    //
	    if ($eventConfig['user_notify'] && isset($notify)) {
		$reg = $xoopsDB->query("SELECT * FROM $rsv WHERE email='$ml' AND eid=0");
		if ($xoopsDB->getRowsNum($reg)==0) {
		    $conf = rand(10000,99999);
		    $xoopsDB->query("INSERT INTO $rsv 
(eid,uid,rdate,email,status,confirm) VALUES (0,$uid,$now,'$ml',1,'$conf')");
		} else {
		    echo "<div class='evnote'>"._MD_DUP_REGISTER."</div>\n";
		}
	    }
	} else {
	    echo "<div class='error'>"._MD_SEND_ERR."</div>\n";
	    // delete failer record.
	    $xoopsDB->query("DELETE FROM $rsv WHERE rvid=$rvid");
	    $xoopsDB->query("UPDATE $opt SET reserved=reserved-1 WHERE eid=$eid");
	}
    }
    CloseTable();
    break;

case 'cancel':
    $result=$xoopsDB->query("SELECT * FROM $rsv WHERE rvid=$rvid AND confirm=$key");
    if ($xoopsDB->getRowsNum($result)) {
	$data = $xoopsDB->fetchArray($result);
	$inc = XOOPS_ROOT_PATH."/themes/".$xoopsTheme['thename'];
	if ( file_exists("$inc/themeevent.php") ) {
	    include("$inc/themeevent.php");
	} else {
	    include("themeevent.php");
	}
	$eid = $data['eid'];
	$result = $xoopsDB->query("SELECT eid, cdate, edate, title, summary, uid, status, style, counter  FROM $tbl WHERE eid=$eid");
	if ($eid == 0 || $view = $xoopsDB->fetchArray($result)) {
	    OpenTable();
	    if ($eid) {
		$mlink = "<a href='event.php?eid=".$data['eid']."'>"._MD_READMORE."</a>";
		themeevent($view, $mlink);
	    } else {
		echo "<div class='evnote'>"._MD_NOTIFY_REQUEST."</div>\n";
	    }
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
    $email = ($xoopsUser)?$xoopsUser->email():"";
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