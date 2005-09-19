<?php
include("header.php");
$inc = XOOPS_ROOT_PATH."/modules/image/class.php";
if (file_exists($inc)) include_once($inc);

foreach (array("eid", "op") as $v) {
    if (isset($_GET[$v])) $$v = $_GET[$v];
}
$myts =& MyTextSanitizer::getInstance();

$tbl = $xoopsDB->prefix("eguide");
$rsv = $xoopsDB->prefix("eguide_reserv");

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

if (!empty($data['reserved'])) {
    $show = array();
    $item = array();
    foreach (explode("\n", $data['optfield']) as $n) {
	$a = explode(",", preg_replace('/[\n\r]/',"", $n));
	$lab = preg_replace('/[\*#]$/', "",array_shift($a));
	if (preg_match('/^!/', $lab)) {
	    $lab = preg_replace('/^!\s*/', '', $lab);
	    $show[] = $lab;
	}
	$item[] = $lab;
    }
    include("functions.php");
    if (count($show)) {
	echo "<br/>";
	OpenTable();
	echo "<h3>"._MD_RESERV_LIST."</h3>\n";
	echo "<table width='100%' cellspacing='1' cellpadding='4' class='bg2' border='0'>\n";
	$x = "<tr class='bg3'><th></th>";
	foreach ($show as $v) {
	    $x .= "<th>$v</th>";
	}
	echo $x."</tr>\n";;
	$result = $xoopsDB->query("SELECT * FROM $rsv WHERE eid=$eid AND status="._AM_RVSTAT_RESERVED." ORDER BY rdate");
	$tags = preg_match("/^XOOPS 1\\./",XOOPS_VERSION)?array("bg1","bg3"):array("even","odd");
	$nc = 0;
	while($rdata = $xoopsDB->fetchArray($result)) {
	    $bg = $tags[($nc++ % 2)];
	    $a = explodeinfo($rdata['info'], $item);
	    if (!empty($rdata['uid'])) {
		$uid = $rdata['uid'];
		$uinfo = " (<a href='".XOOPS_URL."/userinfo.php?uid=$uid'>".XoopsUser::getUnameFromId($uid)."</a>)";
	    } else {
		$uinfo = "";
	    }
	    $x = "";
	    foreach ($show as $v) {
		$x .= "<td>".$myts->sanitizeForDisplay($a[$v])."$uinfo</td>";
		$uinfo = "";
	    }
	    echo "<tr class='$bg'><td align='right'>$nc</td>".$x."</tr>\n";
	}
	echo "</table>\n";
	CloseTable();
    }
}

include(XOOPS_ROOT_PATH."/footer.php");
?>