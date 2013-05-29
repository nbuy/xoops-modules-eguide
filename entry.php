<?php
// Reservation Entry by Poster
// $Id: entry.php,v 1.10 2010/06/27 04:12:30 nobu Exp $

include 'header.php';
include 'perm.php';

$eid = param('eid');
$exid = param('sub');
$uid = param('uid');
$data = fetch_event($eid, $exid, true);
$data['past_register'] = eguide_form_options('enable_past_register', 0);
$errs = array();
$now=time();
$member_only = ($xoopsModuleConfig['member_only']!=ACCEPT_EMAIL && $uid>0);

if (isset($_POST['eid'])) {
    include 'reserv_func.php';
    $myts =& MyTextSanitizer::getInstance();

    $vals = get_opt_values($data['optfield'], $errs, false, false);

    check_prev_order($data, $vals, $errs, true);
    $value = serialize_text($vals);
    $url = EGUIDE_URL.'/receipt.php?eid='.$eid;
    if ($exid) $url .= '&sub='.$exid;
    if (!$errs) {
	$data['closetime'] = 0;	// override close order time
	$accept = $data['autoaccept'];
	$strict = $data['strict'];
	$persons = $data['persons'];
	$num = 1;
	$nlab = eguide_form_options('label_persons');
	if ($nlab && isset($vals[$nlab])) {
	    $num =  intval($vals[$nlab]);
	    if ($num<1) $num = 1;
	}
	if (count_reserved($eid, $exid, $strict, $persons, $num)) {
	    srand();
	    $data['confirm'] = $conf = rand(10000,99999);
	    $email = param('email', '');
	    $ml = $xoopsDB->quoteString($email);
	    $operator=$xoopsUser->getVar('uid');
	    $xoopsDB->query('INSERT INTO '.RVTBL." 
(eid,exid,uid,operator,rdate,email,status,confirm,info) VALUES
($eid,$exid,$uid,$operator,$now,$ml,"._RVSTAT_RESERVED.",$conf,".
			    $xoopsDB->quoteString($value).")");
	    $data['rvid'] = $xoopsDB->getInsertId();
	    $data['reserv_uid'] = $uid;
	    if ($data['edate']-$data['closetime']>$now) {
		order_notify($data, $email, $value); // error ignore
	    }
	    redirect_header($url, 1, _MD_DBUPDATED);
	    exit;
	} else $errs[] = _MD_RESERV_FULL;
    }
}

if (empty($data)) {
	redirect_header(EGUIDE_URL."/index.php",2,_MD_NOEVENT);
	exit();
}

$data['exid']=$exid;
$data['isadmin'] = true;
$data['link'] = true;
include XOOPS_ROOT_PATH.'/header.php';
$xoopsOption['template_main'] = EGPREFIX.'_entry.html';
assign_module_css();
edit_eventdata($data);
$xoopsTpl->assign('event', $data);
if ($errs) $xoopsTpl->assign('errors', $errs);
// check pical exists
$module_handler =& xoops_gethandler('module');
$module =& $module_handler->getByDirname(PICAL);
if (is_object($module) && $module->getVar('isactive')==1) {
    $xoopsTpl->assign('caldate', formatTimestamp($data['edate'], 'Y-m-d'));
}
// page title
$xoopsTpl->assign('xoops_pagetitle', $xoopsModule->getVar('name')." | "._MD_RESERVATION);
if ($data['closedate'] < $now  && !$data['past_register']) {
    if ($data['reservation']) $xoopsTpl->assign('message', _MD_RESERV_CLOSE);
} elseif ($data['reservation']) {
    $reserved = false;
    if ($uid) {
	$result = $xoopsDB->query("SELECT * FROM ".RVTBL." WHERE eid=$eid AND exid=$exid AND uid=$uid");
	$reserved = ($xoopsDB->getRowsNum($result)>0);
    }
    if ($data['strict'] && $data['persons']<=$data['reserved']) {
	$xoopsTpl->assign('message', _MD_RESERV_FULL);
    } elseif ($reserved) {
	$xoopsTpl->assign('message', _MD_RESERVED);
    } else {
	if (empty($_POST['email'])) $_POST['email'] = '';
	$form = eventform($data, $uid);
	$form['lang_email'] = preg_replace('/\\*$/', '', _MD_EMAIL);
	$form['member_only'] = $member_only;
	$xoopsTpl->assign('form', $form);
    }
}

// select users.uid 
if (!$member_only && eguide_form_options('need_bind_uid', 0)) $member_only = true;
if (param('op', '')=='users' || ($member_only && !$uid)) {
    $xoopsOption['template_main'] = EGPREFIX.'_userssel.html';
    include_once XOOPS_ROOT_PATH.'/class/pagenav.php';

    // search string
    $s = param('search', '');
    $cols = eguide_form_options('users_search_columns', 'uname,email');
    $cond = $s?"concat($cols) like ".$xoopsDB->quoteString("%$s%"):"1";

    $cond = "FROM ".$xoopsDB->prefix('users')." WHERE $cond";
    $res = $xoopsDB->query("SELECT count(uid) $cond");
    list($total) = $xoopsDB->fetchRow($res);
    $start = param('start');
    $max_list = $xoopsModuleConfig['max_list'];
    $nav = new XoopsPageNav($total, $max_list, $start, "start", "eid=$eid".($exid?"&exid=$exid":'').($s?"search=$s":''));
    $xoopsTpl->assign('users_total', $total);
    if ($total>$max_list) $xoopsTpl->assign('navigation',$nav->renderNav());
    
    $res = $xoopsDB->query("SELECT uid,$cols $cond", $max_list, $start);
    $users = array();
    if (empty($s)) {
	$users[] = array('uid'=>-1, 'uname'=>$GLOBALS['xoopsConfig']['anonymous']);
    }
    while ($user = $xoopsDB->fetchArray($res)) {
	$users[] = $user;
    }
    $labels = eguide_form_options('users_search_labels', '');
    $lang = array('uname'=>_MD_UNAME, 'email'=>_MD_EMAIL);
    $cols = explode(',', $cols);
    $labels = $labels?explode(',', $labels):array();
    for ($i=0; $i < count($cols); $i++) {
	$col = $cols[$i];
	if (isset($labels[$i])) $lang[$col] = $labels[$i];
	else if (!isset($lang[$col])) $lang[$col] = $col;
    }
    $xoopsTpl->assign('columns', $cols);
    $xoopsTpl->assign('users', $users);
    $xoopsTpl->assign('lang_users', $lang);
}
    
include XOOPS_ROOT_PATH.'/footer.php';
?>