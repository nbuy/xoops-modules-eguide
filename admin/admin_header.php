<?php
include '../../../include/cp_header.php';

$modbase = dirname(dirname(__FILE__));
include "$modbase/const.php";
include "$modbase/functions.php";

$loc="$modbase/language/".$xoopsConfig['language'];
foreach (array("modinfo.php", "main.php") as $res) {
    if ( file_exists("$loc/$res") ) include_once("$loc/$res");
    else include_once("$modbase/language/english/$res");
}
?>