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
	include_once(XOOPS_ROOT_PATH."/include/old_functions.php");
    }
}
?>
