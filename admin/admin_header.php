<?php
include("../../../mainfile.php");
include("../const.php");
//error_reporting(E_ALL);
include_once(XOOPS_ROOT_PATH."/class/xoopsmodule.php");
include(XOOPS_ROOT_PATH."/include/cp_functions.php");
if ( $xoopsUser ) {
	$xoopsModule = XoopsModule::getByDirname("eguide");
	if ( !$xoopsUser->isAdmin($xoopsModule->mid()) ) { 
		redirect_header(XOOPS_URL."/",3,_NOPERM);;
		exit();
	}
} else {
	redirect_header(XOOPS_URL."/",3,_NOPERM);
	exit();
}
if ( file_exists("../language/".$xoopsConfig['language']."/admin.php") ) {
	include("../language/".$xoopsConfig['language']."/admin.php");
} else {
	include("../language/english/admin.php");
}
if (function_exists("getCache")) {
    eval(getCache($xoopsModule->dirname()."/config.php"));
} else {
    include(XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname()."/cache/config.php");
    function putCache($tag, $content) {
	$file = XOOPS_ROOT_PATH."/modules/".preg_replace('/\//', "/cache/", $tag);
	$fp = fopen($file, "w");
	fwrite($fp, "<?php\n$content\n?>");
	fclose($fp);
    }
}
?>
