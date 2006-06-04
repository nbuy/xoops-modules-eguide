<?php
// Administration Date by Poster
// $Id: editdate.php,v 1.6 2006/06/04 07:04:02 nobu Exp $

include 'header.php';
require 'perm.php';

$eid = param('eid');

$result=$xoopsDB->query('SELECT rvid FROM '.RVTBL." WHERE eid=$eid AND exid=0", 1);
if ($xoopsDB->getRowsNum($result)>0) {
    redirect_header(empty($_SERVER['HTTP_REFERER'])?'admin.php':$_SERVER['HTTP_REFERER'], 1, _NOPERM);
}

include XOOPS_ROOT_PATH.'/header.php';

$result=$xoopsDB->query('SELECT edate, cdate, title, persons FROM '.EGTBL." e
LEFT JOIN ".OPTBL." o ON e.eid=o.eid WHERE e.eid=$eid");
if (!$xoopsDB->getRowsNum($result)) {
    redirect_header('index.php', 2, _MD_NOEVENT);
}
list($edate, $cdate, $title, $persons) = $xoopsDB->fetchRow($result);

$xoopsTpl->assign('xoops_module_header', HEADER_CSS);

$myts =& MyTextSanitizer::getInstance();
echo "<p><a href='event.php?eid=$eid' class='evhead'>".$myts->htmlSpecialChars(eventdate($edate).' '.$title)."</a></p>\n";

$now = time();
$extents = get_extents($eid, true);
if (isset($_POST['adds'])) {
    $dels = empty($_POST['dels'])?array():$_POST['dels'];
    $mods = $_POST['mods'];
    $exps = $_POST['exps'];
    $adds = preg_split('/[\n\r]+/', trim($_POST['adds']));
    $chg = 0;
    foreach ($extents as $data) {
	$id = $data['exid'];
	if (isset($dels[$id])) {
	    if ($data['exdate']>$now && $data['reserved']) {
		echo "<div class='error'>".formatTimestamp($data['exdate'], 'Y-m-d H:i').' - '._MD_DATEDELETE_ERR."</div>\n";
	    } else {
		$xoopsDB->query('DELETE FROM '.RVTBL." WHERE eid=$eid AND exid=$id");
		$xoopsDB->query('DELETE FROM '.EXTBL." WHERE exid=$id");
		$chg++;
	    }
	} elseif (isset($mods[$id])) {
	    $exdate = $data['exdate'];
	    $pre = formatTimestamp($exdate, 'Y-m-d H:i');
	    $v = trim($mods[$id]);
	    if ($pre==$v && $exps[$id]==$data['expersons']) continue;
	    $n = $exps[$id]==''?'null':intval($exps[$id]);
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
	    $tm = userTimeToServerTime(mktime($hour,$min, 0, $mm, $dd, $yy), $xoopsUser->getVar("timezone_offset"));
	    if ($tm >= $edate && $tm > $now) {
		$post = formatTimestamp($tm, 'Y-m-d H:i');
		$xoopsDB->query("UPDATE ".EXTBL." SET exdate=$tm, expersons=$n WHERE eidref=$eid AND exid=$id");
		$chg++;
	    } else {
		echo "<div class='error'>$v - "._MD_DATE_ERR."</div>\n";
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
	    $tm = userTimeToServerTime(mktime($hour,$min, 0, $mm, $dd, $yy), $xoopsUser->getVar("timezone_offset"));
	    if ($tm >= $edate && $tm > $now) {
		$xoopsDB->query('INSERT INTO '.EXTBL."(eidref,exdate)VALUES($eid,$tm)");
		$chg++;
	    } else {
		echo "<div class='error'>$v - "._MD_DATE_ERR."</div>\n";
	    }
	}
    }
    if ($chg) {
	$extents = get_extents($eid, true);
	$xoopsDB->query('UPDATE '.EGTBL." SET ldate=0 WHERE eid=$eid");
    }
}

$n=count($extents);
echo '<div>'._MD_POSTDATE.' '.formatTimestamp($cdate, _MD_POSTED_FMT)." &nbsp; ".sprintf(_MD_INFO_COUNT,$n)." &nbsp; "._MD_RESERV_PERSONS.": $persons"._MD_RESERV_UNIT."</div>\n";

echo "<form action='editdate.php?eid=$eid' method='post'>";
if ($n) {
    echo "<table class='outer'>\n";
    echo "<tr><th>"._DELETE."</th><th>"._MD_EXTENT_DATE.
	"</th><th colspan='2'>"._MD_ORDER_COUNT."</th><th>"._EDIT.
	sprintf(" (%s, %s)",_MD_EXTENT_DATE, _MD_RESERV_PERSONS).
	"</th></tr>\n";
    $n = 0;
    foreach ($extents as $data) {
	$id = $data['exid'];
	$tm = $data['exdate'];
	$date = eventdate($tm);
	$edit = formatTimestamp($tm, 'Y-m-d H:i');
	$resv = $data['reserved'];
	$max = empty($data['expersons'])?'-':$data['expersons'];
	$bg = ($n++%2)?'even':'odd';
	if ($tm>$now) {
	    $input = "<input name='mods[$id]' value='$edit' size='18'/>".
		" &nbsp; <input name='exps[$id]' size='2' value='".
		$data['expersons']."'>";
	} else {
	    $input = "";
	}
	$check = ($resv&&$tm>$now)?"-":"<input type='checkbox' name='dels[$id]' value='$id'/>";
	echo "<tr class='$bg'><td align='center'>$check</td>".
	    "<td><a href='event.php?eid=$eid&amp;sub=$id'>$date</a></td>".
	    "<td align='right'>$resv</td><td>$max</td>".
	    "<td>$input</td>".
	    "</tr>\n";
    }
    echo "</table>\n";
}
$adds = htmlspecialchars(param('adds', ''));
echo _MD_ADD_EXTENT." <div><textarea name='adds'>$adds</textarea></div>\n";
echo "<div class='evinfo'>"._MD_ADD_EXTENT_DESC."</div>\n";
echo "<p><input type='submit' value='"._MD_UPDATE."'/></p>\n";
echo "</form>\n";

include XOOPS_ROOT_PATH.'/footer.php';
?>