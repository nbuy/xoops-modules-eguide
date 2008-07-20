<?php
# $Id: const.php,v 1.11 2008/07/20 12:45:31 nobu Exp $

define("_RVSTAT_ORDER",0);
define("_RVSTAT_RESERVED",1);
define("_RVSTAT_REFUSED",2);

define("STAT_NORMAL",0);
define("STAT_POST",1);
define("STAT_DELETED",4);

include_once(dirname(__FILE__).'/mydirname.php');

global $myprefix, $egdirname, $xoopsDB;
define('EGPREFIX', $myprefix);
define('EGTBL', $xoopsDB->prefix($myprefix));
define('CATBL', $xoopsDB->prefix($myprefix."_category"));
define('OPTBL', $xoopsDB->prefix($myprefix."_opt"));
define('EXTBL', $xoopsDB->prefix($myprefix."_extent"));
define('RVTBL', $xoopsDB->prefix($myprefix."_reserv"));

define('HEADER_CSS', '<link rel="stylesheet" type="text/css" media="all" href="style.css" />');

define('PICAL', 'piCal');	// piCal dirname
define('EGUIDE_URL', XOOPS_URL.'/modules/'.$egdirname);
define('EGUIDE_PATH', XOOPS_ROOT_PATH.'/modules/'.$egdirname);
?>