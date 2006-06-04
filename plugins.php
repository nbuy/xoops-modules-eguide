<?php
global $hooked_function;
$hooked_function = array('check'=>array(), 'reserve'=>array(), 'cancel'=>array());
$dir = EGUIDE_PATH.'/plugins';
if ($xoopsModuleConfig['use_plugins'] && is_dir($dir)) {
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
	if (preg_match('/^([\w\d]+)\.php$/', $file, $d)) {
	    $name = $d[1];
	    $module_handler =& xoops_gethandler('module');
	    $module =& $module_handler->getByDirname($name);

	    if ($module && $module->getVar('isactive')) {
		include ("$dir/$file");
		// register hook
		foreach (array('check', 'reserve', 'cancel') as $act) {
		    $func = 'eguide_'.$name.'_'.$act;
		    if (function_exists($func)) {
			$hooked_function[$act][] = $func;
		    }
		}
	    }
	}
    }
}
?>