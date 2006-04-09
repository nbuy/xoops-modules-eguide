<?php
// Send Event Information
// $Id: sendinfo.php,v 1.10 2006/04/09 17:31:33 nobu Exp $

include 'header.php';
require 'perm.php';

$op = param('op', 'form');
$eid = param('eid');
$exid = param('sub');

include(XOOPS_ROOT_PATH."/header.php");

$result = $xoopsDB->query("SELECT edate, title, uid, exdate FROM ".EGTBL." LEFT JOIN ".EXTBL." ON eid=eidref AND exid=$exid WHERE eid=$eid");
$data = $xoopsDB->fetchArray($result);
$edate = empty($data['exdate'])?$data['edate']:$data['exdate'];
$title = "Re: ".eventdate($edate)." ".htmlspecialchars($data['title']);

if ($op=="doit") {
    $xoopsMailer =& getMailer();
    $xoopsMailer->useMail();
    $xoopsMailer->setSubject($title);
    $xoopsMailer->setBody(param('body',''));
    $xoopsMailer->setFromEmail($xoopsUser->email());
    $xoopsMailer->setFromName(_MD_FROM_NAME);
    $xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=$eid");
    $req = param('request')?" OR eid=0":"";
    $status = param('status');
    if (empty($status)) $status=_RVSTAT_RESERVED;
    $result = $xoopsDB->query("SELECT email,uid FROM ".RVTBL." WHERE (eid=$eid AND status=$status AND exid=$exid)$req");
    $emails = array();
    $uids = array();
    while ($data = $xoopsDB->fetchArray($result)) {
	if (empty($data['uid'])) $emails[] = $data['email'];
	else $uids[] = $data['uid'];
    }
    if (param('self')) $uids[] = $xoopsUser->getVar('uid'); // send self
    $emails = array_unique($emails);
    if ($emails) $xoopsMailer->setToEmails($emails);
    $member_handler =& xoops_gethandler('member');
    $users = array();
    foreach (array_unique($uids) as $uid) {
	$users[] =& $member_handler->getUser($uid);
    }
    if ($users) $xoopsMailer->setToUsers($users);
    if ($xoopsMailer->send()) {
	echo "<p><b>"._MD_INFO_MAILOK."</b></p>\n";
	echo $xoopsMailer->getSuccess();
    } else {
	echo "<p><div class='error'>"._MD_INFO_MAILOK."</div></p>\n";
	echo $xoopsMailer->getErrors();
    }
} else {
    $result = $xoopsDB->query("SELECT status, count(rvid) FROM ".RVTBL." WHERE eid=$eid AND exid=$exid GROUP BY status");
    
    echo "<h4>"._MD_INFO_TITLE."</h4>\n";
    echo "<form action='sendinfo.php' method='post'>\n";
    echo "<b>"._MD_INFO_CONDITION."</b> ";
    if ($xoopsDB->getRowsNum($result)) {
	echo "<select name='status'>\n";
	while ($data = $xoopsDB->fetchArray($result)) {
	    $s = $data['status'];
	    $n = $data['count(rvid)'];
	    $ck = ($s == _RVSTAT_RESERVED)?" selected":"";
	    echo "<option value='$s'$ck>".$rv_stats[$s]." - ".
		sprintf(_MD_INFO_COUNT, $n)."</option>\n";
	}
	echo "</select>";
    }
    $result = $xoopsDB->query("SELECT count(rvid) FROM ".RVTBL." WHERE eid=0");
    list($ord)=$xoopsDB->fetchRow($result);
    $notify = ($ord)?("&nbsp;&nbsp; <input type='checkbox' name='request' /> "._MD_INFO_REQUEST." (".sprintf(_MD_INFO_COUNT, $ord).")"):"";
    echo "$notify<br />\n";
    echo "<input type='hidden' name='op' value='doit' />\n".
	"<input type='hidden' name='eid' value='$eid' />\n".
	"<input type='hidden' name='sub' value='$exid' />\n".
	"<p><b>"._MD_TITLE."</b> ".
	"<input name='title' size='60' value='$title' /></p>\n".
	"<p><textarea name='body' cols='60' rows='10'>"._MD_INFO_DEFAULT."</textarea></p>\n".
	"<p><input type='checkbox' name='self' value='1' checked /> ".sprintf(_MD_INFO_SELF, $xoopsUser->email())."</p>\n".
	"<input type='submit' value='"._SUBMIT."' />\n".
	"</form>";
}

include(XOOPS_ROOT_PATH."/footer.php");
exit;

?>