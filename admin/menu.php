<?php
//

use Xoopsmodules\eguide;

require_once __DIR__ . '/../class/Helper.php';
//require_once __DIR__ . '/../include/common.php';
$helper = eguide\Helper::getInstance();

$pathIcon32    = \Xmf\Module\Admin::menuIconPath('');
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');

$adminmenu[] = [
    'title' => _MI_EGUIDE_HOME,
    'link'  => 'admin/index.php',
    'icon'  => $pathIcon32 . '/home.png'
];

$adminmenu[] = [
    'title' => _MI_EGUIDE_EVENTS,
    'link'  => 'admin/main.php?op=events',
    'icon'  => $pathIcon32 . '/event.png'
];
$adminmenu[] = [
    'title' => _MI_EGUIDE_NOTIFIES,
    'link'  => 'admin/main.php?op=notifies',
    'icon'  => $pathIcon32 . '/mail_foward.png'
];
$adminmenu[] = [
    'title' => _MI_EGUIDE_CATEGORY,
    'link'  => 'admin/main.php?op=category',
    'icon'  => $pathIcon32 . '/category.png'
];
$adminmenu[] = [
    'title' => _MI_EGUIDE_SUMMARY,
    'link'  => 'admin/main.php?op=summary',
    'icon'  => $pathIcon32 . '/stats.png'
];

$adminmenu[] = [
    'title' => _MI_EGUIDE_ABOUT,
    'link'  => 'admin/help.php',
    'icon'  => $pathIcon32 . '/about.png'
];

$adminmenu[] = [
    'title' => _MI_EGUIDE_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . '/about.png'
];


$path = __DIR__ . '/../options/menu.php';

if (file_exists($path)) {
    include $path;
}

$adminmenu4altsys[] = [
    'title' => _MD_A_MYMENU_MYTPLSADMIN,
    'link'  => 'admin/index.php?mode=admin&lib=altsys&page=mytplsadmin'
];
$adminmenu4altsys[] = [
    'title' => _MD_A_MYMENU_MYBLOCKSADMIN,
    'link'  => 'admin/index.php?mode=admin&lib=altsys&page=myblocksadmin'
];
$adminmenu4altsys[] = [
    'title' => _MD_A_MYMENU_MYLANGADMIN,
    'link'  => 'admin/index.php?mode=admin&lib=altsys&page=mylangadmin'
];
$adminmenu4altsys[] = [
    'title' => _MD_A_MYMENU_MYPREFERENCES,
    'link'  => 'admin/index.php?mode=admin&lib=altsys&page=mypreferences'
];
