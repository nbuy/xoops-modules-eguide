<?php
// Send Event Information
// $Id: sendinfo.php,v 1.7 2005/11/18 17:08:03 nobu Exp $

include 'header.php';
include 'perm.php';

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
    $xoopsMailer->setBody($body);
    $xoopsMailer->setFromEmail($xoopsUser->email());
    $xoopsMailer->setFromName("Event Information");
    $xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=$eid");
    $req = (isset($request))?" OR eid=0":""; 
    if (empty($status)) $status=1;
    $result = $xoopsDB->query("SELECT email FROM ".RVTBL." WHERE (eid=$eid AND status=$status AND exid=$exid)$req GROUP BY email");
    while ($data = $xoopsDB->fetchArray($result)) {
	$xoopsMailer->setToEmails($data['email']);
    }
    if (isset($self)&&$self) $xoopsMailer->setToUsers($xoopsUser); // send self
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