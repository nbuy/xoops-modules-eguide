<?php
// Event Guide global administration
// $Id: index.php,v 1.18 2005/11/19 08:57:14 nobu Exp $
include 'admin_header.php';

$self = $_SERVER["SCRIPT_NAME"];

$op = param('op', '');
$eid = param('eid');

function css_tags() { return array("even","odd"); }

switch($op) {
case "events":
    xoops_cp_header();
    echo "<h4>"._MI_EGUIDE_EVENTS."</h4>";
    $result = $xoopsDB->query('SELECT o.*,edate,title,uid,status FROM '.EGTBL.
			      ' e LEFT JOIN '.OPTBL." o ON e.eid=o.eid ORDER BY ldate DESC");
    $n = 0;
    echo "<form action='$self' method='post'>\n";
    echo "<table cellspacing='1' border='0' class='outer'>\n";
    echo "<tr class='bg4'><th>"._AM_RESERVATION."</th><th>".
	_AM_EVENT_DAY."</th><th>"._AM_TITLE."</th>";
    echo "<th>"._AM_POSTER."</th><th>"._AM_DISP_STATUS."</th>";
    echo "<th>"._AM_OPERATION."</th></tr>\n";
    $tags = css_tags();
    while ($data = $xoopsDB->fetchArray($result)) {
	$bg = $tags[$n++%2];
	$eid = $data['eid'];
	$date = eventdate($data['edate']);
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
	$ors = $xoopsDB->query("SELECT reservation FROM ".OPTBL." WHERE eid=$eid");
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
    $result = $xoopsDB->query("SELECT count(rvid) FROM ".RVTBL." WHERE eid=0");
    if ($result) {
	list($n) = $xoopsDB->fetchRow($result);
	echo "<p><a href='$self?op=notifies'>"._MD_INFO_REQUEST."</a> ".sprintf(_MD_INFO_COUNT, $n)."</p>\n";
    }
    CloseTable();
    break;

case "notifies":
    xoops_cp_header();
    echo "<h4>"._MD_INFO_REQUEST."</h4>";
    $cond = "eid=0";
    if (isset($_GET['q'])) {
	$q = $_GET['q'];
	$cond .= " AND email like '%$q%'";
    }
    $result = $xoopsDB->query("SELECT * FROM ".RVTBL." WHERE $cond ORDER BY rdate");
    $n = 0;
    $nc = $xoopsDB->getRowsNum($result);
    echo "<form action='$self' method='get'>\n".
	_MD_INFO_SEARCH." <input name='q' />".
	" <input type='hidden' name='op' value='notifies' />\n".
	" <input type='submit' value='"._SUBMIT."' />\n".
	"</form>\n";
    echo sprintf(_MD_INFO_COUNT, $nc);
    if ($nc) {
	echo "<form action='$self' method='post'>\n".
	    "<input type='hidden' name='op' value='delnotify' />\n".
	    "<table cellspacing='1' border='0' class='outer'>\n".
	    "<tr class='bg4'><th></th><th>"._AM_ORDER_DATE."</th>".
	    "<th>"._AM_EMAIL."</th></tr>\n";
	$tags = css_tags();
	while ($data = $xoopsDB->fetchArray($result)) {
	    $bg = $tags[$n++%2];
	    $rvid = $data['rvid'];
	    $date = formatTimestamp($data['rdate'], _AM_POST_FMT);
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
	echo "<div class='evnote'>"._MD_INFO_NODATA."</div>";
    }
    CloseTable();
    break;

case "delnotify":
    foreach ($_POST as $i => $v) {
	if (preg_match('/^rm\d+$/', $i, $d)) {
	    if (empty($cond)) $cond = "rvid=$v";
	    else $cond .= " OR rvid=$v";
	}
    }
    $result = $xoopsDB->queryF($sql = "DELETE FROM ".RVTBL." WHERE eid=0 AND ($cond)");
    redirect_header("$self?op=notifies",1,_AM_DBUPDATED);
    exit;

case "edit":
    xoops_cp_header();
    $result = $xoopsDB->query("SELECT eid,edate,cdate,title,uid,status FROM ".EGTBL." WHERE eid=$eid");
    $data = $xoopsDB->fetchArray($result);
    $date = eventdate($data['edate']); 
    $title = "<a href='../event.php?eid=$eid'>".$data['title']."</a>";
    $uid = $data['uid'];
    $poster = new XoopsUser($uid);
    $post = formatTimestamp($data['cdate'], _AM_POST_FMT);

    echo "<h4>"._MI_EGUIDE_EVENTS."</h4>";
    echo "<form action='$self' method='post'>\n";
    echo "<table border='0' cellspacing='1' class='outer'>\n";
    echo "<tr><td class='head'>"._AM_EVENT_DAY."</td><td class='even'>$date</td></tr>\n";
    echo "<tr><td class='head'>"._AM_TITLE."</td><td class='odd'>$title</td></tr>\n";
    echo "<tr><td class='head'>"._AM_POSTER."</td><td class='even'>";
    $result = $xoopsDB->query("SELECT u.uid,groupid,uname".
	" FROM ".$xoopsDB->prefix("groups_users_link")." l, ".
	$xoopsDB->prefix("users")." u WHERE l.uid=u.uid AND ".
	"(groupid=1 OR groupid=".$xoopsModuleConfig['group'].") GROUP BY u.uid ORDER BY uname");
    echo "<select name='uid'>\n";
    while($p=$xoopsDB->fetchArray($result)) {
	$ck = ($uid==$p['uid'])?" selected":"";
	printf("<option value='%d'$ck>%s</>\n", $p['uid'], $p['uname']);
    }
    echo "</select></td></tr>\n";
    echo "<tr><td class='head'>"._AM_POSTED."</td><td class='odd'>$post</td></tr>\n";
    echo "<tr><td class='head'>"._AM_DISP_STATUS."</td><td class='even'>\n";
    echo "<select name='status'>\n";
    $status=$data['status'];
    foreach ($ev_stats as $i =>$v) {
	$ck = ($status == $i)?" selected":"";
	echo "<option value='$i'$ck>$v</option>\n";
    }
    echo "</select>\n";
    echo "</td></tr>\n";
    echo "</table>\n";
    echo "<p><input type='hidden' name='op' value='save' />";
    echo "<input type='hidden' name='eid' value='$eid' />";
    echo "<input type='submit' value='"._AM_UPDATE."' />";
    echo "&nbsp;<input type='button' value='"._AM_CANCEL."' onclick='javascript:history.go(-1)' /></p>";
    echo "</form>";
    CloseTable();
    break;

case "save":
    $status = param('status');
    $uid = param('uid');
    $result = $xoopsDB->query("UPDATE ".EGTBL." SET uid=$uid, status=$status WHERE eid=$eid");
    redirect_header("$self?op=events",1,_AM_DBUPDATED);
    exit;

case "category":
    xoops_cp_header();
    echo "<h4>"._AM_CATEGORY."</h4>\n";
    showCategories();
    echo "<h4>"._AM_CATEGORY_NEW."</h4>\n";
    editCategory(0);
    break;

case "catedit":
    xoops_cp_header();
    echo "<h4>"._AM_CATEGORY."</h4>\n";
    editCategory(intval($_GET['cat']));
    break;

case "catsave":
    $catid=intval($_POST['catid']);
    $catname=$xoopsDB->quoteString(param('catname',''));
    $catdesc=$xoopsDB->quoteString(param('catdesc',''));
    $catimg=$xoopsDB->quoteString(param('catimg',''));
    if ($catid) {
	$xoopsDB->query("UPDATE ".CATBL." SET catname=$catname, catimg=$catimg, catdesc=$catdesc WHERE catid=$catid");
    } else {
	$xoopsDB->query("INSERT INTO ".CATBL."(catname, catimg, catdesc) VALUES($catname,$catimg,$catdesc)");
    }
    redirect_header("$self?op=category",1,_AM_DBUPDATED);
    exit;

case "catdel":
    $dels = $_POST['dels'];
    foreach (array_keys($dels) as $i) {
	$dels[$i] = intval($i);
    }
    $res = $xoopsDB->query("DELETE FROM ".CATBL." WHERE catid IN (".join(",",$dels).")");
    redirect_header("$self?op=category",1,_AM_DBUPDATED);
    exit;

case "resvCtrl":
    $rv = isset($_POST['rv'])?$_POST['rv']:array();
    $ck = isset($_POST['ck'])?$_POST['ck']:array();
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
	$result = $xoopsDB->query("UPDATE ".OPTBL." SET reservation=1 WHERE $on");
    }
    if ($off != "") {
	$result = $xoopsDB->query("UPDATE ".OPTBL." SET reservation=0 WHERE $off");
    }
    redirect_header("$self?op=events",1,_AM_DBUPDATED);
    exit;

default:
    xoops_cp_header();
    include_once("menu.php");
    $base = XOOPS_URL.'/modules/'.$xoopsModule->dirname();
    $adminmenu[] = array('title'=>_PREFERENCES,
			 'link'=>XOOPS_URL.'/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=' . $xoopsModule->getVar('mid'));
    foreach ($adminmenu as $menu) {
	$title = $menu['title'];
	$link = $menu['link'];
	if (!preg_match('/^https?:\/\/|^\//', $link)) $link = $base.'/'.$link;
	echo "<p> - <b><a href='$link'>$title</a></b></p>\n";
    }
    CloseTable();
    break;
}

xoops_cp_footer();

function showCategories() {
    global $xoopsDB;
    $myts =& MyTextSanitizer::getInstance();
    //$res = $xoopsDB->query('SELECT * FROM '.CATBL.' ORDER BY catid');
    $res = $xoopsDB->query('SELECT c.*,count(topicid) count FROM '.CATBL.' c LEFT JOIN '.EGTBL.' ON catid=topicid GROUP BY catid ORDER BY catid');

    echo "<form action='index.php?op=catdel' method='post'>\n";
    echo "<table border='0' cellspacing='1' class='outer'>\n";
    echo '<tr><th></th><th>'._AM_CAT_NAME.'</th><th>'._AM_CAT_IMG.'</th><th>'._AM_CAT_DESC.'</th><th>'._AM_COUNT.'</th><th>'._AM_OPERATION.'</th></tr>';
    $ndel = $n = 0;
    while ($data = $xoopsDB->fetchArray($res)) {
	$img = $data['catimg'];
	$name =  $myts->htmlSpecialChars($data['catname']);
	$id = $data['catid'];
	$desc =  $myts->htmlSpecialChars($data['catdesc']);
	$count = $data['count'];
	if (preg_match('/^\//', $img)) $img = XOOPS_URL.$img;
	elseif (!empty($img) && !preg_match('/^https?:/', $img)) {
	    $img = XOOPS_URL."/modules/eguide";
	} else {
	    $img = "";
	}
	if (!empty($img)) $img = "<img src='$img' alt='$name'/>";
	$edit="<a href='index.php?op=catedit&amp;cat=$id'>"._EDIT."</a>";
	if ($count) {
	    $del="-";
	} else {
	    $del="<input type='checkbox' name='dels[$id] value='$id'/>";
	    $ndel++;
	}
	echo '<tr class="'.(($n++%2)?'even':'odd').
	    "\"><td align='center'>$del</td>".
	    "<td>$name</td><td>$img</td><td>$desc</td><td>$count</td>".
	    "<td>$edit</td></tr>";
    }
    echo "</table>\n";
    if ($ndel) echo "<p><input type='submit' value='"._DELETE."'/></p>";
    echo "</form>\n";

    echo "<hr/>\n";
}

function editCategory($cat) {
    global $xoopsDB;

    $myts =& MyTextSanitizer::getInstance();
    $res = $xoopsDB->query('SELECT * FROM '.CATBL." WHERE catid=$cat");
    $data = $xoopsDB->fetchArray($res);
    if (empty($data)) {
	$img = '';
	$name =  '';
	$id = 0;
	$desc =  '';
    } else {
	$img = $data['catimg'];
	$name =  $myts->htmlSpecialChars($data['catname']);
	$id = $data['catid'];
	$desc =  $myts->htmlSpecialChars($data['catdesc']);
    }
    echo "<form action='index.php?op=catsave' method='post'>\n";
    echo "<table border='0' class='outer' cellspacing='1'>\n";
    echo "<tr><td class='head'>"._AM_CAT_NAME."</td><td class='even'><input name='catname' value='$name' size='30'/></td></tr>\n";
    echo "<tr><td class='head'>"._AM_CAT_IMG."</td><td class='odd'><input name='catimg' value='$img' size='50'/></td></tr>\n";
    echo "<tr><td class='head'>"._AM_CAT_DESC."</td><td class='even'><textarea name='catdesc'>$desc</textarea></td></tr>\n";
    echo "</table>\n";
    echo "<input type='hidden' name='catid' value='$id'/>\n";
    echo "<p><input type='submit' value='"._GO."'/>\n";
    echo "</form>\n";
}
?>