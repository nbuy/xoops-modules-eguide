<?php
// checking permittion for event adiminstration.
// $Id: perm.php,v 1.5 2005/11/24 08:15:49 nobu Exp $
function eguide_perm($eid) {
    global $xoopsDB, $xoopsUser, $xoopsModule, $xoopsModuleConfig, $_POST;
    if (!$xoopsUser) return false;		   	// need login
    elseif ($xoopsUser->isAdmin($xoopsModule->getVar('mid'))) ;	// ok admin
    elseif (empty($_POST['op'])||$_POST['op']=="new"){
	$groups = $xoopsUser->groups();
	if (!in_array($xoopsModuleConfig['group'],$groups)) return false;
    } elseif (isset($eid)) {				// edit? check poster
	$tbl = $xoopsDB->prefix("eguide");
	$result = $xoopsDB->query("SELECT uid FROM $tbl WHERE eid=$eid");
	$data = $xoopsDB->fetchArray($result);
	if ($xoopsUser->getVar('uid') != $data['uid']) return false; // need poster
    }
    return true;
}

if (!eguide_perm(param('eid'))) {
    redirect_header("index.php",2,_NOPERM);
    exit();
}
?>