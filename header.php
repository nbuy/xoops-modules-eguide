<?php
//

include __DIR__ . '/../../mainfile.php';
if (!defined('_MD_ORDER_DATE')) {
    if (class_exists('XCube_Root')) {    // for XCL with altsys resources
        $root =& XCube_Root::getSingleton();

        $root->mLanguageManager->_loadLanguage($xoopsModule->getVar('dirname'), 'common');
    } else {
        $modres = (__DIR__ . '/language/');
        $lang   = $xoopsConfig['language'];
        if (file_exists("$modres/$lang/common.php")) {
            require_once "$modres/$lang/common.php";
        } else {
            require_once "$modres/english/common.php";
        }
    }
}
include __DIR__ . '/const.php';
include __DIR__ . '/functions.php';
