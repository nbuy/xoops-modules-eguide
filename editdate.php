<?php
// Administration Date by Poster
// $Id: editdate.php,v 1.2 2005/11/19 18:32:34 nobu Exp $

include 'header.php';
require 'perm.php';

// need switch normal xoops
$eid = param('eid');

include XOOPS_ROOT_PATH.'/header.php';

$result=$xoopsDB->query('SELECT edate, expire, title FROM '.EGTBL." WHERE eid=$eid");
if (!$xoopsDB->getRowsNum($result)) {
    redirect_header('index.php', 2, _MD_NOEVENT);
}
list($edate, $expire, $title) = $xoopsDB->fetchRow($result);

$xoopsTpl->assign('xoops_module_header','<link rel="stylesheet" type="text/css" media="all" href="event.css" />');

$myts =& MyTextSanitizer::getInstance();
echo '<h2>'.$myts->htmlSpecialChars(eventdate($edate).' '.$title)."</h2>\n";

$now = time();
$extents = get_extents($eid, 0, true);
if (isset($_POST['adds'])) {
    $dels = empty($_POST['dels'])?array():$_POST['dels'];
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
	    $v = trim($mods[$id]);
	    if ($pre==$v) continue;
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
		if ($tm >= $edate && $tm < $expire && $tm > $now) {
		    $post = formatTimestamp($tm, 'Y-m-d H:i');
		    $xoopsDB->query('UPDATE '.EXTBL.' SET exdate='.$tm." WHERE eidref=$eid AND exid=$id");
		    $chg++;
		} else {
		    echo "<div class='error'>$v - "._MD_DATE_ERR."</div>\n";
		}
	    }
	}
    }
    if (!empty($adds[0])) {
	$defh = formatTimestamp($edate, 'H');
	$defi = formatTimestamp($edate, 'i');
	foreach ($adds as $v) {
	    $v = trim($v);
	    if (empty($v)) continue;
	    if (preg_match('/ /', $v)) {
		list($date, $time) = split(' ', $v);
	    } else {
		$date = $v;
		$time = '';
	    }
	    if (preg_match('/^(\d+)[-\/](\d\d?)[-\/](\d\d?)$/', $date, $d)) {
		$yy = $d[1]; $mm=$d[2]; $dd = $d[3];
	    } else continue;
	    if (preg_match('/^(\d\d?):(\d\d?)/', $time, $d)) {
		$hour=$d[1]; $min = $d[2];
	    } else {
		$hour = $defh; $min = $defi;
	    }
	    $tm = userTimeToServerTime(mktime($hour,$min, 0, $mm, $dd, $yy));
	    if ($tm >= $edate && $tm < $expire && $tm > $now) {
		$xoopsDB->query('INSERT INTO '.EXTBL."(eidref,exdate)VALUES($eid,$tm)");
		$chg++;
	    } else {
		echo "<div class='error'>$v - "._MD_DATE_ERR."</div>\n";
	    }
	}
    }
    if ($chg) {
	$extents = get_extents($eid, 0, true);
	$xoopsDB->query('UPDATE '.EGTBL." SET ldate=0 WHERE eid=$eid");
    }
}

$n=count($extents);
echo '<div>'._MD_EVENT_DATE.' '.formatTimestamp($edate, _MD_TIME_FMT).
" &nbsp; "._MD_EVENT_EXPIRE.' '.formatTimestamp($expire, _MD_TIME_FMT).
" &nbsp; ".sprintf(_MD_INFO_COUNT,$n)."</div>\n";

echo "<form action='editdate.php?eid=$eid' method='post'>";
if ($n) {
    echo "<table class='outer'>\n";
    echo "<tr><th>"._DELETE."</th><th>"._MD_EXTENT_DATE."</th><th>"._MD_ORDER_COUNT."</th><th>"._EDIT."</th></tr>\n";
    $n = 0;
    foreach ($extents as $data) {
	$id = $data['exid'];
	$tm = $data['exdate'];
	$date = eventdate($tm);
	$edit = formatTimestamp($tm, 'Y-m-d H:i');
	$resv = $data['reserved'];
	$bg = ($n++%2)?'even':'odd';
	$input = ($tm>$now)?"<input name='mods[$id]' value='$edit' size='16'/>":"";
	echo "<tr class='$bg'><td align='center'>".
	    "<input type='checkbox' name='dels[$id]' value='$id' /></td>".
	    "<td>$date</td><td>$resv</td>".
	    "<td>$input<td>".
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