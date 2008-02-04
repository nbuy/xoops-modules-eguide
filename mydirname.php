<?php
# resolv dirname base naming
# $Id: mydirname.php,v 1.1 2008/02/04 05:02:43 nobu Exp $

global $mydirname, $myprefix;
$mydirpath = dirname(__FILE__);
$myprefix = $mydirname = basename($mydirpath);
if (preg_match('/^[^a-zA-Z0-9_]+$/', $mydirname)) die("Dirname not accept: $mydirname");
$mypostfix = preg_replace('/.*[^\d](\d*)$/', '$1', $mydirname);
if (!file_exists("$mydirpath/templates/{$myprefix}_index.html")) {
    $myprefix = 'eguide'.$mypostfix;	// using 'eguide' + number;
    if (!file_exists("$mydirpath/templates/{$myprefix}_index.html")) die("eguide configure error: $mydirname");
}
?>