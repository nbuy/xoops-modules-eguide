<?php
// ------------------------------------------------------------------------- //
//                XOOPS - PHP Content Management System                      //
//                       <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------- //
// Based on:								     //
// myPHPNUKE Web Portal System - http://myphpnuke.com/	  		     //
// PHP-NUKE Web Portal System - http://phpnuke.org/	  		     //
// Thatware - http://thatware.org/					     //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //
include("../../mainfile.php");
include("const.php");
global $eventConfig;

//error_reporting(E_ALL);
if (function_exists("getCache")) {
    eval(getCache($xoopsModule->dirname()."/config.php"));
} else {
    // compat XOOPS 1.3.x
    include(XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname()."/cache/config.php");
    // compat XOOPS 2.0.x
    $inc = XOOPS_ROOT_PATH."/include/old_theme_functions.php";
    if (file_exists($inc)) {
	include_once($inc);
	$inc = XOOPS_ROOT_PATH."/include/old_functions.php";
	if (file_exists($inc)) include_once($inc);
    }
}

// remove slashes
if (XOOPS_USE_MULTIBYTES && function_exists("mb_convert_encoding") &&
    $xoopsConfig['language'] == 'japanese') {
    if (get_magic_quotes_gpc()) {
	function post_filter($s) {
	    return mb_convert_encoding(stripslashes($s), _CHARSET, "EUC-JP,UTF-8,Shift_JIS,JIS");
	}
    } else {
	function post_filter($s) {
	    return mb_convert_encoding($s, _CHARSET, "EUC-JP,UTF-8,Shift_JIS,JIS");
	}
    }
} else {
    if (get_magic_quotes_gpc()) {
	function post_filter($s) {
	    return stripslashes($s);
	}
    } else {
	function post_filter($s) {
	    return $s;
	}
    }
}
?>