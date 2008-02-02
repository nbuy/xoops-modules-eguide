<?php
// $Id: notify.inc.php,v 1.16 2008/02/02 11:57:12 nobu Exp $
function event_notify($op, $data) {
    global $xoopsModuleConfig, $xoopsUser, $xoopsConfig;
    $notify = $xoopsModuleConfig['notify'];
    if (!$notify) return;

    $xoopsMailer =& getMailer();
    $xoopsMailer->useMail();

    switch ($op) {
    case "new":
	$tpl = 'notify_admin_new.tpl';
	// notify suppress will be confused?
	$title = $data['title'];
	$edate = eventdate($data['edate']);
	$xoopsMailer->setSubject(_MD_NEWSUB." - {EVENT_DATE} {EVENT_TITLE}");
	$note = ($data['status'] == STAT_POST)?_MD_APPROVE_REQ:"";
	$tags = array('EVENT_TITLE'=> $title,
		      'EVENT_DATE' => $edate,
		      'EVENT_NOTE' => $note,
		      'EVENT_URL'  => EGUIDE_URL."/event.php?eid=".$data['eid']);
	break;
    case "update":
	$tpl = 'notify_admin_change.tpl';
	$tags =& $data;
	$xoopsMailer->setSubject(_MD_UPDATE_SUBJECT);
	break;
    }
    $member_handler =& xoops_gethandler('member');
    $users = $member_handler->getUsersByGroup($xoopsModuleConfig['notify_group'], true);
    $uids = array();
    if ($notify==1) $uids[] = $xoopsUser->getVar('uid'); // suppress self
    $uid = $data['uid'];
    if (!in_array($uid, $uids)) { // update by not poster?
	$user = $member_handler->getUser($uid);
	$xoopsMailer->setToUsers($user);
	$uids[] = $uid;
    }
    foreach ($users as $user) {
	if (!in_array($user->getVar('uid'), $uids)) {
	    $xoopsMailer->setToUsers($user);
	}
    }
    $xoopsMailer->setTemplateDir(template_dir($tpl));
    $xoopsMailer->setTemplate($tpl);
    $xoopsMailer->assign($tags);
    $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
    $xoopsMailer->setFromName(_MD_FROM_NAME);
    return $xoopsMailer->send();
}

function user_notify($eid) {
    global $xoopsUser, $xoopsDB, $xoopsConfig, $xoopsModuleConfig;

    $result = $xoopsDB->query("SELECT title,edate,expire,status,topicid FROM ".EGTBL." WHERE eid=$eid");
    if (!$result || $xoopsDB->getRowsNum($result)==0) {
	echo "<div class='error'>Not found Event(eid='$eid')</div>\n";
	return;
    }
    $data = $xoopsDB->fetchArray($result);
    $title = $data['title'];
    $edate = $data['edate'];
    $expire = $data['expire'];

    // using XOOPS2 notification system
	    
    if (!$xoopsModuleConfig['user_notify'] ||
	($expire>$edate?$expire<time():($edate+$expire)<time()) ||
	$data['status']!=STAT_NORMAL) return (false);

    $tags = array('EVENT_TITLE'=> $title,
		  'EVENT_DATE' => formatTimestamp($edate, _MD_TIME_FMT),
		  'EVENT_NOTE' => '',
		  'EVENT_URL'  => EGUIDE_URL."/event.php?eid=$eid");
    $notification_handler =& xoops_gethandler('notification');
    $notification_handler->triggerEvent('global', 0, 'new', $tags);
    $notification_handler->triggerEvent('category', $data['topicid'], 'new', $tags);

    $result = $xoopsDB->query("SELECT rvid, email, confirm FROM ".RVTBL." WHERE eid=0");
    while ($data = $xoopsDB->fetchArray($result)) {
	$xoopsMailer =& getMailer();
	$xoopsMailer->useMail();
	$xoopsMailer->setSubject(_MD_NEWSUB." - $title");
	$tpl = 'notify_user_new.tpl';
	$xoopsMailer->setTemplateDir(template_dir($tpl));
	$xoopsMailer->setTemplate($tpl);
	$xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
	$xoopsMailer->setFromName(_MD_FROM_NAME);
	$xoopsMailer->assign("SITENAME", $xoopsConfig['sitename']);
	$xoopsMailer->assign("TITLE", $title);
	$xoopsMailer->assign("EVENT_URL", EGUIDE_URL."/event.php?eid=$eid");
	$xoopsMailer->assign("CANCEL_URL", EGUIDE_URL."/reserv.php?op=cancel&rvid=".$data['rvid']."&key=".$data['confirm']);
	$xoopsMailer->setToEmails($data['email']);
	if (!$xoopsMailer->send()) {
	    echo "<div class='error'>".$xoopsMailer->getErrors()."</div>\n";
	}
    }
}
?>