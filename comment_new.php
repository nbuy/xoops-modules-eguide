<?php
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author       XOOPS Development Team,
 */

include __DIR__ . '/header.php';

$com_itemid = isset($_GET['com_itemid']) ? (int)$_GET['com_itemid'] : 0;
$eid        = $com_itemid;
$exid       = 0;
if (preg_match('/sub=(\d+)/', Request::getString('HTTP_REFERER', '', 'SERVER'), $d)) {
    $exid = $d[1];
}

$result = $xoopsDB->query('SELECT * FROM ' . EGTBL . ' e LEFT JOIN ' . OPTBL . ' o ON e.eid=o.eid LEFT JOIN ' . CATBL . ' ON topicid=catid LEFT JOIN ' . EXTBL . " ON e.eid=eidref AND exid=$exid WHERE e.eid=$eid AND status=" . STAT_NORMAL);

$data = $xoopsDB->fetchArray($result);
edit_eventdata($data);
$com_replytext = _POSTEDBY . '&nbsp;<b>' . $data['uname'] . '</b>&nbsp;' . _DATE . '&nbsp;<b>' . $data['postdate'] . '</b><br><br>' . $data['disp_summary'];

$com_replytext  .= '<br><br>' . $data['disp_body'] . '<br>';
$com_replytitle = $data['date'] . ': ' . $data['title'];

include XOOPS_ROOT_PATH . '/include/comment_new.php';
