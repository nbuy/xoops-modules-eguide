<?php
// Event Guide Module
//
use Xoopsmodules\eguide;

# for duplicatable (not D3, old style)
include __DIR__ . '/mydirname.php';

$moduleDirName = basename(__DIR__);

$exname = ('eguide' == $myprefix ? '' : "|$egdirname");
//$myicon = "images/{$myprefix}_slogo2.png";
//if (!file_exists("$mydirpath/$myicon")) {
//    $myicon = 'module_icon.php';
//}
$myicon = 'assets/images/logoModule.png';

$modversion['version']       = '2.74';
$modversion['module_status'] = 'Beta 1';
$modversion['release_date']  = '2017/11/05';
$modversion['name']          = _MI_EGUIDE_NAME . $exname;
$modversion['description']   = _MI_EGUIDE_DESC;
$modversion['credits']       = 'Nobuhiro Yasutomi';
$modversion['author']        = 'Nobuhiro Yasutomi';
//$modversion['help']          = 'help.html';
$modversion['help']          = 'page=help';
$modversion['license']       = 'GNU GPL 2.0 or later';
$modversion['license_url']   = 'www.gnu.org/licenses/gpl-2.0.html';
$modversion['official']      = 0;
$modversion['image']         = $myicon;
$modversion['dirname']       = $egdirname;
$modversion['modicons16'] = 'assets/images/icons/16';
$modversion['modicons32'] = 'assets/images/icons/32';
$modversion['min_php']   = '5.5';
$modversion['min_xoops'] = '2.5.9';
$modversion['min_admin'] = '1.2';
$modversion['min_db']    = ['mysql' => '5.5'];

// Sql file
//$modversion['sqlfile']['mysql'] = ('eguide' === $myprefix ? 'sql/mysql.sql' : "sql/mysql_{$myprefix}.sql");


// ------------------- Mysql ------------------- //
$modversion['sqlfile']['mysql'] = ($moduleDirName === $myprefix ? 'sql/mysql.sql' : "sql/mysql_{$myprefix}.sql");
// Tables created by sql file (without prefix!)
$modversion['tables'] = [
    $moduleDirName ,
    $moduleDirName . '_' . 'opt',
    $moduleDirName . '_' . 'reserv',
    $moduleDirName . '_' . 'category',
    $moduleDirName . '_' . 'extent'
];

$helper = eguide\Helper::getInstance();
$helper->loadLanguage('common');

// ------------------- Help files ------------------- //
$modversion['helpsection'] = [
    ['name' => CO_EGUIDE_OVERVIEW, 'link' => 'page=help'],
    ['name' => CO_EGUIDE_USING_STEPS, 'link' => 'page=help2'],
    ['name' => CO_EGUIDE_FUNCTIONS, 'link' => 'page=help3'],
    ['name' => CO_EGUIDE_CONFIGURATION, 'link' => 'page=help4'],
    ['name' => CO_EGUIDE_FIELD_FORMATS, 'link' => 'page=help5'],
    ['name' => CO_EGUIDE_RESERVATION_PLUGIN, 'link' => 'page=help6'],
    ['name' => CO_EGUIDE_CHANGES, 'link' => 'page=help7'],

    ['name' => CO_EGUIDE_DISCLAIMER, 'link' => 'page=disclaimer'],
    ['name' => CO_EGUIDE_LICENSE, 'link' => 'page=license'],
    ['name' => CO_EGUIDE_SUPPORT, 'link' => 'page=support'],

];

// OnUpdate - upgrade DATABASE
$modversion['onUpdate'] = 'onupdate.php';

// Admin things
$modversion['hasAdmin']    = 1;
$modversion['system_menu'] = 1;
$modversion['adminindex']  = 'admin/help.php';
$modversion['adminmenu']   = 'admin/menu.php';

// Templates
$modversion['templates'] = [
    ['file' => $myprefix . '_admin.tpl', 'description' => _MI_EGUIDE_ADMIN_TPL],
    ['file' => $myprefix . '_collect.tpl', 'description' => _MI_EGUIDE_COLLECT_TPL],
    ['file' => $myprefix . '_confirm.tpl', 'description' => _MI_EGUIDE_EVENT_CONF_TPL],
    ['file' => $myprefix . '_confirm.tpl', 'description' => _MI_EGUIDE_EVENT_CONFIRM_TPL],
    ['file' => $myprefix . '_editdate.tpl', 'description' => _MI_EGUIDE_EDITDATE_TPL],
    ['file' => $myprefix . '_entry.tpl', 'description' => _MI_EGUIDE_ENTRY_TPL],
    ['file' => $myprefix . '_event.tpl', 'description' => _MI_EGUIDE_EVENT_TPL],
    ['file' => $myprefix . '_event_print.tpl', 'description' => _MI_EGUIDE_EVENT_PRINT_TPL],
    ['file' => $myprefix . '_excel.xml', 'description' => _MI_EGUIDE_EXCEL_TPL],
    ['file' => $myprefix . '_index.tpl', 'description' => _MI_EGUIDE_INDEX_TPL],
    ['file' => $myprefix . '_item.tpl', 'description' => _MI_EGUIDE_EVENT_ITEM_TPL],
    ['file' => $myprefix . '_mylist.tpl', 'description' => _MI_EGUIDE_EVENT_LIST_TPL],
    ['file' => $myprefix . '_receipt.tpl', 'description' => _MI_EGUIDE_RECEIPT_TPL],
    ['file' => $myprefix . '_receipt_print.tpl', 'description' => _MI_EGUIDE_RECEIPT_PRINT_TPL],
    ['file' => $myprefix . '_userssel.tpl', 'description' => _MI_EGUIDE_USERSSEL_TPL],
];

// Blocks
$modversion['blocks'][1] = [
    'file'        => 'ev_top.php',
    'name'        => _MI_EGUIDE_HEADLINE . $exname,
    'description' => _MI_EGUIDE_HEADLINE_DESC,
    'show_func'   => "b_${myprefix}_top_show",
    'edit_func'   => 'b_event_top_edit',
    'options'     => '0|10|40|0|',
    'can_clone'   => true,
    'template'    => $myprefix . '_block_top.tpl'
];

$modversion['blocks'][] = [
    'file'        => 'ev_top.php',
    'name'        => _MI_EGUIDE_HEADLINE2 . $exname,
    'description' => _MI_EGUIDE_HEADLINE2_DESC,
    'show_func'   => "b_${myprefix}_top_show",
    'edit_func'   => 'b_event_top_edit',
    'options'     => '0|10|40|1|',
    'can_clone'   => true,
    'template'    => $myprefix . '_block_post.tpl'
];
$modversion['blocks'][] = [
    'file'        => 'ev_top.php',
    'name'        => _MI_EGUIDE_HEADLINE3 . $exname,
    'description' => _MI_EGUIDE_HEADLINE3_DESC,
    'show_func'   => "b_${myprefix}_top_show",
    'edit_func'   => 'b_event_top_edit',
    'options'     => '0|10|40|2|',
    'can_clone'   => true,
    'template'    => $myprefix . '_block_post.tpl'
];
$modversion['blocks'][] = [
    'file'        => 'ev_cat.php',
    'name'        => _MI_EGUIDE_CATBLOCK . $exname,
    'description' => _MI_EGUIDE_CATBLOCK_DESC,
    'show_func'   => "b_${myprefix}_select_show",
    'edit_func'   => 'b_event_select_edit',
    'options'     => '',
    'can_clone'   => true,
    'template'    => $myprefix . '_block_category.tpl'
];
// Menu
/** @var XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$module        = $moduleHandler->getByDirname($modversion['dirname']);

global $xoopsUser, $xoopsDB;
$modversion['hasMain'] = 1;
$configs               = null;
if (is_object($module) && $module->getVar('isactive')) {
    $configHandler = xoops_getHandler('config');
    $configs       = $configHandler->getConfigsByCat(0, $module->getVar('mid'));
    // category submenu
    $res = $xoopsDB->query('SELECT * FROM ' . $xoopsDB->prefix($myprefix . '_category') . ' WHERE catpri=0 ORDER BY weight,catid');
    if ($xoopsDB->getRowsNum($res) > 1) {
        while ($data = $xoopsDB->fetchArray($res)) {
            $modversion['sub'][] = [
                'name' => _MI_EGUIDE_CATEGORY_MARK . $data['catname'],
                'url'  => 'index.php?cat=' . $data['catid']
            ];
        }
    }
}
// register notify
if ($configs) {
    if (!empty($configs['user_notify'])) {
        $modversion['sub'][] = ['name' => _MI_EGUIDE_REG, 'url' => 'reserv.php?op=register'];
    }
}
// login users
if (is_object($xoopsUser)) {
    $modversion['sub'][] = ['name' => _MI_EGUIDE_MYLIST, 'url' => 'mylist.php'];
    // poster administration
    if (!empty($configs) && in_array($configs['group'], $xoopsUser->getGroups())) {
        $modversion['sub'][] = ['name' => _MI_EGUIDE_SUBMIT, 'url' => 'admin.php'];
        $modversion['sub'][] = ['name' => _MI_EGUIDE_COLLECT, 'url' => 'collect.php'];
    }
}

// Search
$modversion['hasSearch']      = 1;
$modversion['search']['file'] = 'include/search.inc.php';
$modversion['search']['func'] = $myprefix . '_search';

// Comments
$modversion['hasComments']          = 1;
$modversion['comments']['pageName'] = XOOPS_URL . '/modules/' . $modversion['dirname'] . '/event.php';
$modversion['comments']['itemName'] = 'eid';

// Config
$modversion['hasconfig'] = 1;
$modversion['config'][]  = [
    'name'        => 'group',
    'title'       => '_MI_EGUIDE_POSTGROUP',
    'description' => '_MI_EGUIDE_POSTGROUP_DESC',
    'formtype'    => 'group',
    'valuetype'   => 'int',
    'default'     => 2
];
$modversion['config'][]  = [
    'name'        => 'notify',
    'title'       => '_MI_EGUIDE_NOTIFYADMIN',
    'description' => '_MI_EGUIDE_NOTIFYADMIN_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'options'     => [_NO => 0, _YES => 1, _MI_EGUIDE_NOTIFY_ALWAYS => 2],
    'default'     => 1
];
$modversion['config'][]  = [
    'name'        => 'notify_group',
    'title'       => '_MI_EGUIDE_NOTIFYGROUP',
    'description' => '_MI_EGUIDE_NOTIFYGROUP_DESC',
    'formtype'    => 'group',
    'valuetype'   => 'int',
    'default'     => 1
];
$modversion['config'][]  = [
    'name'        => 'auth',
    'title'       => '_MI_EGUIDE_NEEDPOSTAUTH',
    'description' => '_MI_EGUIDE_NEEDPOSTAUTH_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0
];
$modversion['config'][]  = [
    'name'        => 'max_item',
    'title'       => '_MI_EGUIDE_MAX_LISTITEM',
    'description' => '_MI_EGUIDE_MAX_LISTITEM_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'int',
    'default'     => 3
];
$modversion['config'][]  = [
    'name'        => 'max_list',
    'title'       => '_MI_EGUIDE_MAX_LISTLINES',
    'description' => '_MI_EGUIDE_MAX_LISTLINES_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'int',
    'default'     => 50
];
$modversion['config'][]  = [
    'name'        => 'max_event',
    'title'       => '_MI_EGUIDE_MAX_EVENT',
    'description' => '_MI_EGUIDE_MAX_LISTITEM_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'int',
    'default'     => 10
];
$modversion['config'][]  = [
    'name'        => 'show_extents',
    'title'       => '_MI_EGUIDE_SHOW_EXTENTS',
    'description' => '_MI_EGUIDE_SHOW_EXTENTS_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1
];
$modversion['config'][]  = [
    'name'        => 'user_notify',
    'title'       => '_MI_EGUIDE_USER_NOTIFY',
    'description' => '_MI_EGUIDE_USER_NOTIFY_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1
];
$modversion['config'][]  = [
    'name'        => 'member_only',
    'title'       => '_MI_EGUIDE_MEMBER',
    'description' => '_MI_EGUIDE_MEMBER_DESC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'options'     => [_NO => 0, _YES => 1, _MI_EGUIDE_MEMBER_RELAX => 2],
    'default'     => 2
];
$modversion['config'][]  = [
    'name'        => 'has_confirm',
    'title'       => '_MI_EGUIDE_ORDERCONF',
    'description' => '_MI_EGUIDE_ORDERCONF_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1
];

# label_persons - eguide 2.5 re-define multiple variable setting accept.
$modversion['config'][] = [
    'name'        => 'label_persons',
    'title'       => '_MI_EGUIDE_LAB_PERSONS',
    'description' => '_MI_EGUIDE_LAB_PERSONS_DESC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => ''
];
$modversion['config'][] = [
    'name'        => 'close_before',
    'title'       => '_MI_EGUIDE_CLOSEBEFORE',
    'description' => '_MI_EGUIDE_CLOSEBEFORE_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'int',
    'default'     => 60
];
$modversion['config'][] = [
    'name'        => 'expire_after',
    'title'       => '_MI_EGUIDE_EXPIRE_AFTER',
    'description' => '_MI_EGUIDE_EXPIRE_AFTER_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'int',
    'default'     => 60 * 24
];
$modversion['config'][] = [
    'name'        => 'default_persons',
    'title'       => '_MI_EGUIDE_PERSONS',
    'description' => '_MI_EGUIDE_PERSONS_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'int',
    'default'     => 10
];
$modversion['config'][] = [
    'name'        => 'date_format',
    'title'       => '_MI_EGUIDE_DATE_FORMAT',
    'description' => '_MI_EGUIDE_DATE_FORMAT_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'text',
    'default'     => _MI_EGUIDE_DATE_FORMAT_DEF
];
$modversion['config'][] = [
    'name'        => 'use_plugins',
    'title'       => '_MI_EGUIDE_PLUGINS',
    'description' => '_MI_EGUIDE_PLUGINS_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0
];
$modversion['config'][] = [
    'name'        => 'maker_set',
    'title'       => '_MI_EGUIDE_MARKER',
    'description' => '_MI_EGUIDE_MARKER_DESC',
    'formtype'    => 'textarea',
    'valuetype'   => 'text',
    'default'     => _MI_EGUIDE_MARKER_DEF
];
$modversion['config'][] = [
    'name'        => 'time_defs',
    'title'       => '_MI_EGUIDE_TIME_DEFS',
    'description' => '_MI_EGUIDE_TIME_DEFS_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'text',
    'default'     => ''
];
$modversion['config'][] = [
    'name'        => 'export_field',
    'title'       => '_MI_EGUIDE_EXPORT_LIST',
    'description' => '_MI_EGUIDE_EXPORT_LIST_DESC',
    'formtype'    => 'text',
    'valuetype'   => 'text',
    'default'     => '*'
];
$modversion['config'][] = [
    'name'        => 'use_comment',
    'title'       => '_MI_EGUIDE_COMMENT',
    'description' => '_MI_EGUIDE_COMMENT_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1
];

// Notification

$modversion['hasNotification']             = 1;
$modversion['notification']['lookup_file'] = 'include/notification.inc.php';
$modversion['notification']['lookup_func'] = $myprefix . '_notify_iteminfo';

$modversion['notification']['category'][1] = [
    'name'           => 'global',
    'title'          => _MI_EGUIDE_GLOBAL_NOTIFY,
    'description'    => _MI_EGUIDE_GLOBAL_NOTIFY_DESC,
    /*    'item_name' => 'cat',*/
    'subscribe_from' => ['index.php', 'event.php']
];
$modversion['notification']['category'][2] = [
    'name'           => 'category',
    'title'          => _MI_EGUIDE_CATEGORY_NOTIFY,
    'description'    => _MI_EGUIDE_CATEGORY_NOTIFY_DESC,
    'item_name'      => 'cat',
    'subscribe_from' => ['index.php', 'event.php']
];
$modversion['notification']['category'][3] = [
    'name'           => 'event',
    'title'          => _MI_EGUIDE_CATEGORY_BOOKMARK,
    'description'    => _MI_EGUIDE_CATEGORY_BOOKMARK_DESC,
    'item_name'      => 'eid',
    'subscribe_from' => 'event.php',
    'allow_bookmark' => 1
];
$modversion['notification']['event'][1]    = [
    'name'          => 'new',
    'category'      => 'global',
    'title'         => _MI_EGUIDE_NEWPOST_NOTIFY,
    'caption'       => _MI_EGUIDE_NEWPOST_NOTIFY_CAP,
    'description'   => '',
    'mail_template' => 'notify',
    'mail_subject'  => _MI_EGUIDE_NEWPOST_SUBJECT
];
$modversion['notification']['event'][2]    = [
    'name'          => 'new',
    'category'      => 'category',
    'title'         => _MI_EGUIDE_NEWPOST_NOTIFY,
    'caption'       => _MI_EGUIDE_CNEWPOST_NOTIFY_CAP,
    'description'   => '',
    'mail_template' => 'notify',
    'mail_subject'  => _MI_EGUIDE_NEWPOST_SUBJECT
];
