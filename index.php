<?php
// Event Guide Module for XOOPS
// $Id: index.php,v 1.12 2005/12/27 05:13:53 nobu Exp $

include 'header.php';

$prev = param('prev');
$page = param('page');

set_next_event();

$now = time();
$max = $xoopsModuleConfig['max_event'];
$cond = 'status='.STAT_NORMAL;

if (empty($prev)) {
    $cond .= " AND expire>$now"; // show upcomming event
    $cond .= " AND (exdate>$now OR exdate IS NULL)";
    $start = (empty($page)?0:$page-1)*$max;
    $ord = 'ASC';
    $ext = $xoopsModuleConfig['show_extents']?'':'AND ldate=exdate';
} else {
    $cond .= " AND edate<$now"; // show passed event
    $cond .= " AND (exdate<$now OR exdate IS NULL)";
    $start = ($prev-1)*$max;
    $ord = 'DESC';
    $ext = $xoopsModuleConfig['show_extents']?'':'AND 0';
}

$opt = isset($_GET['cat'])?' AND topicid='.intval($_GET['cat']):'';

$fields = "e.eid, cdate, persons,title, summary, closetime,
IF(exdate,exdate,edate) edate, IF(x.reserved,x.reserved,o.reserved) reserved,
reservation, uid, status, style, counter, catid, catname, catimg, exid, exdate";
$result = $xoopsDB->query('SELECT '.$fields.' FROM '.EGTBL.' e LEFT JOIN '.
OPTBL.' o ON e.eid=o.eid LEFT JOIN '.CATBL.' ON topicid=catid LEFT JOIN '.
EXTBL." x ON e.eid=eidref $ext WHERE $cond$opt ORDER BY edate $ord", $max, $start);
if ($xoopsDB->errno() && $xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
    redirect_header('admin/upgrade_1.x.x.php', 5, _MD_NEED_UPGRADE);
    exit;
}

$events = array();
$isadmin = false;
$uid = 0;
if (is_object($xoopsUser)) {
    $isadmin = $xoopsUser->isAdmin($xoopsModule->getVar('mid'));
    $uid = $xoopsUser->getVar('uid');
}
while ($event = $xoopsDB->fetchArray($result)) {
    $event['isadmin'] = ($isadmin || $event['uid']==$uid);
    $event['link'] = true;
    $more = 'event.php?eid='.$event['eid'];
    if (!empty($event['exid'])) {
	$event['extent']=true;	// also show editdate link
	$more .= '&amp;sub='.$event['exid'];
    }
    $event['detail'] = $more;
    edit_eventdata($event);
    $events[] = $event;
}

include XOOPS_ROOT_PATH.'/header.php';
$xoopsOption['template_main'] = 'eguide_index.html';

$xoopsTpl->assign('events', $events);
$xoopsTpl->assign('xoops_module_header', HEADER_CSS);

if (empty($prev)) {
    if ($page < 2) {
	$result = $xoopsDB->query('SELECT eid FROM '.EGTBL.' e LEFT JOIN '.EXTBL." ON e.eid=eidref $ext WHERE (edate<$now OR exdate<$now) AND status=".STAT_NORMAL.$opt, 1);
	$p = $xoopsDB->getRowsNum($result);
    } else {
	$p = true;
    }
    $result = $xoopsDB->query('SELECT eid FROM '.EGTBL.' e LEFT JOIN '.EXTBL." ON e.eid=eidref $ext WHERE ".$cond.$opt, 1,$start+$max);
    $q = $xoopsDB->getRowsNum($result);	// there is next page
    if (empty($page) || $page==1) {
	$prev="?prev=1";
	$page="?page=2";
    } else {
	$prev="?page=".($page-1);
	$page="?page=".($page+1);
    }
} else {
    $result = $xoopsDB->query('SELECT eid FROM '.EGTBL.' e LEFT JOIN '.EXTBL." ON e.eid=eidref $ext WHERE ".$cond.$opt, 1,$start+$max);
    $p = $xoopsDB->getRowsNum($result);	// there is more prev page?
    $q = true;			// always next page exists.
    if ($prev==1) {
	$prev="?prev=".($prev+1);
	$page="?page=1";
    } else {
	$page="?prev=".($prev-1);
	$prev="?prev=".($prev+1);
    }
}

if ($opt) $opt = "&amp;cat=".intval($_GET['cat']);

if ($p) $xoopsTpl->assign('page_prev', $prev.$opt);
if ($q) $xoopsTpl->assign('page_next', $page.$opt);

include XOOPS_ROOT_PATH.'/footer.php';
?>