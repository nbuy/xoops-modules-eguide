<?php
// $Id: ev_top.php,v 1.8 2005/10/21 05:47:06 nobu Exp $

function b_event_top_show($options) {
    include_once(XOOPS_ROOT_PATH."/class/xoopsmodule.php");
    global $xoopsDB, $xoopsUser;
    $moddir = 'eguide';
    $modurl = XOOPS_URL."/modules/$moddir";

    $content = "";
    $sql = "SELECT eid, title, edate, cdate, uid FROM ".$xoopsDB->prefix("eguide")." WHERE expire>".time()." AND status=0 ORDER BY edate";
    if(!isset($options[1])) $options[1]=10;
    $result = $xoopsDB->query($sql, $options[1], 0);
    if ($xoopsDB->getRowsNum($result)==0) {
	$content .= "<div class='evline'>"._BLOCK_EV_NONE."</div>\n";
    }
    while ( $myrow = $xoopsDB->fetchArray($result) ) {
	$title = htmlspecialchars($myrow["title"]);
	if ( !XOOPS_USE_MULTIBYTES ) {
	    if (strlen($title) >= 19) {
		$title = substr($title,0,18)."...";
	    }
	}

	$date = "<strong>".formatTimestamp($myrow['edate'], _BLOCK_DATE_FMT)."</strong>&nbsp;";
	if ($options[0]) {
	    $poster = new XoopsUser($myrow['uid']);
	    $add = "["._BLOCK_EV_POST." ".formatTimestamp($myrow['cdate'], _BLOCK_DATE_FMT)." ".$poster->uname()."]";
	} else {
	    $add = "";
	}
	$eid = $myrow['eid'];
	$content .= "<div class='evline'>$date<a href='$modurl/event.php?eid=$eid'>$title</a> $add</div>\n";
    }
    $mod = XoopsModule::getByDirname($moddir);
    if ($xoopsUser && $xoopsUser->isAdmin($mod->mid())) {
	$result = $xoopsDB->query("SELECT count(eid) FROM ".$xoopsDB->prefix("eguide")." WHERE status=1");
	if ($xoopsDB->getRowsNum($result)) {
	    $n = array_shift($xoopsDB->fetchArray($result));
	    if ($n) $content .= "<p><a href='$modurl/admin/index.php?op=events'>"._BLOCK_EV_WAIT."</a>: $n</p>";
	}
    }
    $content .= "<div class='evmore' style='text-align: right'><a href='$modurl/'>"._BLOCK_EV_MORE."</a></div>\n";
    return array("content"=>$content, "title"=>_MI_EGUIDE_HEADLINE);
}

function b_event_top_edit($options) {
    if ($options[0]) {
	$sel0=" checked";
	$sel1="";
    } else {
	$sel0="";
	$sel1=" checked";
    }
    if (!isset($options[1])) $options[1]=10;
    return _BLOCK_EV_STYLE."&nbsp;".
	"<input type='radio' name='options[]' value='1'$sel0 />"._YES." &nbsp; \n".
	"<input type='radio' name='options[]' value='0'$sel1 />"._NO."<br/>\n".
	_BLOCK_EV_ITEMS."&nbsp;<input name='options[1]' value='".$options[1].
	"' />\n";
}
?>