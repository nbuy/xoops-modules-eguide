<?php
// checking permittion for event adiminstration.
// $Id: perm.php,v 1.3 2005/11/18 17:08:02 nobu Exp $
function eguide_perm($eid) {
    global $xoopsDB, $xoopsUser, $xoopsModule, $xoopsModuleConfig, $_POST;
    if (!$xoopsUser) return false;		   	// need login
    elseif ($xoopsUser->isAdmin($xoopsModule->mid())) ;	// ok admin
    elseif (empty($_POST['op'])||$_POST['op']=="new"){
	$groups = $xoopsUser->groups();
	if (!in_array($xoopsModuleConfig['group'],$groups)) return false;
    } elseif (isset($eid)) {				// edit? check poster
	$tbl = $xoopsDB->prefix("eguide");
	$result = $xoopsDB->query("SELECT uid FROM $tbl WHERE eid=$eid");
	$data = $xoopsDB->fetchArray($result);
	if ($xoopsUser->uid() != $data['uid']) return false; // need poster
    }
    return true;
}

if (!eguide_perm(isset($eid)?$eid:0)) {
    redirect_header("index.php",2,_NOPERM);
    exit();
}
?>