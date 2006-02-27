<?php
function event_notify($op, $data) {
    global $xoopsModuleConfig, $xoopsUser, $xoopsConfig;
    switch ($op) {
    case "new":
	$to = $xoopsConfig['adminmail'];
	// notify suppress will be confused?
	if ($xoopsModuleConfig['notify'] /* && $xoopsUser->email()!=$to */) {
	    $xoopsMailer =& getMailer();
	    $xoopsMailer->useMail();
	    $title = $data['title'];
	    $edate = eventdate($data['edate']);
	    $xoopsMailer->setSubject(_MD_NEWSUB." - $edate $title");
	    $xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=".$data['eid']);
	    $xoopsMailer->assign("EVENT_TITLE", $title);
	    $xoopsMailer->assign("EVENT_DATE", $edate);
	    $note = ($data['status'] == STAT_POST)?_MD_APPROVE_REQ:"";
	    $xoopsMailer->assign("EVENT_NOTE", "");
	    $xoopsMailer->setBody(_MD_NOTIFY_NEW);
	    $member_handler =& xoops_gethandler('member');
	    $users = $member_handler->getUsersByGroup($xoopsModuleConfig['notify_group'], true);
	    $uid = $xoopsUser->getVar('uid');
	    foreach ($users as $user) {
		if ($user->getVar('uid') != $uid) {
		    $xoopsMailer->setToUsers($user);
		}
	    }
	    $xoopsMailer->setFromEmail($to);
	    $xoopsMailer->setFromName(_MD_FROM_NAME);
	    $xoopsMailer->send();
	}
	break;
    case "update":
	break;
    }
    
}
function user_notify($eid) {
    global $xoopsUser, $xoopsDB, $xoopsConfig, $xoopsModuleConfig;

    $tbl = $xoopsDB->prefix("eguide");
    $rsv = $xoopsDB->prefix("eguide_reserv");

    $result = $xoopsDB->query("SELECT title,edate,expire,status FROM $tbl WHERE eid=$eid");
    if (empty($result)) {
	echo "<div class='error'>Not found Event(eid='$eid')</div>\n";
    }
    $data = $xoopsDB->fetchArray($result);
    $title = eventdate($data['edate'])." ".$data['title'];

    if (!$xoopsModuleConfig['user_notify'] ||
	$data['expire']<time() ||
	$data['status']!=STAT_NORMAL) return (false);

    $result = $xoopsDB->query("SELECT rvid, email, confirm FROM $rsv WHERE eid=0");
    while ($data = $xoopsDB->fetchArray($result)) {
	$xoopsMailer =& getMailer();
	$xoopsMailer->useMail();
	$xoopsMailer->setSubject(_MD_NEWSUB." - $title");
	$xoopsMailer->setBody(_MD_NEW_NOTIFY);
	$xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
	$xoopsMailer->setFromName(_MD_FROM_NAME);
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