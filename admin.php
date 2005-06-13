<?php
// Event Administration by Poster
// $Id: admin.php,v 1.6 2005/06/13 05:17:34 nobu Exp $

include("header.php");
include_once(XOOPS_ROOT_PATH."/class/xoopscomments.php");
include_once("language/".$xoopsConfig['language']."/admin.php");
include_once("notify.inc.php");

// need switch normal xoops
$myts =& MyTextSanitizer::getInstance();

include("perm.php");

foreach ($HTTP_POST_VARS as $i => $v) {
    $$i = post_filter($v);
}

foreach (array("op","eid") as $v) {
    if (isset($HTTP_GET_VARS[$v])) $$v = $HTTP_GET_VARS[$v];
}

if (isset($HTTP_POST_VARS['save'])) $op = "save";
elseif (isset($HTTP_POST_VARS['preview'])) $op = "preview";
elseif (empty($op)) $op = 'new';

$edate = getDateField("edate");
if (isset($expire)) {
    $expire = $edate + $expire;
} else {
    $expire = getDateField("expire");
}

$tbl = $xoopsDB->prefix("eguide");
$opt = $xoopsDB->prefix("eguide_opt");
$rsv = $xoopsDB->prefix("eguide_reserv");

// store in database
$adm = $xoopsUser->isAdmin($xoopsModule->mid());

if ($op=='save') {
    $uid=$xoopsUser->uid();
    $title=addslashes($title);
    $summary=addslashes($summary);
    $body=addslashes($body);
    $now = time();
    $reservation = (isset($reservation)&&$reservation==1)?1:0;
    $strict = (isset($strict)&&$strict==1)?1:0;
    $autoaccept = (isset($autoaccept)&&$autoaccept==1)?1:0;
    $notify = (isset($notify)&&$notify==1)?1:0;
    $persons = isset($persons)?intval($persons):0;
    $optfield = addslashes($optfield);
    $style = isset($style)?intval($style):1;
    if (isset($eid)) {
	$cond = $adm?"":" AND uid=$uid";
	$result = $xoopsDB->query("SELECT status FROM $tbl WHERE eid=$eid");
	$prev = array_shift($xoopsDB->fetchArray($result));
	$xoopsDB->query("UPDATE $tbl ".
			"SET title='$title', mdate=$now, edate='$edate', ".
			"expire=$expire, summary='$summary', body='$body', ".
			"style=$style, status='$status' ".
			"WHERE eid=$eid$cond");
    } else {
	$prev = STAT_POST;
	$xoopsDB->query("INSERT INTO $tbl ".
			"(uid, title, cdate, mdate, edate, expire, summary, body, style, status)".
			" VALUES($uid, '$title', $now, $now, $edate, $expire, '$summary', '$body', $style, $status)");
	$res = $xoopsDB->query("SELECT eid,title,edate,status FROM $tbl WHERE uid=$uid AND mdate=$now");
	$data = $xoopsDB->fetchArray($res);
	event_notify("new", $data);
	$eid = $data['eid'];
    }
    if (empty($eid)) {
	echo "<div class='error'>Internal Error: table '$tbl' in admin.php</div>\n";
	exit();
    }
    if ($prev!=$status) user_notify($eid);
    $result = $xoopsDB->query("SELECT eid FROM $opt WHERE eid=$eid");
    if ($xoopsDB->getRowsNum($result)) {
	$xoopsDB->query("UPDATE $opt ".
			"SET reservation=$reservation, strict=$strict, ".
			"autoaccept=$autoaccept, notify=$notify, ".
			"persons=$persons, optfield='$optfield' ".
			"WHERE eid=$eid");
    } else {
	$xoopsDB->query("INSERT INTO $opt".
			"(eid, reservation, strict, autoaccept, notify, persons, optfield)".
			" VALUES($eid, $reservation, $strict, $autoaccept, $notify, $persons, '$optfield')");
    }
    redirect_header("event.php?eid=$eid",2,_AM_DBUPDATED);
    exit;
} elseif ($op=='confirm') {
    if ($adm) {			// delete by admin
	$result = $xoopsDB->query("DELETE FROM $tbl WHERE eid=$eid");
	$result = $xoopsDB->query("DELETE FROM $opt WHERE eid=$eid");
	$result = $xoopsDB->query("DELETE FROM $rsv WHERE eid=$eid");
    } else {			// delete by poster
	$result = $xoopsDB->query("UPDATE $tbl SET status=".STAT_DELETED." WHERE eid=$eid");
    }
    redirect_header("index.php",2,_AM_DBDELETED);
    exit();
}

include_once(XOOPS_ROOT_PATH."/header.php");
$inc = XOOPS_ROOT_PATH."/themes/".$xoopsTheme['thename'];
if ( file_exists("$inc/themeevent.php") ) {
    include("$inc/themeevent.php");
} else {
    include("themeevent.php");
}

// preview
if ($op=='preview') {
    OpenTable();
    $strict = (isset($strict)&&$strict==1)?1:0;
    $autoaccept = (isset($autoaccept)&&$autoaccept==1)?1:0;
    $notify = (isset($notify)&&$notify==1)?1:0;
    themeevent(array("edate"=>$edate,"cdate"=>time(),
		     "title"=>$title, "summary"=>$summary, "body"=>$body,
		     "style"=>$style, "uid"=>$xoopsUser->uid()), "");
    eventform(array("reservation"=>$reservation,"optfield"=>$optfield));
    CloseTable();
    echo "<br />\n";
} elseif (isset($eid)) {
    $result = $xoopsDB->query("SELECT * FROM $tbl WHERE eid=$eid");
    $data = $xoopsDB->fetchArray($result);
    if ($op=='delete') {
	OpenTable();
	unset($data['eid']);	// No Admin Anchors
	unset($data['body']);
	themeevent($data, "");
	echo "<br /><div><form action='admin.php' method='post'>
<input type='hidden' name='op' value='confirm' />
<input type='hidden' name='eid' value='$eid' />
<input type='submit' value='"._DELETE."' />
</form><b>"._AM_EVENT_DEL_DESC."</b></div>\n";
	if ($adm) echo "<div style='color: #c00000;'>"._AM_EVENT_DEL_ADMIN."</div>\n";
	CloseTable();
	include(XOOPS_ROOT_PATH."/footer.php");
	exit;
    }
    foreach ($data as $i => $v) {
	$$i = $v;
    }
    $result = $xoopsDB->query("SELECT * FROM $opt WHERE eid=$eid");
    
    if ($opts = $xoopsDB->fetchArray($result)) {
	foreach ($opts as $i => $v) {
	    $$i = $v;
	}
    }
}

OpenTable();
if (empty($summary)) $summary="";
if (empty($body)) $body="";
if (empty($topicdisplay)) {$topicdisplay="0"; $published=0; }
if (empty($type)) $type="";
echo "<div class='evhead'>".(isset($eid)?_AM_EDITARTICLE:_AM_NEWSUB)."</div>";
include("eventform.inc.php");
CloseTable();

include(XOOPS_ROOT_PATH."/footer.php");
exit;

// make to unix time from separate fields.
function getDateField($p) {
    global $HTTP_POST_VARS;
    if (empty($HTTP_POST_VARS["${p}year"])) return 0;
    return mktime($HTTP_POST_VARS["${p}hour"],$HTTP_POST_VARS["${p}min"], 0,
		  $HTTP_POST_VARS["${p}month"], $HTTP_POST_VARS["${p}day"], $HTTP_POST_VARS["${p}year"]);
}
?>