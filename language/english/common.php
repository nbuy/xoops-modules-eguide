<?php
# user/admin common use resources
#

define('_MD_ORDER_DATE', 'Order Date');
define('_MD_CSV_OUT', 'CSV Format');
define('_MD_EXTENT_DATE', 'Open Date');
define('_MD_RESERV_PERSONS', 'Max no. of Persons');
define('_MD_INFO_REQUEST', 'Notify Registered users');
define('_MD_INFO_COUNT', 'count %d');
define('_MD_EXPORT_CHARSET', 'UTF-8');

global $expire_set, $edit_style, $ev_stats, $ev_extents, $rv_stats;

$expire_set = [
    ''        => '-- Use text --',
    '+0'      => 'Same Day',
    '+3600'   => 'an hour',
    '+86400'  => 'Next Day',
    '+172800' => '2 days',
    '+259200' => '3 days',
    '+604800' => '1 week'
];

$edit_style = [
    0 => 'only XOOPS tags',
    1 => 'New line makes tag &lt;br>',
    2 => 'disable HTML tags'
];

$ev_stats = [
    0 => 'display',
    1 => 'waiting',
    4 => 'deleted'
];

$rv_stats = [
    0 => 'waiting',
    1 => 'reserved',
    2 => 'refused'
];

$ev_extents = [
    'none'    => 'Once',
    'daily'   => 'Daily',
    'weekly'  => 'Weekly',
    'monthly' => 'Monthly'
];


$moduleDirName      = basename(dirname(dirname(__DIR__)));
$moduleDirNameUpper = strtoupper($moduleDirName);

define('CO_' . $moduleDirNameUpper . '_GDLIBSTATUS', 'GD library support: ');
define('CO_' . $moduleDirNameUpper . '_GDLIBVERSION', 'GD Library version: ');
define('CO_' . $moduleDirNameUpper . '_GDOFF', "<span style='font-weight: bold;'>Disabled</span> (No thumbnails available)");
define('CO_' . $moduleDirNameUpper . '_GDON', "<span style='font-weight: bold;'>Enabled</span> (Thumbsnails available)");
define('CO_' . $moduleDirNameUpper . '_IMAGEINFO', 'Server status');
define('CO_' . $moduleDirNameUpper . '_MAXPOSTSIZE', 'Max post size permitted (post_max_size directive in php.ini): ');
define('CO_' . $moduleDirNameUpper . '_MAXUPLOADSIZE', 'Max upload size permitted (upload_max_filesize directive in php.ini): ');
define('CO_' . $moduleDirNameUpper . '_MEMORYLIMIT', 'Memory limit (memory_limit directive in php.ini): ');
define('CO_' . $moduleDirNameUpper . '_METAVERSION', "<span style='font-weight: bold;'>Downloads meta version:</span> ");
define('CO_' . $moduleDirNameUpper . '_OFF', "<span style='font-weight: bold;'>OFF</span>");
define('CO_' . $moduleDirNameUpper . '_ON', "<span style='font-weight: bold;'>ON</span>");
define('CO_' . $moduleDirNameUpper . '_SERVERPATH', 'Server path to XOOPS root: ');
define('CO_' . $moduleDirNameUpper . '_SERVERUPLOADSTATUS', 'Server uploads status: ');
define('CO_' . $moduleDirNameUpper . '_SPHPINI', "<span style='font-weight: bold;'>Information taken from PHP ini file:</span>");
define('CO_' . $moduleDirNameUpper . '_UPLOADPATHDSC', 'Note. Upload path *MUST* contain the full server path of your upload folder.');

define('CO_' . $moduleDirNameUpper . '_PRINT', "<span style='font-weight: bold;'>Print</span>");
define('CO_' . $moduleDirNameUpper . '_PDF', "<span style='font-weight: bold;'>Create PDF</span>");


define('CO_' . $moduleDirNameUpper . '_UPGRADEFAILED0', "Update failed - couldn't rename field '%s'");
define('CO_' . $moduleDirNameUpper . '_UPGRADEFAILED1', "Update failed - couldn't add new fields");
define('CO_' . $moduleDirNameUpper . '_UPGRADEFAILED2', "Update failed - couldn't rename table '%s'");
define('CO_' . $moduleDirNameUpper . '_ERROR_COLUMN', 'Could not create column in database : %s');
define('CO_' . $moduleDirNameUpper . '_ERROR_BAD_XOOPS', 'This module requires XOOPS %s+ (%s installed)');
define('CO_' . $moduleDirNameUpper . '_ERROR_BAD_PHP', 'This module requires PHP version %s+ (%s installed)');
define('CO_' . $moduleDirNameUpper . '_ERROR_TAG_REMOVAL', 'Could not remove tags from Tag Module');

define('CO_' . $moduleDirNameUpper . '_FOLDERS_DELETED_OK', 'Upload Folders have been deleted');

// Error Msgs
define('CO_' . $moduleDirNameUpper . '_ERROR_BAD_DEL_PATH', 'Could not delete %s directory');
define('CO_' . $moduleDirNameUpper . '_ERROR_BAD_REMOVE', 'Could not delete %s');
define('CO_' . $moduleDirNameUpper . '_ERROR_NO_PLUGIN', 'Could not load plugin');


//Help
define('CO_' . $moduleDirNameUpper . '_DIRNAME', basename(dirname(dirname(__DIR__))));
define('CO_' . $moduleDirNameUpper . '_HELP_HEADER', __DIR__.'/help/helpheader.tpl');
define('CO_' . $moduleDirNameUpper . '_BACK_2_ADMIN', 'Back to Administration of ');
define('CO_' . $moduleDirNameUpper . '_OVERVIEW', 'Overview');
define('CO_' . $moduleDirNameUpper . '_NAME', _MI_EGUIDE_NAME);

//define('CO_' . $moduleDirNameUpper . '_HELP_DIR', __DIR__);

//help multi-page
define('CO_' . $moduleDirNameUpper . '_DISCLAIMER', 'Disclaimer');
define('CO_' . $moduleDirNameUpper . '_LICENSE', 'License');
define('CO_' . $moduleDirNameUpper . '_SUPPORT', 'Support');

define('CO_' . $moduleDirNameUpper . '_SUMMARY', 'Summary');
define('CO_' . $moduleDirNameUpper . '_USING_STEPS', 'Using Step by Step');
define('CO_' . $moduleDirNameUpper . '_FUNCTIONS', 'Functions');
define('CO_' . $moduleDirNameUpper . '_CONFIGURATION', 'Event Configuration');
define('CO_' . $moduleDirNameUpper . '_FIELD_FORMATS', 'Custom Field Formats');
define('CO_' . $moduleDirNameUpper . '_RESERVATION_PLUGIN', 'Reservation limit plugins');
define('CO_' . $moduleDirNameUpper . '_CHANGES', 'Changes');
