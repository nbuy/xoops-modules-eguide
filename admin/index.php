<?php
// Event Guide global administration
// $Id: index.php,v 1.13 2004/12/02 16:12:50 nobu Exp $
include("admin_header.php");
include_once(XOOPS_ROOT_PATH."/class/xoopstopic.php");
include_once(XOOPS_ROOT_PATH."/class/module.errorhandler.php");
$inc = XOOPS_ROOT_PATH."/modules/image/class.php";
if (file_exists($inc)) include_once($inc);

$self = $HTTP_SERVER_VARS["SCRIPT_NAME"];
foreach (array("op", "eid", "status", "uid") as $v) {
    if (isset($HTTP_GET_VARS[$v])) $$v = $HTTP_GET_VARS[$v];
    elseif (isset($HTTP_POST_VARS[$v])) $$v = $HTTP_POST_VARS[$v];
}

// show general configuration form
function eventConfig() {
    global $xoopsConfig, $xoopsModule, $eventConfig, $xoopsDB, $self;
    xoops_cp_header();
    //$xoopsModule->printAdminMenu();
    //echo "<br />";
    OpenTable();
    echo "<h4>" ._MI_EGUIDE_CONFIG. "</h4><br>\n";
    echo "<form action='$self' method='post'>\n";
    echo "<table border='0'>\n<tr><td class='nw'>".
	_AM_POST_GROUP."</td><td>
        <select name='group'>\n";
    $type=preg_match("/^XOOPS 1/",XOOPS_VERSION)?"type":"group_type";
    $r=$xoopsDB->query("SELECT groupid,name FROM ".$xoopsDB->prefix("groups").
		       " WHERE $type<>'Anonymous'");
    while (list($i,$v)=$xoopsDB->fetchRow($r)) {
	$ck = ($i==$eventConfig['group'])?" selected":"";
	echo "<option value='$i'$ck>$v</option>\n";
    }
    echo "</select>\n";
    echo "</td></tr>";

    $yn = array(1=>_AM_YES, 0=>_AM_NO);
	
    function myradio($name, $item, $value, $def) {
	echo "<tr><td class='nw'>$item</td><td>";
	foreach ($value as $i => $v) {
	    $ck = ($i == $def)?" checked":"";
	    echo "<input type='radio' name='$name' value='$i' $ck />&nbsp;$v&nbsp;";
	}
	echo "</td></tr>";
    }

    myradio("notify", _AM_NOTIFYSUBMIT, $yn, $eventConfig['notify']);
    myradio("auth", _AM_NEEDPOSTAUTH, $yn, $eventConfig['auth']);
    myradio("user_notify", _AM_USER_NOTIFY, $yn, $eventConfig['user_notify']);
    echo "<tr><td class='nw'>"._AM_MAX_LISTITEM."</td>";
    echo "<td><input size=3 name='max_item' value='".$eventConfig['max_item']."' align='right' /></td></tr>\n";
    echo "<tr><td class='nw'>"._AM_MAX_SHOW."</td>";
    echo "<td><input size=3 name='max_event' value='".$eventConfig['max_event']."' align='right' /></td></tr>\n";
    echo "</table>";
    echo "<input type='hidden' name='op' value='eventConfigS' />";
    echo "<input type='submit' value='"._AM_SAVECHANGE."' />";
    echo "&nbsp;<input type='button' value='"._AM_CANCEL."' onclick='javascript:history.go(-1)' />";
    echo "</form>";
    CloseTable();

}

// save general configuration
function eventConfigS() {
    global $xoopsModule,$self;

    function myvalue($name) {
	global $HTTP_POST_VARS;
	$v = $HTTP_POST_VARS[$name];
	if (!preg_match('/^\d+$/', $v)) {
	    $v = "\"$v\"";
	}
	return "\$eventConfig['$name']=$v;\n";
    }

    $content  = myvalue('group');
    $content .= myvalue('notify');
    $content .= myvalue('auth');
    $content .= myvalue('max_item');
    $content .= myvalue('max_event');
    $content .= myvalue('user_notify');

    putCache($xoopsModule->dirname()."/config.php", $content);

    redirect_header("$self",1,_AM_DBUPDATED);
    exit();
}

$tbl = $xoopsDB->prefix("eguide");
$opt = $xoopsDB->prefix("eguide_opt");
$rsv = $xoopsDB->prefix("eguide_reserv");
function css_tags() {
    return preg_match("/^XOOPS 1/",XOOPS_VERSION)?array("bg1","bg3"):array("even","odd");
}
if (!isset($op)) $op="";
switch($op) {
case "events":
    xoops_cp_header();
    OpenTable();
    echo "<h4>"._MI_EGUIDE_EVENTS."</h4>";
    $result = $xoopsDB->query("SELECT eid,edate,title,uid,status FROM $tbl ORDER BY edate DESC");
    $n = 0;
    echo "<form action='$self' method='post'>\n";
    echo "<table cellspacing='1' cellpadding='3' border='0' class='bg2'>\n";
    echo "<tr class='bg4'><th>"._AM_RESERVATION."</th><th>".
	_AM_EVENT_DAY."</th><th>"._AM_TITLE."</th>";
    echo "<th>"._AM_POSTER."</th><th>"._AM_DISP_STATUS."</th>";
    echo "<th>"._AM_OPERATION."</th></tr>\n";
    $tags = css_tags();
    while ($data = $xoopsDB->fetchArray($result)) {
	$bg = $tags[$n++%2];
	$eid = $data['eid'];
	$date = date(_AM_DATE_FMT, $data['edate']);
	$title = "<a href='../event.php?eid=$eid'>".$data['title']."</a>";
	$poster = new XoopsUser($data['uid']);
	$u = "<a href='".XOOPS_URL."/userinfo.php?uid=".$poster->uid()."'>".$poster->uname()."</a>";
	$s = $data['status'];
	$sn = $ev_stats[$data['status']];
	if ($s == STAT_DELETED) {
	    $sn = "<a href='../admin.php?op=delete&amp;eid=$eid'>$sn</a>";
	} elseif ($s == STAT_POST) {
	    $sn = "<strong>$sn</strong>";
	}
	$ors = $xoopsDB->query("SELECT reservation FROM $opt WHERE eid=$eid");
	if ($xoopsDB->getRowsNum($ors)) {
	    list($resv) = $xoopsDB->fetchRow($ors);
	    $mk = "<input type='hidden' name='rv[$eid]' value='on' />";
	    $mk .= "<input type='checkbox' name='ck[$eid]' ".($resv?" checked":"")." />";
	} else {
	    $mk = "&nbsp;";
	}
	    
	$edit = "<a href='../admin.php?eid=$eid'>"._EDIT."</a>".
	    " <a href='$self?op=edit&amp;eid=$eid'>"._AM_EDIT."</a>".
	    " <a href='../admin.php?op=delete&amp;eid=$eid'>"._DELETE."</a>";
	echo "<tr class='$bg'><td align='center'>$mk</td><td>$date</td><td>$title</td>";
	echo "<td>$u</td><td>$sn</td><td>$edit</td></tr>\n";
    }
    echo "</table>\n";
    echo "<input type='hidden' name='op' value='resvCtrl' />\n";
    echo "<input type='submit' value='"._AM_UPDATE."' />\n";
    echo "</form>\n";
    $log = $xoopsDB->prefix("eguide_log");
    $result = $xoopsDB->query("SELECT count(rvid) FROM $rsv WHERE eid=0");
    if ($result) {
	list($n) = $xoopsDB->fetchRow($result);
	echo "<p><a href='$self?op=notifies'>"._AM_INFO_REQUEST."</a> ".sprintf(_AM_INFO_COUNT, $n)."</p>\n";
    }
    CloseTable();
    break;

case "notifies":
    xoops_cp_header();
    OpenTable();
    echo "<h4>"._AM_INFO_REQUEST."</h4>";
    $cond = "eid=0";
    if (isset($HTTP_GET_VARS['q'])) {
	$q = $HTTP_GET_VARS['q'];
	$cond .= " AND email like '%$q%'";
    }
    $result = $xoopsDB->query("SELECT * FROM $rsv WHERE $cond ORDER BY rdate");
    $n = 0;
    $nc = $xoopsDB->getRowsNum($result);
    echo "<form action='$self' method='get'>\n".
	_AM_INFO_SEARCH."<input name='q' />".
	" <input type='hidden' name='op' value='notifies' />\n".
	" <input type='submit' value='"._SUBMIT."' />\n".
	"</form>\n";
    echo sprintf(_AM_INFO_COUNT, $nc);
    if ($nc) {
	echo "<form action='$self' method='post'>\n".
	    "<input type='hidden' name='op' value='delnotify' />\n".
	    "<table cellspacing='1' cellpadding='3' border='0' class='bg2'>\n".
	    "<tr class='bg4'><th></th><th>"._AM_ORDER_DATE."</th>".
	    "<th>"._AM_EMAIL."</th></tr>\n";
	$tags = css_tags();
	while ($data = $xoopsDB->fetchArray($result)) {
	    $bg = $tags[$n++%2];
	    $rvid = $data['rvid'];
	    $date = date(_AM_POST_FMT, $data['rdate']);
	    $email = $data['email'];
	    if (isset($data['uid'])) {
		$uid = $data['uid'];
		$uinfo = " (<a href='".XOOPS_URL."/userinfo.php?uid=$uid'>".XoopsUser::getUnameFromId($uid)."</a>)";
	    } else {
		$uinfo = "";
	    }
	    echo "<tr class='$bg'><td><input type='checkbox' name='rm$n' value='$rvid' /></td>".
		"<td>$date</td><td>$email $uinfo</td></tr>\n";
	}
	echo "</table><br /><input type='submit' value='".
	    _DELETE."' />\n</form>\n<p><a href='../reserv.php?op=register'>".
	    _MI_EGUIDE_REG."</a></p>";
    } else {
	echo "<div class='evnote'>"._AM_INFO_NODATA."</div>";
    }
    CloseTable();
    break;

case "delnotify":
    foreach ($HTTP_POST_VARS as $i => $v) {
	if (preg_match('/^rm\d+$/', $i, $d)) {
	    if (empty($cond)) $cond = "rvid=$v";
	    else $cond .= " OR rvid=$v";
	}
    }
    $result = $xoopsDB->queryF($sql = "DELETE FROM $rsv WHERE eid=0 AND ($cond)");
    redirect_header("$self?op=notifies",1,_AM_DBUPDATED);
    exit;

case "edit":
    xoops_cp_header();
    OpenTable();
    $result = $xoopsDB->query("SELECT eid,edate,cdate,title,uid,status FROM $tbl WHERE eid=$eid");
    $data = $xoopsDB->fetchArray($result);
    $date = date(_AM_DATE_FMT, $data['edate']); 
    $title = "<a href='../event.php?eid=$eid'>".$data['title']."</a>";
    $uid = $data['uid'];
    $poster = new XoopsUser($uid);
    $post = date(_AM_POST_FMT, $data['cdate']);

    echo "<h4>"._MI_EGUIDE_EVENTS."</h4>";
    echo "<form action='$self' method='post'>\n";
    echo "<table border='0'>\n";
    echo "<tr><td class='nw'>"._AM_EVENT_DAY."</td><td>$date</td></tr>\n";
    echo "<tr><td class='nw'>"._AM_TITLE."</td><td>$title</td></tr>\n";
    echo "<tr><td class='nw'>"._AM_POSTER."</td><td>";
    $result = $xoopsDB->query("SELECT u.uid,groupid,uname".
	" FROM ".$xoopsDB->prefix("groups_users_link")." l, ".
	$xoopsDB->prefix("users")." u WHERE l.uid=u.uid AND ".
	"(groupid=1 OR groupid=".$eventConfig['group'].") GROUP BY u.uid ORDER BY uname");
    echo "<select name='uid'>\n";
    while($p=$xoopsDB->fetchArray($result)) {
	$ck = ($uid==$p['uid'])?" selected":"";
	printf("<option value='%d'$ck>%s</>\n", $p['uid'], $p['uname']);
    }
    echo "</select></td></tr>\n";
    echo "<tr><td class='nw'>"._AM_POSTED."</td><td>$post</td></tr>\n";
    echo "<tr><td class='nw'>"._AM_DISP_STATUS."</td><td>\n";
    echo "<select name='status'>\n";
    $status=$data['status'];
    foreach ($ev_stats as $i =>$v) {
	$ck = ($status == $i)?" selected":"";
	echo "<option value='$i'$ck>$v</option>\n";
    }
    echo "</select>\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "<input type='hidden' name='op' value='save' />";
    echo "<input type='hidden' name='eid' value='$eid' />";
    echo "<input type='submit' value='"._AM_SAVECHANGE."' />";
    echo "&nbsp;<input type='button' value='"._AM_CANCEL."' onclick='javascript:history.go(-1)' />";
    echo "</form>";
    CloseTable();
    break;

case "save":
    $result = $xoopsDB->query("UPDATE $tbl SET uid=$uid, status=$status WHERE eid=$eid");
    redirect_header("$self?op=events",1,_AM_DBUPDATED);
    exit();

case "eventConfig":
    eventConfig();
    break;

case "eventConfigS":
    eventConfigS();
    break;

case "resvCtrl":
    $rv = isset($HTTP_POST_VARS['rv'])?$HTTP_POST_VARS['rv']:array();
    $ck = isset($HTTP_POST_VARS['ck'])?$HTTP_POST_VARS['ck']:array();
    $off = $on = "";
    foreach (array_keys($rv) as $k) {
	if (isset($ck[$k])) {
	    if ($on!="") $on .= " OR ";
	    $on .= "eid=".intval($k);
	} else {
	    if ($off!="") $off .= " OR ";
	    $off .= "eid=".intval($k);
	}
    }
    if ($on != "") {
	$result = $xoopsDB->query("UPDATE $opt SET reservation=1 WHERE $on");
    }
    if ($off != "") {
	$result = $xoopsDB->query("UPDATE $opt SET reservation=0 WHERE $off");
    }
    redirect_header("$self?op=events",1,_AM_DBUPDATED);
    exit();

default:
    xoops_cp_header();
    OpenTable();
    include_once("menu.php");
    $base = XOOPS_URL."/modules/".$xoopsModule->dirname();
    foreach ($adminmenu as $v) {
	$title = $v['title'];
	$link = $v['link'];
	echo "<p> - <b><a href='$base/$link'>$title</a></b></p>\n";
    }
    CloseTable();
    break;
}

xoops_cp_footer();
?>