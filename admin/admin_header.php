<?php
include '../../../include/cp_header.php';

$modbase = dirname(dirname(__FILE__));
include "$modbase/const.php";
include "$modbase/functions.php";

$loc="$modbase/language/".$xoopsConfig['language'];
if ( file_exists("$loc/modinfo.php") ) include_once("$loc/modinfo.php");
else include_once("$modbase/language/english/modinfo.php");
?>