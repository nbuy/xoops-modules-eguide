<?php
// $Id: ev_top.php,v 1.27 2009/06/01 05:58:06 nobu Exp $

include dirname(dirname(__FILE__))."/mydirname.php";

eval( '
function b_'.$myprefix.'_top_show( $options )
{
	return b_event_top_show_base( "'.$egdirname.'" , "'.$myprefix.'" ,$options ) ;
}
' ) ;

if (!function_exists("eguide_marker")) {
    include_once(XOOPS_ROOT_PATH."/class/xoopsmodule.php");

    function eguide_marker($full, $dirname) {
	global $marker;
	if (empty($marker)) {
	    $module_handler =& xoops_gethandler('module');
	    $module =& $module_handler->getByDirname($dirname);
	    $config_handler =& xoops_gethandler('config');
	    $config =& $config_handler->getConfigsByCat(0, $module->getVar('mid'));
	    $marker = preg_split('/,|[\r\n]+/',$config['maker_set']);
	}
	$tmp = $marker;
	while($tmp) {
	    list($k,$v) = array_splice($tmp, 0, 2);
	    if ($full<$k) return $v;
	}
	return '';
    }
}

if (!function_exists("b_event_top_show_base")) {

function b_event_top_show_base($dirname, $prefix, $options) {
    global $xoopsDB, $xoopsUser;
    $myts =& MyTextSanitizer::getInstance();
	
    $now = time();
    list($detail, $nitem, $nlen, $only, $cat) = $options;
    $cond = "";
    $ids = array();
    if ($cat) {
	$labs = array();
	foreach (explode(',',$cat) as $val) {
	    $nval = intval($val);
	    if ($nval) {
		$ids[] = $nval;
	    } else {
		$labs[] = $xoopsDB->quoteString($val);
	    }
	}
	if ($ids) {
	    $cond = "catid IN (".join(',',$ids).")";
	}
	if ($labs) {
	    if ($cond) $cond .= " OR ";
	    $cond .= "catname IN (".join(',',$labs).")";
	}
	if ($cond) {
	    $res = $xoopsDB->query("SELECT catid,catname,catimg FROM ".$xoopsDB->prefix($prefix."_category")." WHERE ".$cond);
	    $ids = array();
	    while (list($id,$name,$img) = $xoopsDB->fetchRow($res)) {
		$ids[$id] = array('name'=>htmlspecialchars($name),'img'=>$img);
	    }
	    $cond = $ids?" AND topicid IN (".join(',',array_keys($ids)).")":"";
	}
    }
    if ($only) {
	$sql = "SELECT eid, title, summary, style, MIN(IF(exdate,exdate,edate)) edate, cdate, uid FROM ".$xoopsDB->prefix($prefix)." LEFT JOIN ".$xoopsDB->prefix($prefix."_extent")." ON eid=eidref AND exdate>$now WHERE (edate>$now OR exdate) $cond AND status=0 GROUP BY eid ORDER BY cdate DESC";
    } else {
	$sql = "SELECT e.eid, title, summary, style, IF(exdate,exdate,edate) edate, cdate, uid,
exid, IF(x.reserved,x.reserved,o.reserved)/persons*100 as full, closetime
FROM ".$xoopsDB->prefix($prefix).' e
  LEFT JOIN '.$xoopsDB->prefix($prefix."_opt").' o ON e.eid=o.eid
  LEFT JOIN '.$xoopsDB->prefix($prefix."_extent")." x ON e.eid=eidref
WHERE ((expire>=edate AND expire>$now)
       OR (expire<edate AND IF(exdate,exdate,edate)+expire>$now)) $cond
  AND status=0 ORDER BY edate";
    }
    $result = $xoopsDB->query($sql, $nitem, 0);

    $block = array('detail'=>$detail,
		   'dirname'=>$dirname,
		   'module_url'=>XOOPS_URL."/modules/$dirname",
		   'categories'=>$ids,
		   'events'=>array());
    while ( $myrow = $xoopsDB->fetchArray($result) ) {
	$event = array();
	$title = $myts->makeTboxData4Show($myrow["title"]);
	if ( XOOPS_USE_MULTIBYTES ) {
	    if (function_exists('mb_strcut')&& strlen($title) >= $nlen) {
		$title = $myts->makeTboxData4Show(mb_strcut($myrow['title'],0, $nlen-1, _CHARSET))."...";
	    }
	} else {
	    if (strlen($title) >= $nlen) {
		$title = $myts->makeTboxData4Show(substr($myrow['title'],0,$nlen-1))."...";
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
	$html = 1; $br=0;
	switch ($myrow['style']) {
	case 2: $html = 0;
	case 1: $br = 1;
	}
	$event['description'] = $myts->displayTarea($myrow['summary'],$html,0,1,1,$br);
	if (isset($myrow['full'])) {
	    $event['mark'] = eguide_marker($myrow['full'], $dirname);
	}
	$block['events'][] = $event;
    }

    $module_handler =& xoops_gethandler('module');
    $module =& $module_handler->getByDirname($dirname);
    if ($xoopsUser && $xoopsUser->isAdmin($module->getVar('mid'))) {
	$result = $xoopsDB->query("SELECT count(eid) FROM ".$xoopsDB->prefix($prefix)." WHERE status=1");
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
	"<input type='hidden' name='options[]' value='".$options[3]."' /><br/>\n".
	_BLOCK_EV_CATEGORY."&nbsp;<input name='options[]' value='".$options[4]."' />\n".
	"<input type='hidden' name='options[]' value='".$options[4]."' />\n";
    
}

}
?>