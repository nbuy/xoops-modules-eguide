<?php
include '../../../include/cp_header.php';
include '../const.php';
include '../functions.php';

$modbase = XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname();
$loc="$modbase/language/".$xoopsConfig['language'];
foreach(array('main.php', 'modinfo.php') as $file) {
    if ( file_exists("$loc/$file") ) include_once("$loc/$file");
    else include_once("$modbase/language/english/$file");
}
?>