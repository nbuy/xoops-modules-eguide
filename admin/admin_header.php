<?php
include("../../../mainfile.php");
include("../const.php");
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
$modbase = XOOPS_ROOT_PATH."/modules/".$xoopsModule->dirname();
$loc="$modbase/language/".$xoopsConfig['language'];
if ( file_exists("$loc/admin.php") ) {
	include("$loc/admin.php");
} else {
	include("$modbase/language/english/admin.php");
}
if ( file_exists("$loc/modinfo.php") ) {
	include_once("$loc/modinfo.php");
} else {
	include_once("$modbase/language/english/modinfo.php");
}
if (function_exists("getCache")) {
    eval(getCache($xoopsModule->dirname()."/config.php"));
} else {
    include("$modbase/cache/config.php");
    function putCache($tag, $content) {
	$file = XOOPS_ROOT_PATH."/modules/".preg_replace('/\//', "/cache/", $tag);
	$fp = fopen($file, "w");
	if ($fp) {
	    fwrite($fp, "<?php\n$content\n?>");
	    fclose($fp);
	} else {
	    redirect_header("index.php",5,sprintf(_MUSTWABLE, $file));
	    exit();
	}
    }
}
?>
