<?php
# user/admin common use resources
#

define('_MD_ORDER_DATE', 'Bestelldatum');
define('_MD_CSV_OUT', 'Als CSV exportieren');
define('_MD_EXTENT_DATE', 'Startdatum');
define('_MD_RESERV_PERSONS', 'Max. Anzahl der Plätze');
define('_MD_INFO_REQUEST', 'registrierte Benutzer Benachrichtigen');
define('_MD_INFO_COUNT', 'Anzahl %d');

global $expire_set, $edit_style, $ev_stats, $ev_extents, $rv_stats;

$expire_set = [
    '+0'        => 'gleicher Tag',
    '+86400'    => 'Folgetag',
    '+172800'   => '2 Tage',
    '+259200'   => '3 Tage',
    '+604800'   => '1 Woche',
    '+2592000'  => '1 Monat',
    '+7948800'  => '3 Monat',
    '+15768000' => '6 Monate'
];

$edit_style = [
    0 => 'New BB-Code',
    1 => 'Neue Zeile erzeugt Zeilenumbruch',
    2 => 'HTML deaktivieren'
];

$ev_stats = [
    0 => 'aktiv',
    1 => 'wartend',
    4 => 'gelöscht'
];

$rv_stats = [
    0 => 'wartend',
    1 => 'reserviert',
    2 => 'abgelehnt'
];

$ev_extents = [
    'none'    => 'einmalig',
    'daily'   => 'täglich',
    'weekly'  => 'wöchentlich',
    'monthly' => 'monatlich'
];
