<?php
// ------------------------------------------------------------------------- //
//                XOOPS - PHP Content Management System                      //
//                       <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------- //
// Based on:								     //
// myPHPNUKE Web Portal System - http://myphpnuke.com/	  		     //
// PHP-NUKE Web Portal System - http://phpnuke.org/	  		     //
// Thatware - http://thatware.org/					     //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //
function b_event_top_show($options) {
    global $xoopsDB, $xoopsUser;
    $content = "";
    $sql = "SELECT eid, title, edate, cdate, uid FROM ".$xoopsDB->prefix("eguide")." WHERE expire>".time()." AND status=0 ORDER BY edate";
    $result = $xoopsDB->query($sql, 5, 0);
    while ( $myrow = $xoopsDB->fetchArray($result) ) {
	$title = htmlspecialchars($myrow["title"]);
	if ( !XOOPS_USE_MULTIBYTES ) {
	    if (strlen($title) >= 19) {
		$title = substr($title,0,18)."...";
	    }
	}

	$date = "<strong>".date(_BLOCK_DATE_FMT, $myrow['edate'])."</strong>&nbsp;";
	if ($options[0]) {
	    $poster = new XoopsUser($myrow['uid']);
	    $add = "["._BLOCK_EV_POST." ".date(_BLOCK_DATE_FMT, $myrow['cdate'])." ".$poster->uname()."]";
	} else {
	    $add = "";
	}
	$eid = $myrow['eid'];
	$content .= "<div class='evline'>$date<a href='".XOOPS_URL."/modules/eguide/event.php?eid=$eid'>$title</a> $add</div>\n";
    }
    $mod = XoopsModule::getByDirname("eguide");
    if ($xoopsUser && $xoopsUser->isAdmin($mod->mid())) {
	$result = $xoopsDB->query("SELECT count(eid) FROM ".$xoopsDB->prefix("eguide")." WHERE status=1");
	if ($xoopsDB->getRowsNum($result)) {
	    $n = array_shift($xoopsDB->fetchArray($result));
	    if ($n) $content .= "<p><a href='".XOOPS_URL."/modules/eguide/admin/index.php?op=events'>"._BLOCK_EV_WAIT."</a>: $n</p>";
	}
    }
    return array("content"=>$content, "title"=>_MI_EGUIDE_HEADLINE);
}
function b_event_top_edit($options) {
    if ($options[0]) {
	$sel0=" checked";
	$sel1="";
    } else {
	$sel0="";
	$sel1=" checked";
    }
    return _BLOCK_EV_STYLE."&nbsp;".
	"<input type='radio' name='options[]' value='1'$sel0 />"._YES." &nbsp; \n".
	"<input type='radio' name='options[]' value='0'$sel1 />"._NO."\n";
}
?>