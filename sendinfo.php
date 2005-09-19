<?php
// Send Event Information
// $Id: sendinfo.php,v 1.5 2005/09/19 07:05:58 nobu Exp $

include("header.php");
include_once(XOOPS_ROOT_PATH."/class/xoopscomments.php");
include_once("language/".$xoopsConfig['language']."/admin.php");

include("perm.php");

$tbl = $xoopsDB->prefix("eguide");
$opt = $xoopsDB->prefix("eguide_opt");
$rsv = $xoopsDB->prefix("eguide_reserv");

foreach ($_POST as $i => $v) {
    $$i = post_filter($v);
}
foreach (array("op","eid") as $v) {
    if (isset($_GET[$v])) $$v = $_GET[$v];
}

include(XOOPS_ROOT_PATH."/header.php");
OpenTable();
if (isset($op) && $op=="doit") {
    $result = $xoopsDB->query("SELECT edate, title FROM $tbl WHERE eid=$eid");
    $data = $xoopsDB->fetchArray($result);
    $title = "Re: ".date(_MD_DATE_FMT, $data['edate'])." ".htmlspecialchars($data['title']);
    $xoopsMailer =& getMailer();
    $xoopsMailer->useMail();
    $xoopsMailer->setSubject($title);
    $xoopsMailer->setBody($body);
    $xoopsMailer->setFromEmail($xoopsUser->email());
    $xoopsMailer->setFromName("Event Information");
    $xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=$eid");
    $req = (isset($request))?" OR eid=0":""; 
    if (empty($status)) $status=1;
    $result = $xoopsDB->query("SELECT email FROM $rsv WHERE (eid=$eid AND status=$status)$req GROUP BY email");
    while ($data = $xoopsDB->fetchArray($result)) {
	$xoopsMailer->setToEmails($data['email']);
    }
    if (isset($self)&&$self) $xoopsMailer->setToUsers($xoopsUser); // send self
    if ($xoopsMailer->send()) {
	echo "<p><b>"._AM_INFO_MAILOK."</b></p>\n";
	echo $xoopsMailer->getSuccess();
    } else {
	echo "<p><div class='error'>"._AM_INFO_MAILOK."</div></p>\n";
	echo $xoopsMailer->getErrors();
    }
} else {
    $result = $xoopsDB->query("SELECT edate, title, uid FROM $tbl WHERE eid=$eid");
    $data = $xoopsDB->fetchArray($result);
    $title = "Re: ".date(_MD_DATE_FMT, $data['edate'])." ".htmlspecialchars($data['title']);
    $result = $xoopsDB->query("SELECT status, count(rvid) FROM $rsv WHERE eid=$eid GROUP BY status");
    
    echo "<h4>"._AM_INFO_TITLE."</h4>\n";
    echo "<form action='sendinfo.php' method='post'>\n";
    echo "<b>"._AM_INFO_CONDITION."</b> ";
    if ($xoopsDB->getRowsNum($result)) {
	echo "<select name='status'>\n";
	while ($data = $xoopsDB->fetchArray($result)) {
	    $s = $data['status'];
	    $n = $data['count(rvid)'];
	    $ck = ($s == _AM_RVSTAT_RESERVED)?" selected":"";
	    echo "<option value='$s'$ck>".$rv_stats[$s]." - ".
		sprintf(_AM_INFO_COUNT, $n)."</option>\n";
	}
	echo "</select>";
    }
    $result = $xoopsDB->query("SELECT count(rvid) FROM $rsv WHERE eid=0");
    list($ord)=$xoopsDB->fetchRow($result);
    $notify = ($ord)?("&nbsp;&nbsp; <input type='checkbox' name='request' /> "._AM_INFO_REQUEST." (".sprintf(_AM_INFO_COUNT, $ord).")"):"";
    echo "$notify<br />\n";
    echo "<input type='hidden' name='op' value='doit' />\n".
	"<input type='hidden' name='eid' value='$eid' />\n".
	"<p><b>"._AM_TITLE."</b> ".
	"<input name='title' size='60' value='$title' /></p>\n".
	"<p><textarea name='body' cols='60' rows='10'>"._AM_INFO_DEFAULT."</textarea></p>\n".
	"<p><input type='checkbox' name='self' value='1' checked /> ".sprintf(_AM_INFO_SELF, $xoopsUser->email())."</p>\n".
	"<input type='submit' value='"._SUBMIT."' />\n".
	"</form>";
}

CloseTable();
include(XOOPS_ROOT_PATH."/footer.php");
exit;

?>