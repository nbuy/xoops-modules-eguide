<?php
# Event category select block
#

include __DIR__ . '/../mydirname.php';

eval('
function b_' . $myprefix . '_select_show( $options )
{
    return b_event_select_base( "' . $egdirname . '" , "' . $myprefix . '" ,$options ) ;
}
');

if (!function_exists('b_event_select_base')) {
    /**
     * @param $dirname
     * @param $prefix
     * @param $options
     * @return array
     */
    function b_event_select_base($dirname, $prefix, $options)
    {
        global $xoopsDB, $xoopsUser;
        $cat    = $xoopsDB->prefix($prefix . '_category');
        $cats   = $options[0];
        $opt    = preg_match('/^(\d+,)*\d+$/', $cats) ? " WHERE p.catid IN ($cats)" : '';
        $result = $xoopsDB->query("SELECT c.catid, c.catname AS name, c.catimg AS image,if (p.weight, p.weight, c.weight) ord1, if(p.weight IS NULL, -1, c.weight) ord2,c.catpri, c.catdesc FROM $cat c LEFT JOIN $cat p ON c.catpri=p.catid $opt ORDER BY ord1,ord2,catid");
        $list   = [];
        $catid  = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
        while ($data = $xoopsDB->fetchArray($result)) {
            $id   = $data['catid'];
            $name = htmlspecialchars($data['name']);
            if (!empty($data['catpri']) && empty($cats)) {
                $name = '-- ' . $name;
            }
            $data['name'] = $name;
            $list[]       = $data;
        }

        return [
            'options' => $list,
            'dirname' => $dirname,
            'action'  => XOOPS_URL . "/modules/$dirname/index.php"
        ];
    }

    /**
     * @param $options
     * @return string
     */
    function b_event_select_edit($options)
    {
        return _BLOCK_EV_CATEGORY . "&nbsp;<input name='options[]' value='" . $options[0] . "'>\n";
    }
}
