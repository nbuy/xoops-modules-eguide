<?php
# eguide module onUpdate proceeding.
#

global $xoopsDB;
require_once __DIR__ . '/const.php';

$config = XOOPS_ROOT_PATH . '/modules/eguide/cache/config.php';

if (file_exists($config)) {
    include $config;
    $cri_mods = new CriteriaCompo(new Criteria('conf_modid', $xoopsModule->getVar('mid')));
    $criteria = new CriteriaCompo();
    foreach (['group', 'notify', 'auth', 'max_item', 'max_event', 'user_notify'] as $name) {
        if ($eventConfig[$name] != $xoopsModuleConfig[$name]) {
            $criteria->add(new Criteria('conf_name', $name), 'OR');
        }
    }
    if (count($criteria->criteriaElements)) {
        $cri_mods->add($criteria);
        $configs =& $configHandler->getConfigs($criteria);
        foreach ($configs as $config) {
            $name = $config->getVar('conf_name');
            $config->setConfValueForInput($eventConfig[$name]);
        }
    }
    unlink($config);
}
// create table eguide_category, eguide_extent

$xoopsDB->query('UPDATE ' . EGTBL . ' SET ldate=edate WHERE ldate=0');

// add field in 2.0b
add_field(RVTBL, 'exid', 'INT DEFAULT 0 NOT NULL', 'eid');
add_field(OPTBL, 'closetime', 'INT DEFAULT 0 NOT NULL', 'reserved');
// add field in 2.0b2 added
add_field(EXTBL, 'expersons', 'INTEGER', 'exdate');

// eguide_cat table add in 2.0
$xoopsDB->query('SELECT * FROM ' . CATBL, 1);
if ($xoopsDB->errno()) { // check exists
    $xoopsDB->query('CREATE TABLE ' . CATBL . " (
  catid    INTEGER NOT NULL AUTO_INCREMENT,
  catname  VARCHAR(40) NOT NULL,
  catimg   VARCHAR(255),
  catdesc  TEXT,
  catpri   INTEGER NOT NULL DEFAULT '0',
  weight   INTEGER NOT NULL DEFAULT '0',
  PRIMARY KEY  (catid)
)");
    $xoopsDB->query('INSERT INTO ' . CATBL . " VALUES(1, '')");
}

// eguide_extent table add in 2.0
$xoopsDB->query('SELECT * FROM ' . EXTBL, 1);
if ($xoopsDB->errno()) { // check exists
    $xoopsDB->query('CREATE TABLE ' . EXTBL . " (
  exid    INTEGER NOT NULL AUTO_INCREMENT,
  eidref  INTEGER NOT NULL,
  exdate  INTEGER NOT NULL,
  expersons INTEGER,
  reserved INT(8) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (exid)
)");
    report_message(" Add new table: <b>$table</b>");
}

// add field in 2.1
$res = $xoopsDB->query('SELECT optvars FROM ' . OPTBL, 1);
if (empty($res) && $xoopsDB->errno()) {
    add_field(OPTBL, 'redirect', "varchar(128) NOT NULL default ''", 'optfield');
    // change field name and type
    $xoopsDB->query('ALTER TABLE ' . OPTBL . ' CHANGE `redirect` `optvars` TEXT');
    // convert internal format
    $xoopsDB->query('UPDATE ' . OPTBL . " SET optvars=concat('redirect=', optvars) WHERE optvars<>''");
}
// add field in 2.4
add_field(CATBL, 'catpri', 'INT DEFAULT 0 NOT NULL', 'catdesc');
add_field(CATBL, 'weight', 'INT DEFAULT 0 NOT NULL', 'catpri');
// add field in 2.7
add_field(RVTBL, 'operator', 'INT', 'uid');

/**
 * @param $msg
 */
function report_message($msg)
{
    global $msgs;        // module manager's variable
    static $first = true;
    if ($first) {
        $msgs[] = 'Update Database...';
        $first  = false;
    }
    $msgs[] = "&nbsp;&nbsp; $msg";
}

/**
 * @param $table
 * @param $field
 * @param $type
 * @param $after
 * @return bool|void
 */
function add_field($table, $field, $type, $after)
{
    global $xoopsDB;
    $res = $xoopsDB->query("SELECT $field FROM $table", 1);
    if (empty($res) && $xoopsDB->errno()) { // check exists
        if ($after) {
            $after = "AFTER $after";
        }
        $res = $xoopsDB->query("ALTER TABLE $table ADD $field $type $after");
    } else {
        return false;
    }
    report_message(" Add new field: <b>$table.$field</b>");
    if (!$res) {
        echo "<span style='color: red; font: bold;'>" . $xoopsDB->error() . "</span>\n";
    }

    return $res;
}
