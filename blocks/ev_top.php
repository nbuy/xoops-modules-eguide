<?php
// $Id: ev_top.php,v 1.11 2005/11/19 18:32:35 nobu Exp $

include_once(XOOPS_ROOT_PATH."/class/xoopsmodule.php");

function b_event_top_show($options) {
    global $xoopsDB, $xoopsUser;
    $myts =& MyTextSanitizer::getInstance();
    $moddir = 'eguide';
    $modurl = XOOPS_URL."/modules/$moddir";

    $content = "";
    $order = $options[3]?'cdate DESC':'ldate';
    $sql = "SELECT eid, title, ldate, cdate, uid FROM ".$xoopsDB->prefix("eguide")." WHERE expire>".time().' AND status=0 ORDER BY '.$order;
    if(!isset($options[1])) $options[1]=10;
    $result = $xoopsDB->query($sql, $options[1], 0);

    $block = array('lang_nodata'=>_BLOCK_EV_NONE,
		   'lang_waiting'=>_BLOCK_EV_WAIT,
		   'lang_more'=>_BLOCK_EV_MORE,
		   'detail'=>$options[0],
		   'events'=>array());
    while ( $myrow = $xoopsDB->fetchArray($result) ) {
	$event = array();
	$title = $myts->makeTboxData4Show($myrow["title"]);
	if ( XOOPS_USE_MULTIBYTES ) {
	    if (function_exists('mb_strcut')&& strlen($title) >= $options[2]) {
		$title = $myts->makeTboxData4Show(mb_strcut($myrow['title'],0,($options[2] -1), _CHARSET))."...";
	    }
	} else {
	    if (strlen($title) >= $options[2]) {
		$title = $myts->makeTboxData4Show(substr($myrow['title'],0,($options[2] -1)))."...";
	    }
	}
	$event['title'] = $title;
	$event['eid'] = $myrow['eid'];
	$event['date'] = formatTimestamp($myrow['ldate'], _BLOCK_DATE_FMT);
	$event['_date'] = formatTimestamp($myrow['ldate'], 's');
	$event['uname'] = XoopsUser::getUnameFromId($myrow['uid']);
	$event['post'] = formatTimestamp($myrow['cdate'], _BLOCK_DATE_FMT);
	$event['_post'] = formatTimestamp($myrow['cdate'], 'm');
	$event['uid'] = $myrow['uid'];
	$block['events'][] = $event;
    }
    $mod = XoopsModule::getByDirname($moddir);
    if ($xoopsUser && $xoopsUser->isAdmin($mod->mid())) {
	$result = $xoopsDB->query("SELECT count(eid) FROM ".$xoopsDB->prefix("eguide")." WHERE status=1");
	if ($xoopsDB->getRowsNum($result)) {
	    list($block['waiting']) = $xoopsDB->fetchRow($result);
	}
    }
    return $block;
}

function b_event_top_edit($options) {
    if ($options[0]) {
	$sel0=" checked";
	$sel1="";
    } else {
	$sel0="";
	$sel1=" checked";
    }
    return _BLOCK_EV_STYLE."&nbsp;".
	"<input type='radio' name='options[]' value='1'$sel0 />"._YES." &nbsp; \n".
	"<input type='radio' name='options[]' value='0'$sel1 />"._NO."<br/>\n".
	_BLOCK_EV_ITEMS."&nbsp;<input name='options[]' value='".$options[1].
	"' /><br/>\n".
	_BLOCK_EV_TRIM."&nbsp;<input name='options[]' value='".$options[2]."' />\n".
	"<input type='hidden' name='options[]' value='".$options[3]."' />\n";
    
}
?>