<?php
// Event Reciption for Poster
// $Id: receipt.php,v 1.6 2004/07/07 06:34:55 nobu Exp $

include("header.php");
include_once(XOOPS_ROOT_PATH."/class/xoopscomments.php");
include_once("language/".$xoopsConfig['language']."/admin.php");

include("perm.php");
include("functions.php");

$tbl = $xoopsDB->prefix("eguide");
$opt = $xoopsDB->prefix("eguide_opt");
$rsv = $xoopsDB->prefix("eguide_reserv");
foreach ($HTTP_POST_VARS as $i => $v) {
    $$i = stripslashes($v);
}
foreach (array("op","rvid","eid") as $v) {
    if (isset($HTTP_GET_VARS[$v])) $$v = $HTTP_GET_VARS[$v];
}
if (isset($rvid)) {
    if (empty($op)) $op = "one";
    $result = $xoopsDB->query("SELECT * FROM $rsv WHERE rvid=$rvid");
    $data = $xoopsDB->fetchArray($result);
    $eid = $data['eid'];
    if ($op=='save') {
	$xoopsDB->query("UPDATE $rsv SET email='$email', status='$status', info='$info' WHERE rvid=$rvid");
	update_reserv($eid);
	$op='one';
    } elseif ($op=='delete') {
	$xoopsDB->query("DELETE FROM $rsv WHERE rvid=$rvid AND eid=$eid");
	update_reserv($eid);
	redirect_header("receipt.php?eid=$eid",2,_AM_DBUPDATED);
	exit;
    }
}

$result = $xoopsDB->query("SELECT * FROM $opt WHERE eid=$eid");
$opts = $xoopsDB->fetchArray($result);

$result = $xoopsDB->query("SELECT edate, title, uid FROM $tbl WHERE eid=$eid");
$head = $xoopsDB->fetchArray($result);
$title = date(_MD_DATE_FMT, $head['edate'])." ".htmlspecialchars($head['title']);
$poster = new XoopsUser($head['uid']);

if (empty($op)) $op = 'view';
$print = $op=='print';

// make optional field and countable list.
if (isset($eid)) {
    $result = $xoopsDB->query("SELECT optfield FROM $opt WHERE eid=$eid");
    $opts = $xoopsDB->fetchArray($result);
    $item = array();
    foreach (explode("\n",preg_replace('/\r/','',$opts['optfield'])) as $ln) {
	// comment line
	if (preg_match('/^\s*#/', $ln)||preg_match('/^\s*$/', $ln)) continue;
	$fld = explode(",", $ln);
	$lab = preg_replace('/^!\s*/', '',
			    preg_replace('/[\*#]$/', "", array_shift($fld)));
	$type = isset($fld[0])?strtolower($fld[0]):false;
	if ($type=="checkbox" || $type=="radio" || $type=="select") {
	    $mc[$lab]=$type;	// it's countable
	}
	$item[] =  $lab;
    }
}

$result = $xoopsDB->query("SELECT * FROM $rsv WHERE eid=$eid ORDER BY rvid");
$nrec = $xoopsDB->getRowsNum($result);

if ($nrec) {
    if ($op=='csv') {
	$charset = "Shift_JIS";
	if (isset($HTTP_GET_VAR['charset'])) {
	    $charset = $HTTP_GET_VAR['charset'];
	    if ($charset != _CHARSET) $charset = "Shift_JIS";
	}
	// field name
	$out = '"'._AM_ORDER_DATE.'","'._MD_EMAIL.'"';
	if (count($item)) {
	    $out .= ',"'.join('","', preg_replace('/\"/', '&quot;',$item)).'"';
	}
	$out .= "\n";
	// body
	while ($a = $xoopsDB->fetchArray($result)) {
	    $out .= '"'.date(_MD_TIME_FMT,$a['rdate']).'","'.$a['email'].'"';
	    foreach (explodeinfo($a['info'], $item) as $lab => $v) {
		if ($v) $v = '"'.preg_replace('/\n/', '\n', (preg_replace('/\"/', '&quot;', $v))).'"';
		$out .= ",$v";
	    }
	    $out .= "\n";
	}

	$file = "eguide_".date("Ymd").".csv";
	header("Content-Type: text/plain; Charset=$charset");
	header('Content-Disposition:attachment;filename="'.$file.'"');
	if (XOOPS_USE_MULTIBYTES && $charset != _CHARSET) {
	    if (function_exists("mb_convert_encoding")) {
		$out = mb_convert_encoding($out, $charset, _CHARSET);
	    } elseif (function_exists("iconv")) {
		$out = iconv(_CHARSET, $charset, $out);
	    }
	}
	echo $out;
	exit;
    }
}
if ($print) {
    include("print.php");
    PrintHeader();
} else {
    include(XOOPS_ROOT_PATH."/header.php");
    OpenTable();
}

echo "<p class='evhead'><a href='event.php?eid=$eid'>$title</a></p>\n";
switch ($op) {
case 'active':
    foreach (array_keys($HTTP_POST_VARS) as $i) {
	if (preg_match('/^act\d+$/', $i)) {
	    $rvid = $HTTP_POST_VARS[$i];
	    $result=$xoopsDB->query("SELECT * FROM $rsv WHERE rvid=$rvid");
	    $data = $xoopsDB->fetchArray($result);
	    if ($data) {
		$xoopsMailer =& getMailer();
		$xoopsMailer->useMail();
		$xoopsMailer->setSubject("Re: $title");
		$xoopsMailer->setBody($msg);
		$xoopsMailer->setFromEmail($poster->email());
		$xoopsMailer->setFromName("Event Reservation");
		$xoopsMailer->setToEmails($data['email']);
		$xoopsMailer->assign("EVENT_URL", XOOPS_URL."/modules/eguide/event.php?eid=$eid");
		$xoopsMailer->assign("ORDER_MAIL", $data['email']);
		$xoopsMailer->assign("TITLE", $title);
		$xoopsMailer->assign("INFO", $data['info']);
		$xoopsMailer->assign("RESULT", $yesno==1?
				     _AM_RESERV_ACTIVE:_AM_RESERV_REFUSE);
		if ($xoopsMailer->send()) {
		    $xoopsDB->query("UPDATE $rsv SET status='$yesno' WHERE rvid=$rvid");
		    echo $xoopsMailer->getSuccess();
		} else {
		    echo $xoopsMailer->getErrors();
		}
	    }
	}
    }
    update_reserv($eid);
    break;

case 'edit':
    echo "<h4>"._AM_RESERV_EDIT."</h4>";
    echo "<form action='receipt.php' method='post'>
<table class='evtbl' width='100%'>\n";
    echo "<input type='hidden' name='op' value='save' />\n";
    echo "<input type='hidden' name='rvid' value='$rvid' />\n";
    $atr = "align='left' class='bg1'";
    echo "<tr><th $atr>"._AM_RVID."</th><td class='bg3'>$rvid</td></tr>\n";
    echo "<tr><th $atr>"._AM_ORDER_DATE."</th><td class='bg3'>".date(_MD_TIME_FMT, $data['rdate'])."</td></tr>\n";
    echo "<tr><th $atr>"._MD_EMAIL."</th><td class='bg3'><input size='40' name='email' value='".$data['email']."' /></td></tr>\n";
    echo "<tr><th $atr>"._AM_STATUS."</th><td class='bg3'>\n";
    $s = $data['status'];
    echo "<select name='status'>\n";
    foreach ($rv_stats as $i => $v) {
	$ck = ($i==$s)?" selected":"";
	echo "<option value='$i'$ck>$v</option>\n";
    }
    echo "</select></td></tr>\n";
    echo "<tr><th $atr>"._AM_RESERV_ITEM."</th><td class='bg3'>\n";
    echo "<textarea name='info' cols='40' rows='5'>".
	htmlspecialchars($data['info'])."</textarea>\n";
    echo "</td></tr>\n";
    echo "<tr class='bg1'><td colspan='2' align='center'><input type='submit' value='"._AM_SAVECHANGE."' /></td></tr>\n";
    echo "</table>\n</form>\n";
    echo "<p><a href='receipt.php?eid=".$data['eid']."'>"._AM_RESERV_LIST."</a></p>";
    break;
case 'one':
    echo "<h4>"._AM_RESERV_REC."</h4>";
    $edit = "<a href='receipt.php?op=edit&amp;rvid=$rvid'>"._EDIT."</a>";
    $del ="<form action='receipt.php' method='post'><input type='checkbox' name='op' value='delete' /><input type='hidden' name='rvid' value='$rvid' />"._AM_RESERV_DEL." <input type='submit' value='"._DELETE."' /></form>";
    echo "<table class='evtbl'>\n";
    $atr = "align='left' class='bg1'";
    echo "<tr><th $atr>"._AM_RVID."</th><td class='bg3' nowrap>$rvid [$edit]</td></tr>\n";
    echo "<tr><th $atr>"._AM_ORDER_DATE."</th><td class='bg3'>".date(_MD_TIME_FMT, $data['rdate'])."</td></tr>\n";
    echo "<tr><th $atr>"._MD_EMAIL."</th><td class='bg3'>".$data['email']."</td></tr>\n";
    echo "<tr><th $atr>"._AM_STATUS."</th><td class='bg3'>".$rv_stats[$data['status']]."</td></tr>\n";
    foreach (explodeinfo($data['info'], $item) as $lab => $v) {
	if (empty($v)) $v = '&nbsp;';
	echo "<tr><th $atr>$lab</th><td class='bg3'>".nl2br($v)."</td></tr>\n";
    }
    echo "<tr><td class='bg3' colspan='2'>$del</td></tr>\n";
    echo "</table>\n";
    echo "<p><a href='receipt.php?eid=".$data['eid']."'>"._AM_RESERV_LIST."</a></p>";
    break;
default:
    $status = 0;
    echo "<table width='100%'><tr><td>"._AM_ORDER_COUNT." ".$nrec."</td>";
    echo "<td align='right'>"._AM_PRINT_DATE." ".date(_MD_POSTED_FMT)."</td></tr></table>";
    echo "<form action='receipt.php' method='post'>\n";
    echo "<table class='evtbl' width='100%'>\n";
    echo "<tr class='bg1'>";
    if (!$print) echo "<th>"._AM_OPERATION."</th>";
    echo "<th>"._AM_ORDER_DATE."</th><th>"._MD_EMAIL."</th>";
    if ($eventConfig['max_item'] && count($item)) {
	echo "<th>".join("</th><th>",
			 array_slice($item, 0, $eventConfig['max_item']))."</th>";
    }
    echo "</tr>\n";
    $nc = 0;
    $citem = array();
    $tags = preg_match("/^XOOPS 1\\./",XOOPS_VERSION)?array("bg1","bg3"):array("even","odd");
    while ($order = $xoopsDB->fetchArray($result)) {
	$bg = $tags[($nc++ % 2)];
	echo "<tr class='$bg'>";
	if (!$print) {
	    echo "<td nowrap>";
	    echo "<a href='receipt.php?rvid=".$order['rvid']."'>".
		_AM_EDITUSER."</a>\n";
	    if ($order['status']==_AM_RVSTAT_ORDER) {
		$r = $order['rvid'];
		echo "<input type='checkbox' name='act$r' value='$r' checked />\n";
		$status++;
	    }
	    echo "</td>";
	}
	printf("<td nowrap>%s</td><td><a href='mailto:%s'>%s</td>",
	       date(_AM_TIME_FMT, $order['rdate']),
	       $order['email'], $order['email']);
	$n = $eventConfig['max_item'];
	foreach (explodeinfo($order['info'], $item) as $lab => $v) {
	    if ($v && isset($mc[$lab])) {
		$mv = ($mc[$lab]=='checkbox')?explode(",",$v):array($v);
		foreach ($mv as $i) {
		    if (empty($i)) continue;
		    $x="$lab/$i";
		    if (isset($citem[$x])) $citem[$x]++;
		    else $citem[$x]=1;
		}
	    }
	    if ($v=="") $v="&nbsp;";
	    if ($n-->0) echo "<td>".nl2br($v)."</td>";
	}
	echo "</tr>\n";
    }
    echo "</table>\n";
    if ($status) {
	echo "<input type='hidden' name='op' value='active' />
<input type='hidden' name='eid' value='$eid' />
<table><tr><td><select name='yesno'>
<option value='"._AM_RVSTAT_RESERVED."'>"._AM_ACTIVATE."</option>
<option value='"._AM_RVSTAT_REFUSED."'>"._AM_REFUSE."</option>
</select></td><td>
<input type='submit' value='"._SUBMIT."' />
</td></tr>
</table>
<p><b>"._AM_RESERV_MSG_H."</b></p>
<textarea name='msg' cols='40' rows='5'>"._AM_RESERV_MSG."</textarea>
";
    }
    echo "</form>";
    if (!$print) {
	echo "<p align='right'>[ ";
	echo "<a href='receipt.php?op=csv&amp;eid=$eid'>"._AM_CSV_OUT."</a>";
	echo " | <a href='sendinfo.php?eid=$eid'>"._AM_INFO_MAIL."</a>";
	echo " ] <a href='receipt.php?op=print&amp;eid=$eid'><img src='".XOOPS_URL."/modules/news/images/print.gif' alt='"._PRINT."' border='0'></a>";
	echo "</p>";
    }
    if (count($citem)) {
	echo "<div class='evhead'>"._AM_SUMMARY."</div>";
	echo "<table class='evtbl'>\n";
	echo "<tr class='bg1'><th>"._AM_SUM_ITEM."</th><th>"._AM_SUM."</th></tr>\n";
	ksort($citem);
	$nc = 0;
	foreach ($citem as $i=>$v) {
	    $bg = ($nc++ % 2)?"bg3":"bg4";
	    echo "<tr class='$bg'><td>$i</td><td align='right'>$v</td></tr>\n";
	}
	echo "</table>\n";
    }
}

if ($print) {
    PrintFooter();
} else {
    CloseTable();
    include(XOOPS_ROOT_PATH."/footer.php");
}
exit;

function update_reserv($eid) {
    global $xoopsDB, $rsv, $opt;
    $result = $xoopsDB->query("SELECT count(rvid) FROM $rsv WHERE eid=$eid AND status="._AM_RVSTAT_RESERVED);
    $data=$xoopsDB->fetchArray($result);
    $n = array_shift($data);
    $xoopsDB->query("UPDATE $opt SET reserved=$n WHERE eid=$eid");
}

?>