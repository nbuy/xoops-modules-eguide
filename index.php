<?php
include("header.php");
if ( $xoopsConfig['startpage'] == $xoopsModule->dirname() ) {

	$xoopsOption['show_rblock'] =1;
	include(XOOPS_ROOT_PATH."/header.php");
	if ( empty($start) && empty($prev) ) {
		make_cblock();
	}
} else {
	$xoopsOption['show_rblock'] =0;
	include(XOOPS_ROOT_PATH."/header.php");
}

$inc = XOOPS_ROOT_PATH."/themes/".$xoopsTheme['thename'];
if ( file_exists("$inc/themeevent.php") ) {
    include("$inc/themeevent.php");
} else {
    include("themeevent.php");
}

$now = time();
if (empty($prev)) {
    $expire = "expire>$now";
} else {
    $expire = "expire<$now";
}
$tbl = $xoopsDB->prefix("eguide");
$max = $eventConfig['max_event'];
if (isset($prev)) {
    $start = ($prev-1)*$max;
} else {
    $start = (isset($page)?$page-1:0)*$max;
}
$result = $xoopsDB->query("SELECT eid, cdate, edate, title, summary, uid, status, style, counter  FROM $tbl WHERE $expire AND status=".STAT_NORMAL." ORDER BY edate LIMIT $start,$max");
if ($xoopsDB->getRowsNum($result)==0) {
	OpenTable();
	echo _MD_EVENT_NONE;
	CloseTable();
} else {
    while ($data = $xoopsDB->fetchArray($result)) {
	$mlink = "<a href='event.php?eid=".$data['eid']."'>"._MD_READMORE."</a>";
	OpenTable();
	themeevent($data, $mlink);
	CloseTable();
    }
}

if (empty($prev)) {
    $result = $xoopsDB->query("SELECT eid FROM $tbl WHERE expire<$now AND status=".STAT_NORMAL);
    $p = $xoopsDB->getRowsNum($result);
    $start += $max;
    $result = $xoopsDB->query("SELECT eid FROM $tbl WHERE expire>$now AND status=".STAT_NORMAL." LIMIT $start,$max");
    $q = $xoopsDB->getRowsNum($result);
    if (empty($page) || $page=1) {
	$prev="?prev=1";
	$page="?page=2";
    } else {
	$prev="?page=".$page-1;
	$page="?page=".$page+1;
    }
} else {
    $result = $xoopsDB->query("SELECT eid FROM $tbl WHERE expire<$now AND status=".STAT_NORMAL." LIMIT ".$eventConfig['max_event'].", ".$eventConfig['max_event']);
    $p = $xoopsDB->getRowsNum($result);
    $result = $xoopsDB->query("SELECT eid FROM $tbl WHERE expire>$now AND status=".STAT_NORMAL);
    $q = $xoopsDB->getRowsNum($result);
    if ($prev==1) {
	$prev="?prev=".($prev+1);
	$page="";
    } else {
	$prev="?prev=".($prev-1);
	$page="?prev=".($prev+1);
    }
}
echo "<table width='95%'><tr><td>";
if ($p) {
    echo "<a href='index.php$prev'>&lt;&lt; "._MD_SHOW_PREV."</a>";
}
echo "</td><td align='right'>";
if ($q) {
    echo "<a href='index.php$page'>"._MD_SHOW_NEXT." &gt;&gt;</a>";
}
echo "</td></tr></table>";

include_once(XOOPS_ROOT_PATH."/footer.php");
?>