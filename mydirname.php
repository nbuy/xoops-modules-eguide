<?php
# resolv dirname base naming
#

global $egdirname, $myprefix;
$modulePath = __DIR__;
$myprefix   = $egdirname = basename($modulePath);
if (preg_match('/^[^a-zA-Z0-9_]+$/', $egdirname)) {
    die("Dirname not accept: $egdirname");
}
$mypostfix = preg_replace('/.*[^\d](\d*)$/', '$1', $egdirname);
if (!file_exists("$modulePath/templates/{$myprefix}_index.tpl")) {
    $myprefix = 'eguide' . $mypostfix;    // using 'eguide' + number;
    if (!file_exists("$modulePath/templates/{$myprefix}_index.tpl")) {
        die("eguide configure error: $egdirname");
    }
}
