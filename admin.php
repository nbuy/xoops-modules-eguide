<?php
// Event Administration by Poster
// $Id: admin.php,v 1.12 2005/11/24 08:15:48 nobu Exp $

include 'header.php';
include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
include_once 'notify.inc.php';

require 'perm.php';

$eid = param('eid');
$op = param('op', $eid?'edit':'new');

if (isset($_POST['save'])) $op = 'save';
elseif (isset($_POST['preview'])) $op = 'preview';
elseif (isset($_POST['editdate'])) $op = 'date';
elseif (empty($op)) $op = 'new';

// store in database
$adm = $xoopsUser->isAdmin($xoopsModule->mid());
$uid = $xoopsUser->getVar('uid');

// set form data
$iargs = array('reservation', 'strict', 'autoaccept', 'notify',
	       'persons', 'style'); // integer value default '0'
$targs = array('title', 'summary', 'body', 'optfield');

$myts =& MyTextSanitizer::getInstance();
$xoopsOption['template_main'] = 'eguide_admin.html';

if ($op=='new') {
    // default value in post form
    $data = array('lang_event_edit'=>_MD_NEWSUB,
		  'reservation'	=> 1, // reservation: 0=disable, 1=enable
		  'style'	=> 1, // edit text: 0=html, 1=CRLF, 2=plain
		  'autoaccept'	=> 1, // order to: 0=confirm, 1=accepted
		  'notify'	=> 1, // notify to poster: 0=nothing, 1=send
		  'strict'	=> 1, // order when full: 0=continue, 1=stop
		  'persons'	=> 10, // how many persons/sheet in room
		  'optfield'	=>
		  $xoopsModuleConfig['member_only']?'':_MD_RESERV_DEFAULT_ITEM,
		  'title'	=> '', // contents
		  'summary'	=> '',
		  'body'	=> '',
		  'edate'	=> time()+3600*24, // now + a day
		  'event'	=> '',
		  'topicid'	=> 1);
} else {
    if ($eid) {
	$result = $xoopsDB->query('SELECT * FROM '.EGTBL.' e LEFT JOIN '.OPTBL.' o ON e.eid=o.eid LEFT JOIN '.CATBL." ON topicid=catid WHERE e.eid=$eid");
	$data = $xoopsDB->fetchArray($result);
	$edate = $data['edate'];
    } else {
	$data = array();
    }
    $data['lang_event_edit'] = _MD_EDITARTICLE;
    if ($op == 'preview' || $op == 'save' || $op == 'date') {
	$edate = getDateField("edate");
	if (isset($_POST['expire'])) {
	    $expire = $edate + intval($_POST['expire']);
	} else {
	    $expire = getDateField('expire');
	}
	$data['ldate'] = $data['edate'] = $edate;
	$data['expire'] = $expire;
	$data['topicid'] = param('topicid', 1);
	foreach ($iargs as $name) {
	    $data[$name] = param($name);
	}
	$data['ldate'] = 0;
	foreach ($targs as $name) {
	    $data[$name] = param($name, "");
	}
	if ($adm) $data['status'] = param('status');
    }
}
if (!isset($data['status'])) {
    $data['status']=$xoopsModuleConfig['auth']?STAT_NORMAL:STAT_POST;
}

$extent_sets = array();
if (isset($_POST['extent_sets'])) {
    $init = false;
    $sets = $_POST['extent_sets'];
} else {
    $init = true;
}
if ($eid) {			// already exists extents
    $result = $xoopsDB->query('SELECT rvid FROM '.RVTBL." WHERE eid=$eid AND exid=0", 1);
    if ($xoopsDB->getRowsNum($result)>0) {
	$xoops_extent = '';
    } else {
	$input_extent = '<input type="submit" name="editdate" value="'._MD_EDIT_EXTENT.'"/>';
    }
    $result = $xoopsDB->query('SELECT * FROM '.EXTBL.' WHERE eidref='.$eid.' ORDER BY exdate');
    while ($ext = $xoopsDB->fetchArray($result)) {
	$n = $ext['exid'];
	$ext['date'] = eventdate($ext['exdate']);
	$ext['no'] = $n;
	$ext['disable'] = true;
	$extent_sets[] = $ext;
    }
} else {
    $extents = param('extents',"none");
    $input_extent = select_list('extents', $ev_extents, $extents);
    $step = 86400;		// sec/day
    switch ($extents) {
    case 'weekly':
	$step = $step * 7;
    case 'daily':
	$n=0;
	for ($i=$edate; $i<$expire; $i += $step) {
	    $n++;
	    $v = $init?true:isset($sets[$n]);
	    $extent_sets[] =
		array('exdate'=>$i, 'no'=>$n,
		      'date'=>eventdate($i), 'checked'=>$v);
	    $sets[$n]=$v?$i:0;
	}
	break;
    case 'monthly':
	list($y, $m, $d, $h, $i) = split(' ', formatTimestamp($edate, "Y m j G i"));
	for ($n=0; $n<9999;) {
	    $i = userTimeToServerTime(mktime($h,$i, 0, $m+$n, $d, $y));
	    $n++;
	    if ($i>$expire) break;
	    $v = $init?true:isset($sets[$n]);
	    $extent_sets[] =
		array('exdate'=>$i, 'no'=>$n,
		      'date'=>eventdate($i), 'checked'=>$v);
	    $sets[$n]=$v?$i:0;
	}
    default:
	break;
    }
}

$now = time();

if ($op=='save' || $op=='date') {
    // database field names
    $fields = array('title', 'edate', 'ldate', 'expire', 'summary',
		    'body', 'style', 'status', 'topicid');
    if ($eid) {
	$cond = $adm?"":" AND uid=$uid"; // condition update by poster
	$result = $xoopsDB->query('SELECT status,edate FROM '.EGTBL." WHERE eid=$eid");
	list($prev,$pdate) = $xoopsDB->fetchRow($result);
	$buf = "mdate=$now";
	foreach ($fields as $name) {
	    $buf .= ", $name=".$xoopsDB->quoteString($data[$name]);
	}
	$xoopsDB->query('UPDATE '.EGTBL.' SET '.$buf." WHERE eid=$eid $cond");
	$delta = $edate - $pdate;
	if ($delta) {
	    $xoopsDB->query('UPDATE '.EXTBL." SET exdate=exdate+'$delta' WHERE eidref=$eid AND exdate>$now");
	    $xoopsDB->query('DELETE '.EXTBL." WHERE eidref=$eid AND exdate>$expire");
	}
    } else {
	$prev = STAT_POST;
	$flist = "uid, cdate, mdate";
	$buf = "$uid, $now, $now";
	foreach ($fields as $name) {
	    $flist .= ", $name";
	    $buf .= ', '.$xoopsDB->quoteString($data[$name]);
	}
	$xoopsDB->query('INSERT INTO '.EGTBL."($flist) VALUES($buf)");
	$data['eid'] = $eid = $xoopsDB->getInsertId();
	foreach ($sets as $v) {
	    $xoopsDB->query('INSERT INTO '.EXTBL."(eidref, exdate) VALUES($eid, $v)");
	}
	event_notify('new', $data);
    }
    if (empty($eid)) {
	echo "<div class='error'>Internal Error: eguide/admin.php</div>\n";
	exit();
    }
    if ($prev!=$data['status']) user_notify($eid);
    $result = $xoopsDB->query("SELECT eid FROM ".OPTBL." WHERE eid=$eid");
    
    $ofields = array('reservation', 'strict', 'autoaccept', 'notify',
		     'persons', 'optfield');
    if ($xoopsDB->getRowsNum($result)) {
	$buf = "";
	foreach ($ofields as $name) {
	    $buf .= (empty($buf)?'':', ').$name.'='.$xoopsDB->quoteString($data[$name]);
	}
	$xoopsDB->query('UPDATE '.OPTBL." SET $buf WHERE eid=$eid");
    } else {
	$flist = 'eid, '.join(', ', $ofields);
	$buf = $eid;
	foreach ($ofields as $name) {
	    $buf .= ', '.$xoopsDB->quoteString($data[$name]);
	}
	$xoopsDB->query("INSERT INTO ".OPTBL."($flist) VALUES($buf)");
    }
    if ($op == 'date') {
	header('Location: '.XOOPS_URL.'/modules/eguide/editdate.php?eid='.$eid);
    } else {
	redirect_header("event.php?eid=$eid",2,_MD_DBUPDATED);
    }
    exit;
} elseif ($op=='confirm') {
    if ($adm) {			// delete by admin
	$result = $xoopsDB->query('DELETE FROM '.EGTBL." WHERE eid=$eid");
	$result = $xoopsDB->query('DELETE FROM '.OPTBL." WHERE eid=$eid");
	$result = $xoopsDB->query('DELETE FROM '.RVTBL." WHERE eid=$eid");
	$result = $xoopsDB->query('DELETE FROM '.EXTBL." WHERE eidref=$eid");
    } else {			// delete by poster
	$result = $xoopsDB->query('UPDATE '.EGTBL.' SET status='.STAT_DELETED." WHERE eid=$eid AND uid=$uid");
    }
    redirect_header("index.php",2,_MD_DBDELETED);
    exit();
}


include(XOOPS_ROOT_PATH."/header.php");

$xoopsTpl->assign(assign_const());

if ($eid && $op=='delete') {
    $xoopsOption['template_main'] = 'eguide_event.html';
    edit_eventdata($data);
    unset($data['eid']);	// disable control link
    $xoopsTpl->assign('event', $data);
    $xoopsTpl->assign('message', "<div><form action='admin.php' method='post'>
<input type='hidden' name='op' value='confirm' />
<input type='hidden' name='eid' value='$eid' />
<input type='submit' value='"._DELETE."' />
</form><b>"._MD_EVENT_DEL_DESC."</b></div>\n".
(($adm)?"<div class='evnote'>"._MD_EVENT_DEL_ADMIN."</div>\n":''));
} else {
    if (empty($data['expire'])) {
	$input_expire = select_list('expire', $expire_set, '+86400');
    } else {
	$input_expire = datefield('expire', $data['expire']);
    }

    $cats = get_category();
    if (count($cats) > 1) {
	$input_category = select_list('topicid', $cats, $data['topicid']);
    } else {
	$input_category = '';
    }

    if ($op == 'preview') {
	$views = array('edate', 'cdate', 'ldate', 'title', 'summary', 'body',
		       'persons', 'reserved',
		       'style', 'uid', 'counter', 'catid', 'catimg', 'catname');
	$event = array();
	if (empty($data['cdate'])) {
	    $data['cdate'] = $now;
	    $data['reserved'] = 0;
	    $data['counter'] = 0;
	    $data['uid'] = $uid;
	    $data['catid'] = 1;
	}
	$result = $xoopsDB->query('SELECT catname, catimg FROM '.CATBL.' WHERE catid='.$data['topicid']);
	list($data['catname'], $data['catimg']) = $xoopsDB->fetchRow($result);
	foreach ($views as $name) {
	    $event[$name] = $data[$name];
	}
	edit_eventdata($event);
	$xoopsTpl->assign('form', eventform($data));
	$xoopsTpl->assign('event',$event);
    } else {
	$xoopsTpl->assign('event','');
    }

    $input_status = $adm?select_list('status', $ev_stats, $data['status']):'';

    $xoopsTpl->assign($data);
    $xoopsTpl->assign(admin_const());

    class myFormDhtmlTextArea extends XoopsFormDhtmlTextArea
    {
	function _renderSmileys() {} // only disable smileys
    }
    $summary = isset($data['summary'])?$data['summary']:'';
    $textarea = new myFormDhtmlTextArea('', 'summary', $summary, 10, 60);
    $xoopsTpl->assign(array('input_edate'=>datefield('edate',$data['edate']),
			    'input_expire'=>$input_expire,
			    'input_category'=>$input_category,
			    'input_extent'=>$input_extent,
			    'input_status'=>$input_status,
			    'extent_sets'=>$extent_sets,
			    'summary_textarea'=>$textarea->render(),
			    'input_style'=>select_list('style', $edit_style, $data['style']),
			    ));
}

include(XOOPS_ROOT_PATH."/footer.php");

// make to unix time from separate fields.
function getDateField($p) {
    global $_POST;
    if (empty($_POST["${p}year"])) return 0;
    return userTimeToServerTime(mktime($_POST["${p}hour"],$_POST["${p}min"], 0,
		  $_POST["${p}month"], $_POST["${p}day"], $_POST["${p}year"]));
}

function datefield($prefix, $time) {
    list($y, $m, $d, $h, $i) = split(' ', formatTimestamp($time, "Y m j G i"));
    $buf = select_value(_MD_YEARC, "${prefix}year", $y-10, $y+10, $y);
    $buf .= "&nbsp;\n";
    $buf .= select_value(_MD_MONTHC, "${prefix}month", 1, 12, $m);
    $buf .= "&nbsp;\n";
    $buf .= select_value(_MD_DAYC, "${prefix}day", 1, 31, $d);
    $buf .= "&nbsp;\n";

    $buf .= "&nbsp;"._MD_TIMEC;
    $buf .= select_value("%02d", "${prefix}hour", 0, 23, $h);
    $buf .= " : ";

    $buf .= select_value("%02d", "${prefix}min", 0, 59, $i, 5);
    $buf .= " : 00<br />\n";
    return $buf;
}

function select_value($fmt, $name, $from, $to, $def=0, $step=1) {
    $buf = "<select name='$name'>\n";
    for ($i = $from; $i<=$to; $i+=$step) {
	$buf .= "<option value='$i'".($i==$def?" selected":"").">".sprintf($fmt, $i)."</option>\n";
    }
    $buf .= "</select>\n";
    return $buf;
}

function select_list($name, $options, $def=1) {
    $buf = "<select name='$name'>\n";
    foreach ($options as $i => $v) {
	$buf .= "<option value='$i'".($i==$def?" selected":"").">$v</option>\n";
    }
    $buf .= "</select></p>\n";
    return $buf;
}

function admin_const() {
    return array('lang_title'=>_MD_TITLE,
		 'lang_event_date'=>_MD_EVENT_DATE,
		 'lang_event_expire'=>_MD_EVENT_EXPIRE,
		 'lang_event_category'=>_MD_EVENT_CATEGORY,
		 'lang_event_extent'=>_MD_EVENT_EXTENT,
		 'lang_introtext'=>_MD_INTROTEXT,
		 'lang_extext'=>_MD_EXTEXT,
		 'lang_event_style'=>_MD_EVENT_STYLE,
		 'lang_reserv_setting'=>_MD_RESERV_SETTING,
		 'lang_reserv_desc'=>_MD_RESERV_DESC,
		 'lang_reserv_stopfull'=>_MD_RESERV_STOPFULL,
		 'lang_reserv_auto'=>_MD_RESERV_AUTO,
		 'lang_reserv_notifyposter'=>_MD_RESERV_NOTIFYPOSTER,
		 'lang_reserv_persons'=>_MD_RESERV_PERSONS,
		 'lang_reserv_unit'=>_MD_RESERV_UNIT,
		 'lang_reserv_item'=>_MD_RESERV_ITEM,
		 'lang_reserv_item_desc'=>_MD_RESERV_ITEM_DESC,
		 'lang_preview'=>_MD_PREVIEW,
		 'lang_save'=>_MD_SAVE);
}
?>