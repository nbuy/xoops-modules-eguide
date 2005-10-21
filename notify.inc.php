<?php
function event_notify($op, $data) {
    global $eventConfig, $xoopsUser, $xoopsConfig;
    switch ($op) {
    case "new":
	$to = $xoopsConfig['adminmail'];
	// notify suppress will be confused?
	if ($eventConfig['notify'] /* && $xoopsUser->email()!=$to */) {
	    $xoopsMailer =& getMailer();
	    $xoopsMailer->useMail();
	    $title = $data['title'];
	    $edate = formatTimestamp($data['edate'], _AM_DATE_FMT);
	    $xoopsMailer->setSubject(_AM_NEWSUB." - $edate $title");
	    $xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=".$data['eid']);
	    $xoopsMailer->assign("EVENT_TITLE", $title);
	    $xoopsMailer->assign("EVENT_DATE", $edate);
	    $note = ($data['status'] == STAT_POST)?_AM_APPROVE_REQ:"";
	    $xoopsMailer->assign("EVENT_NOTE", "");
	    $xoopsMailer->setBody(_AM_NOTIFY_NEW);
	    $xoopsMailer->setToEmails($to);
	    $xoopsMailer->setFromEmail($to);
	    $xoopsMailer->setFromName("Event Notify");
	    $xoopsMailer->send();
	}
	break;
    case "update":
	break;
    }
    
}
function user_notify($eid) {
    global $xoopsUser, $xoopsDB, $xoopsConfig, $eventConfig;

    $tbl = $xoopsDB->prefix("eguide");
    $rsv = $xoopsDB->prefix("eguide_reserv");

    $result = $xoopsDB->query("SELECT title,edate,expire,status FROM $tbl WHERE eid=$eid");
    if (empty($result)) {
	echo "<div class='error'>Not found Event(eid='$eid')</div>\n";
    }
    $data = $xoopsDB->fetchArray($result);
    $title = formatTimestamp($data['edate'], _AM_DATE_FMT)." ".$data['title'];

    if (!$eventConfig['user_notify'] ||
	$data['expire']<time() ||
	$data['status']!=STAT_NORMAL) return (false);

    $result = $xoopsDB->query("SELECT rvid, email, confirm FROM $rsv WHERE eid=0");
    while ($data = $xoopsDB->fetchArray($result)) {
	$xoopsMailer =& getMailer();
	$xoopsMailer->useMail();
	$xoopsMailer->setSubject(_AM_NEWSUB." - $title");
	$xoopsMailer->setBody(_AM_NEW_NOTIFY);
	$xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
	$xoopsMailer->setFromName("Event Notify");
	$xoopsMailer->assign("SITENAME", $xoopsConfig['sitename']);
	$xoopsMailer->assign("TITLE", $title);
	$xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=$eid");
	$xoopsMailer->assign("CANCEL_URL", XOOPS_URL."/modules/eguide/reserv.php?op=cancel&rvid=".$data['rvid']."&key=".$data['confirm']);
	$xoopsMailer->setToEmails($data['email']);
	if (!$xoopsMailer->send()) {
	    echo "<div class='error'>".$xoopsMailer->getErrors()."</div>\n";
	}
    }
}
?>