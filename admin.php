<?php
// Event Administration by Poster
// $Id: admin.php,v 1.18 2006/06/04 07:04:02 nobu Exp $

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
$targs = array('title', 'summary', 'body', 'optfield', 'before');

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
		  'persons'	=> $xoopsModuleConfig['default_persons'], 
		  // how many persons/sheet in room
		  'optfield'	=>
		  $xoopsModuleConfig['member_only']?
				  _MD_RESERV_DEFAULT_MEMBER:_MD_RESERV_DEFAULT_ITEM,
		  'title'	=> '', // contents
		  'summary'	=> '',
		  'body'	=> '',
		  'counter'	=> 0,
		  'reserved'	=> 0,
		  'edate'	=> time()+3600*24, // now + a day
		  'event'	=> '',
		  'expire'	=> $xoopsModuleConfig['expire_after']*60,
		  'closetime'      => $xoopsModuleConfig['close_before']*60,
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
	    $expire = empty($_POST['expire'])?time_to_sec($_POST['expire_text']):intval($_POST['expire']);
	} else {
	    $expire = getDateField('expire');
	}
	$data['ldate'] = $data['edate'] = $edate;
	$data['expire'] = $expire;
	$data['topicid'] = param('topicid', 1);
	foreach ($iargs as $name) {
	    $data[$name] = param($name);
	}
	foreach ($targs as $name) {
	    $data[$name] = param($name, "");
	}
	$data['ldate'] = 0;
	$data['closetime'] = time_to_sec($data['before']);
	if ($adm) $data['status'] = param('status');
    }
}
if (!isset($data['status'])) {
    $data['status']=$xoopsModuleConfig['auth']?STAT_POST:STAT_NORMAL;
}

$extent_sets = array();
if (isset($_POST['extent_sets'])) {
    $init = false;
    $sets = $_POST['extent_sets'];
} else {
    $init = true;
}
$input_extent = '';
if ($eid) {			// already exists extents
    $result = $xoopsDB->query('SELECT rvid FROM '.RVTBL." WHERE eid=$eid AND exid=0", 1);
    if ($xoopsDB->getRowsNum($result)==0) {
	$save = _MD_EDIT_EXTENT."("._MD_SAVE.")";
	$input_extent = "<input type='submit' name='editdate' value='$save'/> 
&nbsp; <a href='editdate.php?eid=$eid'>"._MD_EDIT_EXTENT."</a>";
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
    $repeat = param('repeat',1);
    $input_extent = select_list('extents', $ev_extents, $extents);
    $input_extent .= ' &nbsp; '._MD_EXTENT_REPEAT." <input size='2' value='$repeat' name='repeat'/>";
    $step = 86400;		// sec/day
    switch ($extents) {
    case 'weekly':
	$step = $step * 7;
    case 'daily':
	for ($n=0, $i=$edate; $n<$repeat; $n++, $i += $step) {
	    $v = $init?true:isset($sets[$n]);
	    $extent_sets[] =
		array('exdate'=>$i, 'no'=>$n,
		      'date'=>eventdate($i), 'checked'=>$v);
	    $sets[$n]=$v?$i:0;
	}
	break;
    case 'monthly':
	list($y, $m, $d, $h, $i) = split(' ', formatTimestamp($edate, "Y m j G i"));
	for ($n=0; $n<$repeat; $n++) {
	    $i = userTimeToServerTime(mktime($h,$i, 0, $m+$n, $d, $y));
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
	    if ($v) $xoopsDB->query('INSERT INTO '.EXTBL."(eidref, exdate) VALUES($eid, $v)");
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
		     'persons', 'optfield', 'closetime');
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
	header('Location: '.EGUIDE_URL.'/editdate.php?eid='.$eid);
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

$xoopsTpl->assign('xoops_module_header', HEADER_CSS);

// DHTML calendar
include_once XOOPS_ROOT_PATH.'/language/'.$xoopsConfig['language'].'/calendar.php';
if (!empty($ev_week)) $xoopsTpl->assign('weekname', $ev_week);
if (!empty($ev_month)) $xoopsTpl->assign('monthname', $ev_month);
$xoopsTpl->assign('calrange', array(2000,2015));

$check = array('title'=>_MD_TITLE, 'summary'=>_MD_INTROTEXT);
foreach ($check as $k=>$v) {
    $check[$k] = sprintf(_FORM_ENTER, $v);
}
$xoopsTpl->assign('check', $check);

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
    $expire = $data['expire']>$data['edate']?$data['expire']-$data['edate']:$data['expire'];
    $str = isset($expire_set["+$expire"])?"":htmlspecialchars(time_to_str($expire));
    $input_expire = "<input name='expire_text' size='8' value='$str' onchange='document.evform.expire.selectedIndex=0' /> ".
	select_list('expire', $expire_set, $expire);

    $cats = get_category();
    if (count($cats) > 1) {
	$input_category = select_list('topicid', $cats, $data['topicid']);
    } else {
	$input_category = '';
    }

    if ($op == 'preview') {
	$views = array('edate', 'cdate', 'ldate', 'title', 'summary', 'body',
		       'persons', 'reserved', 'closetime',
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
	$form = eventform($data);
	$form['submit_opts'] = 'disabled';
	$xoopsTpl->assign('form', $form);
	$xoopsTpl->assign('event',$event);
    } else {
	$xoopsTpl->assign('event','');
    }

    $input_status = $adm?select_list('status', $ev_stats, $data['status']):'';
    if (empty($data['before'])) {
	$data['before']=time_to_str($data['closetime']);
    }
    edit_eventdata($data);
    $xoopsTpl->assign($data);

    class myFormDhtmlTextArea extends XoopsFormDhtmlTextArea
    {
	function _renderSmileys() {} // only disable smileys
    }
    $summary = isset($data['summary'])?$data['summary']:'';
    $textarea = new myFormDhtmlTextArea('', 'summary', $summary, 10, 60);
    $nlab = $xoopsModuleConfig['label_persons'];
    if ($nlab) $nlab = sprintf(_MD_RESERV_LABEL_DESC, $nlab);
    $xoopsTpl->assign(array('input_edate'=>datefield('edate',$data['edate']),
			    'input_expire'=>$input_expire,
			    'input_category'=>$input_category,
			    'input_extent'=>$input_extent,
			    'input_status'=>$input_status,
			    'extent_sets'=>$extent_sets,
			    'label_desc'=>$nlab,
			    'summary_textarea'=>$textarea->render(),
			    'input_style'=>select_list('style', $edit_style, $data['style']),
			    ));
}

include(XOOPS_ROOT_PATH."/footer.php");

// make to unix time from separate fields.
function getDateField($p) {
    global $xoopsUser;
    if (empty($_POST["${p}ymd"])) return 0;
    list($y, $m, $d) = split('-', $_POST["${p}ymd"]);
    return userTimeToServerTime(mktime($_POST["${p}hour"],$_POST["${p}min"], 0,
				       $m, $d, $y), $xoopsUser->getVar("timezone_offset"));
}

function datefield($prefix, $time) {
    list($h, $i) = split(' ', formatTimestamp($time, "G i"));
    $buf = "<input id='${prefix}ymd' name='${prefix}ymd' size='12' value='".formatTimestamp($time, "Y-m-d")."'/> ";
    $buf .= "<script language='javascript'><!-- 
document.write('<input type=\"button\" value=\""._MD_CAL."\" onClick=\"showCalendar(\\'${prefix}ymd\\')\">');
--></script>\n";

    $buf .= "&nbsp;"._MD_TIMEC." ";
    $buf .= select_value("%02d", "${prefix}hour", 0, 23, $h);
    $buf .= " : ";

    $buf .= select_value("%02d", "${prefix}min", 0, 59, $i, 5);
    $buf .= " : 00<br />\n";
    return $buf;
}

function select_value($fmt, $name, $from, $to, $def=0, $step=1) {
    $buf = "<select name='$name' id='$name'>\n";
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
    $buf .= "</select>\n";
    return $buf;
}

function time_to_str($sec) {
    $unit = split(',',_MD_TIME_UNIT);
    if (($sec >= 86400) && ($sec % 86400) == 0) { // days
	return ($sec / 86400).$unit[0];
    } elseif (($sec >= 3600) && ($sec % 3600 == 0)) { // hours
	return ($sec / 3600).$unit[1];
    }
    return intval($sec / 60).$unit[2];
}

function time_to_sec($str) {
    $unit = split(',',_MD_TIME_REG);
    $v = intval($str);
    if (preg_match('/^\d+'.$unit[0].'$/i', $str)) {
	return $v * 86400;
    } elseif (preg_match('/^\d+'.$unit[1].'?$/i', $str)) {
	return $v * 3600;
    }
    return $v * 60;
}
?>