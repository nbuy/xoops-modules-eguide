<?php
// $Id: menu.php,v 1.7 2007/12/31 06:43:53 nobu Exp $

$adminmenu[]=array('title' => _MI_EGUIDE_EVENTS,
		    'link' => "admin/index.php?op=events");
$adminmenu[]=array('title' => _MI_EGUIDE_NOTIFIES,
		   'link'  => "admin/index.php?op=notifies");
$adminmenu[]=array('title' => _MI_EGUIDE_CATEGORY,
		   'link'  => "admin/index.php?op=category");
$adminmenu[]=array('title' => _MI_EGUIDE_SUMMARY,
		   'link'  => "admin/index.php?op=summary");
$adminmenu[]=array('title' => _MI_EGUIDE_SUBMIT,
		   'link'  => "admin.php");
$adminmenu[]=array('title' => _MI_EGUIDE_ABOUT,
		   'link'  => "admin/help.php");
$path = dirname(dirname(__FILE__)).'/options/menu.php';

if (file_exists($path)) include $path;

$adminmenu4altsys[]=
    array('title' => _MD_A_MYMENU_MYTPLSADMIN,
	  'link' => 'admin/index.php?mode=admin&lib=altsys&page=mytplsadmin');
$adminmenu4altsys[]=
    array('title' => _MD_A_MYMENU_MYBLOCKSADMIN,
	  'link' => 'admin/index.php?mode=admin&lib=altsys&page=myblocksadmin');
$adminmenu4altsys[]=
    array('title' => _MD_A_MYMENU_MYPREFERENCES,
	  'link' => 'admin/index.php?mode=admin&lib=altsys&page=mypreferences');
?>
