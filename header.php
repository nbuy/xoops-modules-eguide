<?php
// $Id: header.php,v 1.9 2010/07/18 06:51:54 nobu Exp $

include '../../mainfile.php';
$lang = $xoopsConfig['language'];
$modbase = (dirname(__FILE__)."/language/");
if (file_exists("$modbase/$lang/common.php")) include_once "$modbase/$lang/common.php";
else include_once "$modbase/english/common.php";
include 'const.php';
include 'functions.php';
?>