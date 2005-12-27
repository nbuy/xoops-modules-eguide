<?php
// Event Guide - Personal reservation event list
// $Id: mylist.php,v 1.1 2005/12/27 08:29:43 nobu Exp $

include 'header.php';

if (!is_object($xoopsUser)) {
    redirect_header(XOOPS_URL.'/user.php', 1, _NOPERM);
}
$uid = $xoopsUser->getVar('uid');


include XOOPS_ROOT_PATH.'/header.php';
echo "<h2>"._MD_MYLIST."</h2>\n";
$res = $xoopsDB->query('SELECT r.eid,r.exid, r.rdate, e.title, IF(exdate,exdate,edate) edate FROM '.RVTBL.' r,'.EGTBL.' e LEFT JOIN '.EXTBL." x ON r.eid=eidref AND r.exid=x.exid WHERE r.uid=$uid AND r.eid=e.eid ORDER BY rvid");
echo $xoopsDB->error();
echo "<table class='outer'>\n";
echo "<tr><th>"._MD_ORDER_DATE."</th><th>"._MD_TITLE."</th></tr>\n";
$n = 0;
while ($data = $xoopsDB->fetchArray($res)) {
    $bg = ($n++%2)?'even':'odd';
    $url = 'event.php?eid='.$data['eid'];
    if ($data['exid']) $url .= '&sub='.$data['exid'];
    $title = "<a href='$url'>".eventdate($data['edate']).": ".$data['title']."</a>";
    echo "<tr class='$bg'><td>".formatTimestamp($data['rdate'], _MD_TIME_FMT).
	"</td><td>$title</td></tr>\n";
}
echo "</table>";
include XOOPS_ROOT_PATH.'/footer.php';
?>