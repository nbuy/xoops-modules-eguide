<?php
# user/admin common use resources
#

define('_MD_ORDER_DATE', 'Fecha');
define('_MD_CSV_OUT', 'CSV Format');
define('_MD_EXTENT_DATE', 'Open Date');
define('_MD_RESERV_PERSONS', 'Max no. of Persons');
define('_MD_INFO_REQUEST', 'Notify Registered users');
define('_MD_INFO_COUNT', 'total %d');

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
    1 => 'New line makes tag &lt;br&gt;',
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
