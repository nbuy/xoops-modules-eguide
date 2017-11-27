<?php
// checking permittion for event adiminstration.
//
/**
 * @param $eid
 * @return bool
 */
function eguide_perm($eid)
{
    global $xoopsDB, $xoopsUser, $xoopsModule, $xoopsModuleConfig, $_POST;
    if (!$xoopsUser) {
        return false;
    }            // need login
    elseif ($xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
    }    // ok admin
    elseif (!empty($eid)) {                // edit? check poster
        $result = $xoopsDB->query('SELECT uid FROM ' . EGTBL . " WHERE eid=$eid");
        $data   = $xoopsDB->fetchArray($result);
        if ($xoopsUser->getVar('uid') != $data['uid']) {
            return false;
        } // need poster
    } elseif (empty($_POST['op']) || 'new' === $_POST['op']) {
        $groups = $xoopsUser->groups();
        if (!in_array($xoopsModuleConfig['group'], $groups)) {
            return false;
        }
    }

    return true;
}

if (!eguide_perm(param('eid'))) {
    redirect_header(EGUIDE_URL . '/index.php', 2, _NOPERM);
}
