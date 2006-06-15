<?php
// $Id: ev_top.php,v 1.16 2006/06/15 04:56:11 nobu Exp $

include_once(XOOPS_ROOT_PATH."/class/xoopsmodule.php");

function b_event_top_show($options) {
    global $xoopsDB, $xoopsUser;
    $myts =& MyTextSanitizer::getInstance();
    $dirname = basename(dirname(dirname(__FILE__)));
    $modurl = XOOPS_URL."/modules/$dirname";

    $now = time();
    if ($options[3]) {
        $sql = "SELECT eid, title, edate, cdate, uid FROM ".$xoopsDB->prefix("eguide")." WHERE edate>$now AND status=0 ORDER BY cdate DESC";
    } else {
	$sql = "SELECT eid, title, IF(exdate,exdate,edate) edate, cdate, uid, exid FROM ".$xoopsDB->prefix("eguide").' LEFT JOIN '.$xoopsDB->prefix("eguide_extent")." ON eid=eidref WHERE ((expire>=edate AND expire>$now) OR (expire<edate AND IF(exdate,exdate,edate)+expire>$now)) AND status=0 ORDER BY edate";
    }
    if(!isset($options[1])) $options[1]=10;
    $result = $xoopsDB->query($sql, $options[1], 0);

    $block = array('lang_nodata'=>_BLOCK_EV_NONE,
		   'lang_waiting'=>_BLOCK_EV_WAIT,
		   'lang_more'=>_BLOCK_EV_MORE,
		   'detail'=>$options[0],
		   'dirname'=>$dirname,
		   'module_url'=>$modurl,
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
	$edate = empty($myrow['exdate'])?$myrow['edate']:$myrow['exdate'];
	$event['title'] = $title;
	$event['eid'] = $myrow['eid'];
	if (isset($myrow['exid'])) $event['exid'] = $myrow['exid'];
	$event['date'] = formatTimestamp($edate, _BLOCK_DATE_FMT);
	$event['_date'] = formatTimestamp($edate, 's');
	$event['uname'] = XoopsUser::getUnameFromId($myrow['uid']);
	$event['post'] = formatTimestamp($myrow['cdate'], _BLOCK_DATE_FMT);
	$event['_post'] = formatTimestamp($myrow['cdate'], 'm');
	$event['uid'] = $myrow['uid'];
	$block['events'][] = $event;
    }
    $mod = XoopsModule::getByDirname($dirname);
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