<?php
// Event Guide Module for XOOPS
// $Id: index.php,v 1.8 2005/11/18 17:08:02 nobu Exp $

include 'header.php';

$prev = isset($_GET['prev'])?intval($_GET['prev']):0;
$page = isset($_GET['page'])?intval($_GET['page']):0;

set_next_event();

$now = time();
$max = $xoopsModuleConfig['max_event'];
$cond = 'status='.STAT_NORMAL;

if (empty($prev)) {
    $cond .= " AND expire>$now"; // show upcomming event
    $start = (empty($page)?0:$page-1)*$max;
    $ord = 'ASC';
} else {
    $cond .= " AND expire<$now"; // show passed event
    $start = ($prev-1)*$max;
    $ord = 'DESC';
}

$opt = isset($_GET['cat'])?' AND topicid='.intval($_GET['cat']):'';

$fields = "e.eid, cdate, ldate, title, summary, reservation, uid, status, style, counter, catid, catname, catimg ";
$result = $xoopsDB->query('SELECT '.$fields.' FROM '.EGTBL.' e LEFT JOIN '.OPTBL.' o ON e.eid=o.eid LEFT JOIN '.CATBL.' ON topicid=catid WHERE '.$cond.$opt.' ORDER BY ldate '.$ord, $max, $start);

$events = array();
$isadmin = false;
$uid = 0;
if (is_object($xoopsUser)) {
    $isadmin = $xoopsUser->isAdmin($xoopsModule->getVar('mid'));
    $uid = $xoopsUser->getVar('uid');
}
while ($event = $xoopsDB->fetchArray($result)) {
    $event['isadmin'] = ($isadmin || $event['uid']==$uid);
    edit_eventdata($event);
    $events[] = $event;
}

include XOOPS_ROOT_PATH.'/header.php';

$xoopsOption['template_main'] = 'eguide_index.html';

$xoopsTpl->assign('events', $events);
$xoopsTpl->assign(assign_const());

if (empty($prev)) {
    $result = $xoopsDB->query('SELECT eid FROM '.EGTBL.' WHERE expire<'.$now.' AND status='.STAT_NORMAL.$opt, 1);
    $p = $xoopsDB->getRowsNum($result);
    $start += $max;
    $result = $xoopsDB->query('SELECT eid FROM '.EGTBL.' WHERE expire>'.$now.' AND status='.STAT_NORMAL.$opt, 1, $start);
    $q = $xoopsDB->getRowsNum($result);	// there is next page
    if (empty($page) || $page==1) {
	$prev="?prev=1";
	$page="?page=2";
    } else {
	$prev="?page=".($page-1);
	$page="?page=".($page+1);
    }
} else {
    $result = $xoopsDB->query('SELECT eid FROM '.EGTBL." WHERE expire<$now AND status=".STAT_NORMAL.$opt, $max, $start+$max);
    $p = $xoopsDB->getRowsNum($result);	// there is more prev page?
    $q = true;			// always next page exists.
    if ($prev==1) {
	$prev="?prev=".($prev+1);
	$page="?page=1";
    } else {
	$prev="?prev=".($prev-1);
	$page="?prev=".($prev+1);
    }
}
if ($opt) $opt = "&amp;cat=".intval($_GET['cat']);

if ($p) $xoopsTpl->assign('page_prev', $prev.$opt);
if ($q) $xoopsTpl->assign('page_next', $page.$opt);

include XOOPS_ROOT_PATH.'/footer.php';
?>