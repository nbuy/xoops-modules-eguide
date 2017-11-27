<?php
// Reservation Entry by Poster
//

include __DIR__ . '/header.php';
include __DIR__ . '/perm.php';

$eid                   = param('eid');
$exid                  = param('sub');
$uid                   = param('uid');
$data                  = fetch_event($eid, $exid, true);
$data['past_register'] = eguide_form_options('enable_past_register', 0);
$errs                  = [];
$now                   = time();
$member_only           = (ACCEPT_EMAIL != $xoopsModuleConfig['member_only'] && $uid > 0);

if (isset($_POST['eid'])) {
    include __DIR__ . '/reserv_func.php';
    $myts = MyTextSanitizer::getInstance();

    $vals = get_opt_values($data['optfield'], $errs, false, false);

    check_prev_order($data, $vals, $errs, true);
    $value = serialize_text($vals);
    $url   = EGUIDE_URL . '/receipt.php?eid=' . $eid;
    if ($exid) {
        $url .= '&sub=' . $exid;
    }
    if (!$errs) {
        $data['closetime'] = 0;    // override close order time
        $accept            = $data['autoaccept'];
        $strict            = $data['strict'];
        $persons           = $data['persons'];
        $num               = 1;
        $nlab              = eguide_form_options('label_persons');
        if ($nlab && isset($vals[$nlab])) {
            $num = (int)$vals[$nlab];
            if ($num < 1) {
                $num = 1;
            }
        }
        if (count_reserved($eid, $exid, $strict, $persons, $num)) {
            srand();
            $data['confirm'] = $conf = rand(10000, 99999);
            $email           = param('email', '');
            $ml              = $xoopsDB->quoteString($email);
            $operator        = $xoopsUser->getVar('uid');
            $xoopsDB->query('INSERT INTO ' . RVTBL . "
(eid,exid,uid,operator,rdate,email,status,confirm,info) VALUES
($eid,$exid,$uid,$operator,$now,$ml," . _RVSTAT_RESERVED . ",$conf," . $xoopsDB->quoteString($value) . ')');
            $data['rvid']       = $xoopsDB->getInsertId();
            $data['reserv_uid'] = $uid;
            if ($data['edate'] - $data['closetime'] > $now) {
                order_notify($data, $email, $value); // error ignore
            }
            redirect_header($url, 1, _MD_DBUPDATED);
        } else {
            $errs[] = _MD_RESERV_FULL;
        }
    }
}

if (empty($data)) {
    redirect_header(EGUIDE_URL . '/index.php', 2, _MD_NOEVENT);
}

$data['exid']    = $exid;
$data['isadmin'] = true;
$data['link']    = true;
include XOOPS_ROOT_PATH . '/header.php';
$GLOBALS['xoopsOption']['template_main'] = EGPREFIX . '_entry.tpl';
assign_module_css();
edit_eventdata($data);
$xoopsTpl->assign('event', $data);
if ($errs) {
    $xoopsTpl->assign('errors', $errs);
}
// check pical exists
/** @var XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$module        = $moduleHandler->getByDirname(PICAL);
if (is_object($module) && 1 == $module->getVar('isactive')) {
    $xoopsTpl->assign('caldate', formatTimestamp($data['edate'], 'Y-m-d'));
}
// page title
$xoopsTpl->assign('xoops_pagetitle', $xoopsModule->getVar('name') . ' | ' . _MD_RESERVATION);
if ($data['closedate'] < $now && !$data['past_register']) {
    if ($data['reservation']) {
        $xoopsTpl->assign('message', _MD_RESERV_CLOSE);
    }
} elseif ($data['reservation']) {
    $reserved = false;
    if ($uid) {
        $result   = $xoopsDB->query('SELECT * FROM ' . RVTBL . " WHERE eid=$eid AND exid=$exid AND uid=$uid");
        $reserved = ($xoopsDB->getRowsNum($result) > 0);
    }
    if ($data['strict'] && $data['persons'] <= $data['reserved']) {
        $xoopsTpl->assign('message', _MD_RESERV_FULL);
    } elseif ($reserved) {
        $xoopsTpl->assign('message', _MD_RESERVED);
    } else {
        if (empty($_POST['email'])) {
            $_POST['email'] = '';
        }
        $form                = eventform($data, $uid);
        $form['lang_email']  = preg_replace('/\\*$/', '', _MD_EMAIL);
        $form['member_only'] = $member_only;
        $xoopsTpl->assign('form', $form);
    }
}

// select users.uid
if (!$member_only && eguide_form_options('need_bind_uid', 0)) {
    $member_only = true;
}
if ('users' === param('op', '') || ($member_only && !$uid)) {
    $GLOBALS['xoopsOption']['template_main'] = EGPREFIX . '_userssel.tpl';
    require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

    // search string
    $s    = param('search', '');
    $cols = eguide_form_options('users_search_columns', 'uname,email');
    $cond = $s ? "concat($cols) like " . $xoopsDB->quoteString("%$s%") : '1';

    $cond = 'FROM ' . $xoopsDB->prefix('users') . " WHERE $cond";
    $res  = $xoopsDB->query("SELECT count(uid) $cond");
    list($total) = $xoopsDB->fetchRow($res);
    $start    = param('start');
    $max_list = $xoopsModuleConfig['max_list'];
    $nav      = new XoopsPageNav($total, $max_list, $start, 'start', "eid=$eid" . ($exid ? "&exid=$exid" : '') . ($s ? "search=$s" : ''));
    $xoopsTpl->assign('users_total', $total);
    if ($total > $max_list) {
        $xoopsTpl->assign('navigation', $nav->renderNav());
    }

    $res   = $xoopsDB->query("SELECT uid,$cols $cond", $max_list, $start);
    $users = [];
    if (empty($s)) {
        $users[] = ['uid' => -1, 'uname' => $GLOBALS['xoopsConfig']['anonymous']];
    }
    while ($user = $xoopsDB->fetchArray($res)) {
        $users[] = $user;
    }
    $labels = eguide_form_options('users_search_labels', '');
    $lang   = ['uname' => _MD_UNAME, 'email' => _MD_EMAIL];
    $cols   = explode(',', $cols);
    $labels = $labels ? explode(',', $labels) : [];
    for ($i = 0, $iMax = count($cols); $i < $iMax; ++$i) {
        $col = $cols[$i];
        if (isset($labels[$i])) {
            $lang[$col] = $labels[$i];
        } elseif (!isset($lang[$col])) {
            $lang[$col] = $col;
        }
    }
    $xoopsTpl->assign('columns', $cols);
    $xoopsTpl->assign('users', $users);
    $xoopsTpl->assign('lang_users', $lang);
}

include XOOPS_ROOT_PATH . '/footer.php';
