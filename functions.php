<?php
// Event Guide common functions
// $Id: functions.php,v 1.11 2006/06/04 07:04:02 nobu Exp $

// exploding addional informations.
function explodeopts($opts) {
    $myitem = array();
    foreach (explode("\n",preg_replace('/\r/','',$opts)) as $ln) {
	// comment line
	if (preg_match('/^\s*#/', $ln)||preg_match('/^\s*$/', $ln)) continue;
	$fld = preg_split("/,\s*/", $ln);
	$lab = preg_replace('/^!\s*/', '', preg_replace('/[\*#]$/', "", array_shift($fld)));
	$myitem[] =  $lab;
    }
    return $myitem;
}

function explodeinfo($info, $item) {
    if (!is_array($item)) $item = explodeopts($item);
    $ln = explode("\n", preg_replace('/\r/','',$info));
    $n = 0;
    $result = array();
    while ($a = array_shift($ln)) {
	$lab = $item[$n];
	if (preg_match("/^".str_replace("/", '\/', quotemeta($lab)).": (.*)$/", $a, $m)) {
	    $v = isset($m[1])?$m[1]:"";
	    if ($m[1] == "\\") {
		$v = "";
		$x = "/^".(isset($item[$n+1])?quotemeta($item[$n+1]):"\n").": /";
		while (($a=array_shift($ln))&&!preg_match($x, $a)) {
		    $v .= "$a\n";
		}
		array_unshift($ln, $a);
	    }
	    $result[$lab] = "$v";
	} else {
	    global $xoopsConfig;
	    if (isset($xoopsConfig['debug']) && $xoopsConfig['debug']) {
		echo "<span class='error'>".$item[$n].",$a</span>";
	    }
	    break;
	}
	$n++;
    }
    return $result;
}

function xss_filter($text) {
    return preg_replace('/<script[^>]*>.*<\/script[^>]*>/si', '', $text);
}

function edit_eventdata(&$data) {
    global $xoopsModuleConfig, $xoopsUser;

    $myts =& MyTextSanitizer::getInstance();
    $str = $pat = array();
    $pat[] = '{X_DATE}';
    $str[] = $data['ldate'] =
	empty($data['exdate'])?$data['edate']:$data['exdate'];
    $data['closedate']=$data['ldate']-$data['closetime'];
    $data['dispclose'] = formatTimestamp($data['closedate'], _MD_TIME_FMT);
    $data['date'] = eventdate($data['ldate']);
    $pat[] = '{X_TIME}';
    $str[] = $data['time'] = formatTimestamp($data['ldate'], _MD_STIME_FMT);
    $post = isset($data['cdate'])?$data['cdate']:time();
    $data['postdate'] = formatTimestamp($post, _MD_TIME_FMT);
    $data['uname'] = isset($data['uid'])?XoopsUser::getUnameFromId($data['uid']):$xoopsUser->getVar('uname');
    $data['hits'] = sprintf(_MD_REFER, $data['counter']);
    $br = 0;
    $html = 1;
    switch ($data['style']) {
    case 2: $html = 0;
    case 1: $br = 1;
    }
    $data['disp_summary'] = str_replace($pat,$str,xss_filter($myts->displayTarea($data['summary'],$html,0,1,1,$br)));
    $data['disp_body'] = str_replace($pat,$str,xss_filter($myts->displayTarea($data['body'],$html,0,1,1,$br)));
    $data['title'] = $myts->htmlSpecialChars($data['title']);
    if (!empty($data['persons'])) {
	$data['reserv_num']=sprintf(_MD_RESERV_NUM, $data['persons']);
	$data['reserv_reg']=sprintf(_MD_RESERV_REG, $data['reserved']);
    }
    // fill of seat
    if ($data['persons']) {
	
	$marker = preg_split('/,|[\r\n]+/',$xoopsModuleConfig['maker_set']);
	$fill=$data['fill']=intval($data['reserved']/$data['persons']*100);
	if ($data['closedate']<time()) $fill = -1;
	while(list($k,$v) = array_splice($marker, 0, 2)) {
	    if ($fill<$k) {
		$data['fill_mark'] = $v;
		break;
	    }
	}
    }
    return $data;
}

function eventform($data) {
    global $xoopsUser, $xoopsModuleConfig;
    $myts =& MyTextSanitizer::getInstance();

    if (empty($data['reservation'])) return null;

    $form = array();
    $optfield = $data['optfield'];
    // reservation form
    if (isset($_POST['email'])) {
	$email = $myts->stripSlashesGPC($_POST['email']);
    } else $email = is_object($xoopsUser)?$xoopsUser->email():"";
    $form['email'] = $myts->makeTboxData4Edit($email);
    $form['user_notify'] = $xoopsModuleConfig['user_notify'];
    $form['check'] = array();
    $items = array();
    $field = 0;
    $note1 = $note2 = "";
    foreach (explode("\n", $optfield) as $n) {
	$field++;
	$n = preg_replace("/\s*[\n\r]/", "", $n);
	if ($n=="") continue;
	$attr = "";
	$require = false;
	if (preg_match('/^\s*#/', $n)) {
	    $opts = preg_replace('/^\s*#\s*/', "", $n);
	    $type = "#";
	    $name = "&nbsp;";
	} else {
	    $opt = preg_split("/,\\s*/", $n);
	    $name = array_shift($opt);
	    if (preg_match('/[\*#]$/', $name)) {
		$require = true;
		$attr = 'evms';
		$note1 = _MD_ORDER_NOTE1;
	    }
	    if (preg_match('/^!/', $name)) {
		$name = preg_replace('/^!/', '', $name);
		$attr = 'evop';
		$note2 = _MD_ORDER_NOTE2;
	    }
	    $v = "";
	    if ($xoopsUser && preg_match(_MD_NAME, $name)) {
		$v = htmlspecialchars($xoopsUser->getVar('name'));
	    }
	    $type = "text";
	    $aname = isset($opt[0])?strtolower($opt[0]):"";
	    switch ($aname) {
		case "hidden":
		case "text":
		case "checkbox":
		case "radio":
		case "textarea":
		case "select":
		    $type = $aname;
		    array_shift($opt);
	    }
	    if ($type=='hidden') continue;
	    $size = 60;
	    $cols = 40;
	    $rows = 5;
	    $opts = "";
	    $comment = "";
	    $fname = "opt$field";
	    $sub = 0;
	    if (isset($_POST[$fname])) {
		$v = $myts->stripSlashesGPC($_POST[$fname]);
	    }
	    foreach ($opt as $op) {
		if (preg_match("/^#/",$op)) {
		    $comment .= preg_replace("/^#/","",$op);
		    continue;
		}
		$args = explode("=", $op, 2);
		// XXX: strtolower PHP4 mbstring bug escape.
		$aname=isset($args[1])?strtolower($args[0]):$args[0];
		switch ($aname) {
		case "size":
		    $size = $args[1];
		    break;
		case "rows":
		    $rows = $args[1];
		    break;
		case "cols":
		    $cols = $args[1];
		    break;
		default:
		    $an = preg_replace('/\+$/', "", $aname);
		    if ($v) {
			$ck = ($an == $v)?" checked":"";
		    } else {
			$ck = ($an != $aname)?" checked":"";
		    }
		    if ($type=='radio') {
			$sub++;
			if (isset($args[1])) {
			    $opts .= "<input type='$type' name='$fname' value='$an'$ck />".$args[1]." &nbsp; ";
			} else {
			    $opts .= "<input type='$type' name='$fname' value='$an'$ck />$an &nbsp; ";
			}
		    } elseif (($type=='text' || $type=='textarea')) {
			if (!isset($_POST[$fname])) {
			    $v .= ($v==""?"":",").str_replace('\n', "\n", $op);
			}
		    } elseif ($type=='checkbox') {
			$sub++;
			$iname = $fname.'_'.$sub;
			if (isset($_POST[$iname])) {
			    $v = $myts->stripSlashesGPC($_POST[$iname]);
			    $ck = ($an==$v)?' checked':'';
			}
			if (isset($args[1])) {
			    $opts .= "<input type='$type' name='$iname' value='$an'$ck/>".$args[1]." &nbsp; ";
			} else {
			    $opts .= "<input type='$type' name='$iname' value='$an'$ck/>$an &nbsp; ";
			}
		    } elseif ($type=='select') {
			if ($ck != "") $ck = " selected";
			if (isset($args[1])) {
			    $opts .= "<option value='$an'$ck>".$args[1]."</option>\n";
			} else {
			    $opts .= "<option$ck>$an</option>\n";
			}
		    }
		}
	    }
	    if ($type == "text") {
		$opts .= "<input size='$size' name='$fname' value='$v' />";
	    } elseif ($type == "textarea") {
		$opts .= "<textarea name='$fname' rows='$rows' cols='$cols' wrap='virtual'>$v</textarea>";
	    } elseif ($type == "select") {
		$opts = "<select name='$fname'>\n$opts</select>";
	    }
	}
	if ($require) $form['check'][$fname] = preg_replace('/\\*$/', '', $name);
	$name = preg_replace('/\\*$/', _MD_REQUIRE_MARK, $name);
	if ($attr=='evop') $name = sprintf(_MD_LISTITEM_FMT, $name);
	if ($attr=='') $attr = (count($items)%2)?'even':'odd';
	$items[] = array('attr'=>$attr, 'label'=>$name,
			 'value'=>$opts, 'comment'=>$comment);
    }
    $mo = $xoopsModuleConfig['member_only'];
    $form['member_only'] = $mo;
    $form['op'] = ($xoopsModuleConfig['has_confirm']&&
		   (count($items)||!$mo))?'confirm':'order';
    $form['items'] = $items;
    $form['note'] = $note1.(!empty($note1)&&!empty($note2)?" ":"").$note2;
    $form['eid'] = empty($data['eid'])?0:$data['eid'];
    return $form;
}

// remove slashes
if (XOOPS_USE_MULTIBYTES && function_exists("mb_convert_encoding") &&
    $xoopsConfig['language'] == 'japanese') {
    if (get_magic_quotes_gpc()) {
	function post_filter($s) {
	    return mb_convert_encoding(stripslashes($s), _CHARSET, "EUC-JP,UTF-8,Shift_JIS,JIS");
	}
    } else {
	function post_filter($s) {
	    return mb_convert_encoding($s, _CHARSET, "EUC-JP,UTF-8,Shift_JIS,JIS");
	}
    }
} else {
    if (get_magic_quotes_gpc()) {
	function post_filter($s) {
	    return stripslashes($s);
	}
    } else {
	function post_filter($s) {
	    return $s;
	}
    }
}

// take HTTP paramater with normalize filter
function param($name, $def=0) {
    if (isset($_POST[$name])) $val = $_POST[$name];
    elseif (isset($_GET[$name])) $val = $_GET[$name];
    else return $def;
    return is_numeric($def)?intval($val):post_filter($val);
}

// set ldate to next event date if exists extent entry.
function set_next_event() {
    global $xoopsDB;
    $now = time();
    // Search already passed event that exist next extent.
    $res = $xoopsDB->query("SELECT eid, min(exdate),edate FROM ".EGTBL." LEFT JOIN ".EXTBL." ON eid=eidref AND exdate>$now WHERE (edate<$now OR ldate=0) AND ldate<$now AND expire>$now GROUP BY eid");
    while (list($eid, $exdate, $edate) = $xoopsDB->fetchRow($res)) {
	if (empty($exdate)) $exdate = $edate;
	$xoopsDB->queryF("UPDATE ".EGTBL." SET ldate=$exdate WHERE eid=$eid");
    }
}

function get_extents($eid, $all=false) {
    global $xoopsDB;
    $result=$xoopsDB->query('SELECT exid,exdate,expersons,x.reserved,if(expersons IS NULL,persons,expersons) persons FROM '.EXTBL.' x LEFT JOIN '.OPTBL." o ON eidref=eid WHERE eidref=$eid".($all?"":" AND exdate-closetime>".time()).' ORDER BY exdate');
    $extents = array();
    while ($extent = $xoopsDB->fetchArray($result)) {
	$extent['date'] = eventdate($extent['exdate']);
	$extents[] = $extent;
    }
    return $extents;
}

function eventdate($time) {
    global $ev_week, $xoopsModuleConfig;
    $str = formatTimestamp($time, $xoopsModuleConfig['date_format']);
    if (isset($ev_week)) {
	$str = str_replace(array_keys($ev_week), $ev_week, $str);
    }
    return $str;
}

function get_category() {
    global $xoopsDB;
    $result = $xoopsDB->query("SELECT catid, catname FROM ".CATBL." ORDER BY catid");
    $list = array();
    while (list($id, $name)=$xoopsDB->fetchRow($result)) {
	$list[$id] = $name;
    }
    return $list;
}

// fetch event data set
function fetch_event($eid, $exid, $admin=false) {
    global $xoopsDB;
    $stc=$admin?"":"AND status=".STAT_NORMAL;
    $fields = "e.eid, cdate, title, summary, body, optfield,
IF(expersons IS NULL, persons, expersons) persons,
IF(exdate,exdate,edate) edate, IF(x.reserved,x.reserved,o.reserved) reserved, 
closetime, reservation, uid, status, style, counter, catid, catname, catimg, 
exid, exdate, strict, autoaccept, notify";
    $result = $xoopsDB->query("SELECT $fields FROM ".EGTBL.' e LEFT JOIN '.OPTBL.
' o ON e.eid=o.eid LEFT JOIN '.CATBL.' ON topicid=catid LEFT JOIN '.EXTBL.
" x ON e.eid=eidref WHERE e.eid=$eid $stc".($exid?' AND exid='.$exid:''));
    return $xoopsDB->fetchArray($result);
}

function template_dir($file='') {
    global $xoopsConfig;
    $lang = $xoopsConfig['language'];
    $dir = dirname(__FILE__).'/language/%s/mail_template/%s';
    $path = sprintf($dir,$lang, $file);
    if (file_exists($path)) {
	$path = sprintf($dir,$lang, '');
    } else {
	$path = sprintf($dir,'english', '');
    }
    return $path;
}

function order_notify($data, $email, $value) {
    global $xoopsModuleConfig, $xoopsUser, $xoopsModule;

    $poster = new XoopsUser($data['uid']);
    $eid = $data['eid'];
    $exid = $data['exid'];
    $url = XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/event.php?eid='.$eid.($exid?"&sub=$exid":'');

    $xoopsMailer =& getMailer();
    $xoopsMailer->useMail();
    $tplfile = $data['autoaccept']?"accept.tpl":"order.tpl";
    $xoopsMailer->setTemplateDir(template_dir($tplfile));
    $xoopsMailer->setTemplate($tplfile);
    $xoopsMailer->assign("EVENT_URL", $url);
    if ($xoopsModuleConfig['member_only']) {
	$uinfo = sprintf("%s: %s (%s)\n", _MD_UNAME,
			 $xoopsUser->getVar('uname'),
			 $xoopsUser->getVar('name'));
    } else {
	$uinfo = "";
    }
    if ($email) $uinfo .= sprintf("%s: %s\n", _MD_EMAIL, $email);
    $rvid = $data['rvid'];
    $conf = $data['confirm'];
    $xoopsMailer->assign("RVID", $rvid);
    $xoopsMailer->assign("CANCEL_KEY", $conf);
    $xoopsMailer->assign("CANCEL_URL", EGUIDE_URL."/reserv.php?op=cancel&rvid=$rvid&key=$conf");
    $xoopsMailer->assign("INFO", $uinfo.$value);
    $title = eventdate($data['edate'])." ".$data['title'];
    $xoopsMailer->assign("TITLE", $title);
    $xoopsMailer->assign("SUMMARY", strip_tags($data['summary']));
    if (!empty($email)) $xoopsMailer->setToEmails($email);
    if ($data['notify']) {
	if (!in_array($xoopsModuleConfig['notify_group'], $poster->groups())) {
	    $xoopsMailer->setToUsers($poster);
	}
	$member_handler =& xoops_gethandler('member');
	$notify_group = $member_handler->getGroup($xoopsModuleConfig['notify_group']);
	$xoopsMailer->setToGroups($notify_group);
    }
    $xoopsMailer->setSubject(_MD_SUBJECT.' - '.$title);
    $xoopsMailer->setFromEmail($poster->getVar('email'));
    $xoopsMailer->setFromName(_MD_FROM_NAME);
    return $xoopsMailer->send();
}
?>