<?php
include("header.php");
$inc = XOOPS_ROOT_PATH."/modules/image/class.php";
if (file_exists($inc)) include_once($inc);

if (empty($eid)) {
	redirect_header("index.php",2,_MD_NOEVENT);
	exit();
}
$myts =& MyTextSanitizer::getInstance();

$tbl = $xoopsDB->prefix("eguide");
$stc=($xoopsUser && $xoopsUser->isAdmin($xoopsModule->mid())?"":"AND status=".STAT_NORMAL);
$result = $xoopsDB->query("SELECT * FROM $tbl WHERE eid=$eid $stc");
$data = $xoopsDB->fetchArray($result);

$xoopsConfig['title'] = $myts->sanitizeForDisplay($data['title']);
if (empty($data['eid'])) {
	redirect_header("index.php",2,_MD_NOEVENT);
	exit();
}
if (empty($op)) $op = "view";
if ($op == "print") {
    include("print.php");
    PrintPage($data);
    exit();
} else {
    if (!$xoopsUser || $data['uid']!=$xoopsUser->uid()) {
	$xoopsDB->queryF("UPDATE $tbl SET counter=counter+1 WHERE eid=$eid");
	$data['counter']++;
    }
    include(XOOPS_ROOT_PATH."/header.php");
}

$inc = XOOPS_ROOT_PATH."/themes/".$xoopsTheme['thename'];
if ( file_exists("$inc/themeevent.php") ) {
    include("$inc/themeevent.php");
} else {
    include("themeevent.php");
}

OpenTable();
$print = "<a href='event.php?op=print&amp;eid=$eid'><img src='".XOOPS_URL."/modules/news/images/print.gif' alt='"._PRINT."' border='0'></a>";
themeevent($data, $print);

$opt = $xoopsDB->prefix("eguide_opt");
$result = $xoopsDB->query("SELECT * FROM $opt WHERE eid=$eid");
if ($data['expire'] < time() || !$result) {
    # expired event
} elseif ($data = $xoopsDB->fetchArray($result)) {
    if ($data['reservation']) {
	$reserved = false;
	if ($xoopsUser) {
	    $rsv = $xoopsDB->prefix("eguide_reserv");
	    $result = $xoopsDB->query("SELECT * FROM $rsv WHERE eid=$eid AND uid=".$xoopsUser->uid());
	    $reserved = ($xoopsDB->getRowsNum($result)>0);
	}
	if ($reserved) {
	    echo "<div class='evnote'>"._MD_RESERVED."</div>\n";
	} elseif ($data['strict'] && $data['persons']<=$data['reserved']) {
	    echo "<div class='evnote'>"._MD_RESERV_FULL."</div>\n";
	} else {
	    eventform($data);
	}
	if ($data['persons']) {
	    echo "<p>".sprintf(_MD_RESERV_NUM, $data['persons'])." (".
		sprintf(_MD_RESERV_REG, $data['reserved']).")</p>\n";
	}
    }
}

CloseTable();

include(XOOPS_ROOT_PATH."/footer.php");
?>