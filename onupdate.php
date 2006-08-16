<?php
# eguide module onUpdate proceeding.
# $Id: onupdate.php,v 1.2 2006/08/16 16:57:10 nobu Exp $

global $xoopsDB;
include dirname(__FILE__)."/const.php";

$config = XOOPS_ROOT_PATH.'/modules/eguide/cache/config.php';

if (file_exists($config)) {
    include $config;
    $cri_mods = new CriteriaCompo(new Criteria('conf_modid', $xoopsModule->getVar('mid')));
    $criteria = new CriteriaCompo();
    foreach (array('group', 'notify', 'auth', 'max_item', 'max_event', 'user_notify') as $name) {
	if ($eventConfig[$name] != $xoopsModuleConfig[$name]) {
	    $criteria->add(new Criteria('conf_name', $name), 'OR');
	}
    }
    if (count($criteria->criteriaElements)) {
	$cri_mods->add($criteria);
	$configs =& $config_handler->getConfigs($criteria);
	foreach ($configs as $config) {
	    $name = $config->getVar('conf_name');
	    $config->setConfValueForInput($eventConfig[$name]);
	}
    }
    unlink($config);
}
// create table eguide_category, eguide_extent

$xoopsDB->query('UPDATE '.EGTBL.' SET ldate=edate WHERE ldate=0');

// addional field in 2.0b
add_field(RVTBL, 'exid', "INT DEFAULT 0 NOT NULL", 'eid');
add_field(OPTBL, 'closetime', "INT DEFAULT 0 NOT NULL", 'reserved');

// eguide_cat table add in 2.0
$xoopsDB->query('SELECT * FROM '.CATBL, 1);
if ($xoopsDB->errno()) { // check exists
    $xoopsDB->query("CREATE TABLE ".CATBL." (
  catid    integer NOT NULL auto_increment,
  catname  varchar(40) NOT NULL,
  catimg   varchar(255),
  catdesc  text,
  PRIMARY KEY  (catid)
)");
    $xoopsDB->query("INSERT INTO ".CATBL." VALUES(1, '')");
}

// eguide_extent table add in 2.0
$xoopsDB->query('SELECT * FROM '.EXTBL, 1);
if ($xoopsDB->errno()) { // check exists
    $xoopsDB->query("CREATE TABLE ".EXTBL." (
  exid    integer NOT NULL auto_increment,
  eidref  integer NOT NULL,
  exdate  integer NOT NULL,
  expersons integer,
  reserved int(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (exid)
)");
}

// after 2.0b2 added
add_field(EXTBL, 'expersons', 'INTEGER', 'exdate');
// after 2.1 added
add_field(OPTBL, 'redirect', "varchar(128) NOT NULL default ''", 'optfield');

function add_field($table, $field, $type, $after) {
    global $xoopsDB;
    $res = $xoopsDB->query("SELECT $field FROM $table", 1);
    if (empty($res) && $xoopsDB->errno()) { // check exists
	if ($after) $after = "AFTER $after";
	$res = $xoopsDB->query("ALTER TABLE $table ADD $field $type $after");
    } else return false;
    if (!$res) {
	echo "<div class='errorMsg'>".$xoopsDB->errno()."</div>\n";
    }
    return $res;
}
?>