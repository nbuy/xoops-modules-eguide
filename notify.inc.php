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
	    $note = ($data['status'] == STAT_POST)?_MD_APPROVE_REQ:"";
	    $tags = array('EVENT_TITLE'=> $title,
			  'EVENT_DATE' => $edate,
			  'EVENT_NOTE' => $note,
			  'EVENT_URL'  => EGUIDE_URL."/event.php?eid=".$data['eid']);
	    $xoopsMailer->assign($tags);
	    $tpl = 'notify_admin_new.tpl';
	    $xoopsMailer->setTemplateDir(template_dir($tpl));
	    $xoopsMailer->setTemplate($tpl);
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

    $result = $xoopsDB->query("SELECT title,edate,expire,status,topicid FROM $tbl WHERE eid=$eid");
    if (!$result || $xoopsDB->getRowsNum($result)==0) {
	echo "<div class='error'>Not found Event(eid='$eid')</div>\n";
	return;
    }
    $data = $xoopsDB->fetchArray($result);
    $title = $data['title'];
    $edate = $data['edate'];
    $expire = $data['expire'];

    // using XOOPS2 notification system
	    
    $tags = array('EVENT_TITLE'=> $title,
		  'EVENT_DATE' => formatTimestamp($edate, _MD_TIME_FMT),
		  'EVENT_NOTE' => '',
		  'EVENT_URL'  => EGUIDE_URL."/event.php?eid=$eid");
    $notification_handler =& xoops_gethandler('notification');
    $notification_handler->triggerEvent('global', 0, 'new', $tags);
    $notification_handler->triggerEvent('category', $data['topicid'], 'new', $tags);


    if (!$xoopsModuleConfig['user_notify'] ||
	($expire>$edate?$expire<time():($edate+$expire)<time()) ||
	$data['status']!=STAT_NORMAL) return (false);

    $result = $xoopsDB->query("SELECT rvid, email, confirm FROM $rsv WHERE eid=0");
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