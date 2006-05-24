<?php
// $Id: menu.php,v 1.6 2006/05/24 04:48:58 nobu Exp $

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
?>