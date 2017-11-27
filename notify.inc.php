<?php
//
/**
 * @param $op
 * @param $data
 * @return bool|void
 */
function event_notify($op, $data)
{
    global $xoopsModuleConfig;
    $notify = $xoopsModuleConfig['notify'];
    if (!$notify) {
        return;
    }

    $xoopsMailer = xoops_getMailer();
    $xoopsMailer->useMail();

    switch ($op) {
        case 'new':
            $tpl = 'notify_admin_new.tpl';
            // notify suppress will be confused?
            $title = $data['title'];
            $edate = eventdate($data['edate']);
            $xoopsMailer->setSubject(_MD_NEWSUB);
            $note = (STAT_POST == $data['status']) ? _MD_APPROVE_REQ : '';
            $tags = [
                'EVENT_TITLE' => $title,
                'EVENT_DATE'  => $edate,
                'EVENT_NOTE'  => $note,
                'EVENT_URL'   => EGUIDE_URL . '/event.php?eid=' . $data['eid']
            ];
            break;
        case 'update':
            $tpl  = 'notify_admin_change.tpl';
            $tags =& $data;
            $xoopsMailer->setSubject(_MD_UPDATE_SUBJECT);
            break;
    }
    $memberHandler = xoops_getHandler('member');
    $users         = $memberHandler->getUsersByGroup($xoopsModuleConfig['notify_group'], true);
    $uids          = [];
    if (1 == $notify) {
        $uids[] = $GLOBALS['xoopsUser']->getVar('uid');
    } // suppress self
    $uid = $data['uid'];
    if (!in_array($uid, $uids)) { // update by not poster?
        $user = $memberHandler->getUser($uid);
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
    $xoopsMailer->setFromEmail($GLOBALS['xoopsConfig']['adminmail']);
    $xoopsMailer->setFromName(eguide_from_name());

    return $xoopsMailer->send();
}

/**
 * @param $eid
 * @return bool|void
 */
function user_notify($eid)
{
    global $xoopsDB, $xoopsConfig;

    $result = $xoopsDB->query('SELECT title,edate,expire,status,topicid FROM ' . EGTBL . " WHERE eid=$eid");
    if (!$result || 0 == $xoopsDB->getRowsNum($result)) {
        echo "<div class='error'>Not found Event(eid='$eid')</div>\n";

        return;
    }
    $data   = $xoopsDB->fetchArray($result);
    $title  = $data['title'];
    $edate  = $data['edate'];
    $expire = $data['expire'];

    // using XOOPS2 notification system

    if (!$GLOBALS['xoopsModuleConfig']['user_notify']
        || ($expire > $edate ? $expire < time() : ($edate + $expire) < time())
        || STAT_NORMAL != $data['status']) {
        return false;
    }

    $tags                = [
        'EVENT_TITLE' => $title,
        'EVENT_DATE'  => eventdate($edate, _MD_TIME_FMT),
        'EVENT_NOTE'  => '',
        'EVENT_URL'   => EGUIDE_URL . "/event.php?eid=$eid"
    ];
    $notificationHandler = xoops_getHandler('notification');
    $notificationHandler->triggerEvent('global', 0, 'new', $tags);
    $notificationHandler->triggerEvent('category', $data['topicid'], 'new', $tags);

    $result = $xoopsDB->query('SELECT rvid, email, confirm FROM ' . RVTBL . ' WHERE eid=0');
    while ($data = $xoopsDB->fetchArray($result)) {
        $xoopsMailer = xoops_getMailer();
        $xoopsMailer->useMail();
        $xoopsMailer->setSubject(_MD_NEWSUB);
        $tpl = 'notify_user_new.tpl';
        $xoopsMailer->setTemplateDir(template_dir($tpl));
        $xoopsMailer->setTemplate($tpl);
        $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
        $xoopsMailer->setFromName(eguide_from_name());
        $xoopsMailer->assign($tags);
        $xoopsMailer->assign('CANCEL_URL', EGUIDE_URL . '/reserv.php?op=cancel&rvid=' . $data['rvid'] . '&key=' . $data['confirm']);
        $xoopsMailer->setToEmails($data['email']);
        if (!$xoopsMailer->send()) {
            echo "<div class='error'>" . $xoopsMailer->getErrors() . "</div>\n";
        }
    }
}
