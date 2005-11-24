<?php
define("_RVSTAT_ORDER",0);
define("_RVSTAT_RESERVED",1);
define("_RVSTAT_REFUSED",2);

define("STAT_NORMAL",0);
define("STAT_POST",1);
//define("STAT_DISPLAY",2);
//define("STAT_EXPIRED",3);
define("STAT_DELETED",4);

define('EGTBL', $xoopsDB->prefix("eguide"));
define('CATBL', $xoopsDB->prefix("eguide_category"));
define('OPTBL', $xoopsDB->prefix("eguide_opt"));
define('EXTBL', $xoopsDB->prefix("eguide_extent"));
define('RVTBL', $xoopsDB->prefix("eguide_reserv"));

define('HEADER_CSS', '<link rel="stylesheet" type="text/css" media="all" href="event.css" />');

?>