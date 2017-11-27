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

include __DIR__ . '/../mydirname.php';

eval('
function ' . $myprefix . '_notify_iteminfo( $category, $item_id )
{
    return eguide_notify_iteminfo_base( "' . $egdirname . '" , "' . $myprefix . '" , $category, $item_id ) ;
}
');

if (!function_exists('eguide_notify_iteminfo_base')) {
    require_once __DIR__ . '/../const.php';

    /**
     * @param $dirname
     * @param $prefix
     * @param $category
     * @param $item_id
     * @return array
     */
    function eguide_notify_iteminfo_base($dirname, $prefix, $category, $item_id)
    {
        global $xoopsDB;

        $item = ['name' => ''];
        if ('event' === $category && 0 != $item_id) {
            // Assume we have a valid story id
            $sql    = 'SELECT title FROM ' . EGTBL . ' WHERE status=0 AND eid=' . $item_id;
            $result = $xoopsDB->query($sql); // TODO: error check

            list($item['name']) = $xoopsDB->fetchRow($result);
            $item['url'] = XOOPS_URL . "/modules/$dirname/event.php?eid=" . $item_id;
        }

        return $item;
    }
}
