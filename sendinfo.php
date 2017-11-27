<?php
// Send Event Information
//

include __DIR__ . '/header.php';
require_once __DIR__ . '/perm.php';

$op   = param('op', 'form');
$eid  = param('eid');
$exid = param('sub');

include XOOPS_ROOT_PATH . '/header.php';
assign_module_css();

$result = $xoopsDB->query('SELECT IF(exdate,exdate,edate) edate, title, uid FROM ' . EGTBL . ' LEFT JOIN ' . EXTBL . " ON eid=eidref AND exid=$exid WHERE eid=$eid");
$data   = $xoopsDB->fetchArray($result);
$edate  = eventdate($data['edate']);
$title  = $edate . ' ' . htmlspecialchars($data['title']);

$evurl = EGUIDE_URL . "/event.php?eid=$eid" . ($exid ? "&sub=$exid" : '');
echo "<p><a href='$evurl' class='evhead'>$title</a></p>\n";
if ('doit' === $op) {
    $title       = param('title', '');
    $xoopsMailer = xoops_getMailer();
    $xoopsMailer->useMail();
    $xoopsMailer->setSubject($title);
    $xoopsMailer->setBody(param('body', ''));
    $xoopsMailer->setFromEmail($xoopsUser->email());
    $xoopsMailer->setFromName(eguide_from_name());
    $xoopsMailer->assign('EVENT_URL', EGUIDE_URL . "/event.php?eid=$eid");
    $req    = param('request') ? ' OR eid=0' : '';
    $status = param('status');
    if (empty($status)) {
        $status = _RVSTAT_RESERVED;
    }
    $result = $xoopsDB->query('SELECT email,uid FROM ' . RVTBL . " WHERE (eid=$eid AND status=$status AND exid=$exid)$req");
    $emails = [];
    $uids   = [];
    while ($data = $xoopsDB->fetchArray($result)) {
        if (empty($data['uid'])) {
            $emails[] = $data['email'];
        } else {
            $uids[] = $data['uid'];
        }
    }
    if (param('self')) {
        $uids[] = $xoopsUser->getVar('uid');
    } // send self
    $emails = array_unique($emails);
    if ($emails) {
        $xoopsMailer->setToEmails($emails);
    }
    $memberHandler = xoops_getHandler('member');
    $users         = [];
    foreach (array_unique($uids) as $uid) {
        $users[] = $memberHandler->getUser($uid);
    }
    if ($users) {
        $xoopsMailer->setToUsers($users);
    }
    if ($xoopsMailer->send()) {
        echo '<p><b>' . _MD_INFO_MAILOK . "</b></p>\n";
        echo $xoopsMailer->getSuccess();
    } else {
        echo "<p><div class='error'>" . _MD_INFO_MAILOK . "</div></p>\n";
        echo $xoopsMailer->getErrors();
    }
} else {
    $result = $xoopsDB->query('SELECT status, count(rvid) FROM ' . RVTBL . " WHERE eid=$eid AND exid=$exid GROUP BY status");

    echo "<h3 class='page-header'>" . _MD_INFO_TITLE . "</h3>\n";
    echo "<form action='sendinfo.php' method='post'>\n";
    echo $GLOBALS['xoopsSecurity']->getTokenHTML();
    echo "<div class='form-group'><label>" . _MD_INFO_CONDITION . '</label> ';
    if ($xoopsDB->getRowsNum($result)) {
        echo "<select name='status' class='form-control'>\n";
        while ($data = $xoopsDB->fetchArray($result)) {
            $s  = $data['status'];
            $n  = $data['count(rvid)'];
            $ck = (_RVSTAT_RESERVED == $s) ? ' selected' : '';
            echo "<option value='$s'$ck>" . $rv_stats[$s] . ' - ' . sprintf(_MD_INFO_COUNT, $n) . "</option>\n";
        }
        echo '</select></div>';
    }
    $result = $xoopsDB->query('SELECT count(rvid) FROM ' . RVTBL . ' WHERE eid=0');
    list($ord) = $xoopsDB->fetchRow($result);
    $notify = $ord ? ("<div class='checkbox'><label><input type='checkbox' name='request'> " . _MD_INFO_REQUEST . ' (' . sprintf(_MD_INFO_COUNT, $ord) . ')</label></div>') : '';
    echo "$notify\n";
    echo "<input type='hidden' name='op' value='doit'>\n"
         . "<input type='hidden' name='eid' value='$eid'>\n"
         . "<input type='hidden' name='sub' value='$exid'>\n"
         . "<div class='form-group'><label>"
         . _MD_TITLE
         . '</label> '
         . "<input type='text' name='title' size='60' value='Re: $title' class='form-control'></div>\n"
         . "<textarea name='body' cols='60' rows='10' class='form-control'>"
         . _MD_INFO_DEFAULT
         . "</textarea>\n"
         . "<div class='checkbox'><label><input type='checkbox' name='self' value='1' checked> "
         . sprintf(_MD_INFO_SELF, $xoopsUser->email())
         . "</label></div>\n"
         . "<input type='submit' value='"
         . _SUBMIT
         . "' class='btn btn-primary'>\n"
         . '</form>';
}

include XOOPS_ROOT_PATH . '/footer.php';
exit;
