<?php
// Event Guide common functions
// $Id: functions.php,v 1.5 2005/12/27 05:13:53 nobu Exp $

// exploding addional informations.
function explodeinfo($info, $item) {
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
	    if ($xoopsConfig['debug']) {
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
    global $xoopsModuleConfig;

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
    $data['postdate'] = formatTimestamp($data['cdate'], _MD_TIME_FMT);
    $data['uname'] = XoopsUser::getUnameFromId($data['uid']);
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
}

function eventform($data) {
    global $xoopsUser, $xoopsModuleConfig;
    $myts =& MyTextSanitizer::getInstance();

    if (empty($data['reservation'])) return null;

    $form = array();
    // reservation form
    $form['email'] = $myts->makeTboxData4Edit(is_object($xoopsUser)?$xoopsUser->email():"");
    $form['member_only'] = $xoopsModuleConfig['member_only'];
    $form['user_notify'] = $xoopsModuleConfig['user_notify'];
    $items = array();
    $field = 0;
    $note1 = $note2 = "";
    foreach (explode("\n", $data['optfield']) as $n) {
	$field++;
	$n = preg_replace("/\s*[\n\r]/", "", $n);
	if ($n=="") continue;
	$attr = "";
	if (preg_match('/^\s*#/', $n)) {
	    $opts = preg_replace('/^\s*#\s*/', "", $n);
	    $type = "#";
	    $name = "&nbsp;";
	} else {
	    $opt = explode(",", $n);
	    $name = array_shift($opt);
	    if (preg_match('/\*$/', $name)) {
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
		$v = htmlspecialchars($xoopsUser->name());
	    }
	    $type = "text";
	    $aname = isset($opt[0])?strtolower($opt[0]):"";
	    switch ($aname) {
		case "text":
		case "checkbox":
		case "radio":
		case "textarea":
		case "select":
		    $type = $aname;
		    array_shift($opt);
	    }
	    $size = 60;
	    $cols = 40;
	    $rows = 5;
	    $opts = "";
	    $comment = "";
	    $fname = "opt$field";
	    $sub = 0;
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
		    $ck = ($an != $aname)?" checked":"";
		    if ($type=='radio') {
			$sub++;
			if (isset($args[1])) {
			    $opts .= "<input type='$type' name='$fname' value='$an'$ck />".$args[1]." &nbsp; ";
			} else {
			    $opts .= "<input type='$type' name='$fname' value='$an'$ck />$an &nbsp; ";
			}
		    } elseif ($type=='checkbox') {
			$sub++;
			if (isset($args[1])) {
			    $opts .= "<input type='$type' name='${fname}_$sub' value='$an'$ck />".$args[1]." &nbsp; ";
			} else {
			    $opts .= "<input type='$type' name='${fname}_$sub' value='$an'$ck />$an &nbsp; ";
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
	if ($attr=='evop') $name = "[$name]";
	if ($attr=='') $attr = (count($items)%2)?'even':'odd';
	$items[] = array('attr'=>$attr, 'label'=>$name, 'value'=>$opts, 'comment'=>$comment);
    }
    $form['items'] = $items;
    $form['lang_note'] = $note1;
    $form['note'] = $note2;
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
    $result=$xoopsDB->query('SELECT exid,exdate,x.reserved FROM '.EXTBL.' x LEFT JOIN '.OPTBL." o ON eidref=eid WHERE eidref=$eid".($all?"":" AND exdate-closetime>".time()).' ORDER BY exdate');
    echo $xoopsDB->error();
    $extents = array();
    while ($extent = $xoopsDB->fetchArray($result)) {
	$extent['date'] = eventdate($extent['exdate']);
	$extents[] = $extent;
    }
    return $extents;
}

function eventdate($time) {
    global $ev_week;
    $str = formatTimestamp($time, _MD_DATE_FMT);
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
?>