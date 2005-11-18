<?php
// Administration Date by Poster
// $Id: editdate.php,v 1.1 2005/11/18 17:08:02 nobu Exp $

include 'header.php';
include 'perm.php';

// need switch normal xoops
$myts =& MyTextSanitizer::getInstance();
$eid = param('eid');

include XOOPS_ROOT_PATH.'/header.php';

$extents = get_extents($eid, 0);
if (isset($_POST['adds'])) {
    $dels = $_POST['dels'];
    $mods = $_POST['mods'];
    $adds = preg_split('/[\n\r]+/', trim($_POST['adds']));
    $chg = 0;
    foreach ($extents as $data) {
	$id = $data['exid'];
	if (isset($dels[$id])) {
	    $xoopsDB->query('DELETE FROM '.RVTBL." WHERE eid=$eid AND exid=$id");
	    $xoopsDB->query('DELETE FROM '.EXTBL." WHERE exid=$id");
	    $chg++;
	} elseif (isset($mods[$id])) {
	    $exdate = $data['exdate'];
	    $pre = formatTimestamp($exdate, 'Y-m-d H:i');
	    if ($pre==$mods[$id]) continue;
	    list($date, $time) = split(' ', trim($mods[$id]));
	    if (preg_match('/^(\d+)[-\/](\d\d?)[-\/](\d\d?)$/', $date, $d)) {
		$yy = $d[1]; $mm=$d[2]; $dd = $d[3];
	    } else continue;
	    if (preg_match('/^(\d\d?):(\d\d?)/', $time, $d)) {
		$hour=$d[1]; $min = $d[2];
	    } else {
		$hh = formatTimestamp($exdate, 'H');
		$mm = formatTimestamp($exdate, 'i');
	    }
	    $tm = userTimeToServerTime(mktime($hour,$min, 0, $mm, $dd, $yy));
	    if ($exdate != $tm) {
		$post = formatTimestamp($tm, 'Y-m-d H:i');
		$xoopsDB->query('UPDATE '.EXTBL.' SET exdate='.$tm." WHERE eidref=$eid AND exid=$id");
		$chg++;
	    }
	}
    }
    if (!empty($adds[0])) {
	$result = $xoopsDB->query('SELECT edate FROM '.EGTBL.' WHERE eid='.$eid);
	list($edate) = $xoopsDB->fetchRow($result);
	$defh = formatTimestamp($edate, 'H');
	$defi = formatTimestamp($edate, 'i');
	foreach ($adds as $v) {
	    list($date, $time) = split(' ', trim($v));
	    if (preg_match('/^(\d+)[-\/](\d\d?)[-\/](\d\d?)$/', $date, $d)) {
		$yy = $d[1]; $mm=$d[2]; $dd = $d[3];
	    } else continue;
	    if (preg_match('/^(\d\d?):(\d\d?)/', $time, $d)) {
		$hour=$d[1]; $min = $d[2];
	    } else {
		$hour = $defh; $min = $defi;
	    }
	    $tm = userTimeToServerTime(mktime($hour,$min, 0, $mm, $dd, $yy));
	    $xoopsDB->query('INSERT INTO '.EXTBL."(eidref,exdate)VALUES($eid,$tm)");
	    $chg++;
	}
    }
    if ($chg) $extents = get_extents($eid, 0);
}

$xoopsTpl->assign('xoops_module_header','<link rel="stylesheet" type="text/css" media="all" href="event.css" />');
$result=$xoopsDB->query('SELECT edate, expire FROM '.EGTBL." WHERE eid=$eid");
if (!$xoopsDB->getRowsNum($result)) {
    redirect_header('index.php', 2, _MD_NOEVENT);
}
list($edate, $expire) = $xoopsDB->fetchRow($result);
echo '<div>'._MD_EVENT_DATE.' '.formatTimestamp($edate, _MD_TIME_FMT)."</div>\n";
echo '<div>'._MD_EVENT_EXPIRE.' '.formatTimestamp($expire, _MD_TIME_FMT)."</div>\n";

echo "<form action='editdate.php?eid=23' method='post'>";
if (count($extents)) {
    echo "<table class='outer'>\n";
    echo "<tr><th>"._DELETE."</th><th>"._MD_EXTENT_DATE."</th><th>"._MD_ORDER_COUNT."</th><th>"._EDIT."</th></tr>\n";
    $n = 0;
    foreach ($extents as $data) {
	$id = $data['exid'];
	$date = eventdate($data['exdate']);
	$edit = formatTimestamp($data['exdate'], 'Y-m-d H:i');
	$resv = $data['reserved'];
	$bg = ($n++%2)?'even':'odd';
	echo "<tr class='$bg'><td align='center'>".
	    "<input type='checkbox' name='dels[$id]' value='$id' /></td>".
	    "<td>$date</td><td>$resv</td>".
	    "<td><input name='mods[$id]' value='$edit' size='20'/><td>".
	    "</tr>\n";
    }
    echo "</table>\n";
}

echo _MD_ADD_EXTENT." <div><textarea name='adds'></textarea></div>\n";
echo "<div class='evinfo'>"._MD_ADD_EXTENT_DESC."</div>\n";
echo "<p><input type='submit' value='"._MD_UPDATE."'/></p>\n";
echo "</form>\n";

include XOOPS_ROOT_PATH.'/footer.php';
?>