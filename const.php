<?php
define("_RVSTAT_ORDER",0);
define("_RVSTAT_RESERVED",1);
define("_RVSTAT_REFUSED",2);

define("STAT_NORMAL",0);
define("STAT_POST",1);
//define("STAT_DISPLAY",2);
//define("STAT_EXPIRED",3);
define("STAT_DELETED",4);

$mydirname = basename(dirname(__FILE__));

define('EGTBL', $xoopsDB->prefix($mydirname));
define('CATBL', $xoopsDB->prefix($mydirname."_category"));
define('OPTBL', $xoopsDB->prefix($mydirname."_opt"));
define('EXTBL', $xoopsDB->prefix($mydirname."_extent"));
define('RVTBL', $xoopsDB->prefix($mydirname."_reserv"));

define('HEADER_CSS', '<link rel="stylesheet" type="text/css" media="all" href="style.css" />');

define('PICAL', 'piCal');	// piCal dirname
define('EGUIDE_URL', XOOPS_URL.'/modules/'.$mydirname);
define('EGUIDE_PATH', XOOPS_ROOT_PATH.'/modules/'.$mydirname);
?>