<?php
# import from GIJOE's pico
#

define('ICON_ASIS', 'module_icon.png');
define('ICON_IMAGE', 'images/eguide_slogo2.png');

$modulePath       = __DIR__;
$moduleDirName    = basename($modulePath);
$icon_cache_limit = 3600; // default 3600sec == 1hour

session_cache_limiter('public');
header('Expires: ' . date('r', (int)(time() / $icon_cache_limit) * $icon_cache_limit + $icon_cache_limit));

header("Cache-Control: public, max-age=$icon_cache_limit");
header('Last-Modified: ' . date('r', (int)(time() / $icon_cache_limit) * $icon_cache_limit));
header('Content-type: image/png');

if (file_exists($modulePath . '/module_icon.png')) {
    $use_custom_icon = true;
    $icon_fullpath   = $modulePath . '/' . ICON_TEMPLATE;
} else {
    $use_custom_icon = false;
    $icon_fullpath   = $modulePath . '/' . ICON_IMAGE;
}

if (!$use_custom_icon && function_exists('imagecreatefrompng') && function_exists('imagecolorallocate')
    && function_exists('imagestring')
    && function_exists('imagepng')) {
    $im = imagecreatefrompng($icon_fullpath);

    $color = imagecolorallocate($im, 0, 0, 0); // black
    $px    = (92 - 6 * strlen($moduleDirName)) / 2;
    $bg    = imagecolorat($im, $px, 34);
    imagefilledrectangle($im, 3, 34, 84, 47, $bg);
    imagestring($im, 3, $px, 34, $moduleDirName, $color);
    imagepng($im);
    imagedestroy($im);
} else {
    readfile($icon_fullpath);
}
