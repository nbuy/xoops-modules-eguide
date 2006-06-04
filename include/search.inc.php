<?php

function eguide_search($queryarray, $andor, $limit, $offset, $userid, $desc=true){
	global $xoopsDB;
	$opt = $desc?", summary":"";
	$sql = "SELECT eid,uid,title,edate,cdate$opt FROM ".$xoopsDB->prefix("eguide")." WHERE status=0";
	//$sql .= " AND expire>".time();
	if ( $userid != 0 ) {
		$sql .= " AND uid=".$userid." ";
	} 
	// because count() returns 1 even if a supplied variable
	// is not an array, we must check if $querryarray is really an array
	if ( is_array($queryarray) && $count = count($queryarray) ) {
		$sql .= " AND ((summary LIKE '%$queryarray[0]%' OR body LIKE '%$queryarray[0]%' OR title LIKE '%$queryarray[0]%')";
		for($i=1;$i<$count;$i++){
			$sql .= " $andor ";
			$sql .= "(summary LIKE '%$queryarray[$i]%' OR body LIKE '%$queryarray[$i]%' OR title LIKE '%$queryarray[$i]%')";
		}
		$sql .= ") ";
	}
	$sql .= " ORDER BY edate DESC";
	$result = $xoopsDB->query($sql,$limit,$offset);
	$ret = array();
	$i = 0;
 	while($myrow = $xoopsDB->fetchArray($result)){
	    //$ret[$i]['image'] = "images/forum.gif";
		$ret[$i]['link'] = "event.php?eid=".$myrow['eid']."";
		$ret[$i]['title'] = formatTimestamp($myrow['edate'], 's').' '.
		    $myrow['title'];
		$ret[$i]['time'] = $myrow['cdate'];
		$ret[$i]['uid'] = $myrow['uid'];
		if ($desc) $ret[$i]['description'] = $myrow['summary'];
		$i++;
	}
	return $ret;
}
?>