<?php
// $Id: ev_top.php,v 1.17 2006/08/24 07:49:52 nobu Exp $

include_once(XOOPS_ROOT_PATH."/class/xoopsmodule.php");

if (!function_exists("eguide_marker")) {
    function eguide_marker($full) {
	global $marker;
	if (empty($marker)) {
	    $module_handler =& xoops_gethandler('module');
	    $module =& $module_handler->getByDirname('eguide');
	    $config_handler =& xoops_gethandler('config');
	    $config =& $config_handler->getConfigsByCat(0, $module->getVar('mid'));
	    $marker = preg_split('/,|[\r\n]+/',$config['maker_set']);
	}
	$tmp = $marker;
	while(list($k,$v) = array_splice($tmp, 0, 2)) {
	    if ($full<$k) return $v;
	}
	return '';
    }
 }

function b_event_top_show($options) {
    global $xoopsDB, $xoopsUser;
    $myts =& MyTextSanitizer::getInstance();
    $dirname = basename(dirname(dirname(__FILE__)));
    $modurl = XOOPS_URL."/modules/$dirname";

    $now = time();
    if ($options[3]) {
	$sql = "SELECT eid, title, MIN(IF(exdate,exdate,edate)) edate, cdate, uid FROM ".$xoopsDB->prefix("eguide")." LEFT JOIN ".$xoopsDB->prefix("eguide_extent")." ON eid=eidref AND exdate>$now WHERE (edate>$now OR exdate) AND status=0 GROUP BY eid ORDER BY cdate DESC";
    } else {
	$sql = "SELECT e.eid, title, IF(exdate,exdate,edate) edate, cdate, uid,
exid, IF(x.reserved,x.reserved,o.reserved)/persons*100 as full, closetime
FROM ".$xoopsDB->prefix("eguide").' e
  LEFT JOIN '.$xoopsDB->prefix("eguide_opt").' o ON e.eid=o.eid
  LEFT JOIN '.$xoopsDB->prefix("eguide_extent")." x ON e.eid=eidref
WHERE ((expire>=edate AND expire>$now)
       OR (expire<edate AND IF(exdate,exdate,edate)+expire>$now))
  AND status=0 ORDER BY edate";
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
	if (isset($myrow['full'])) {
	    $event['mark'] = eguide_marker($myrow['full']);
	}
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