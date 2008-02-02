<?php
# search interface for eguide
# $Id: search.inc.php,v 1.7 2008/02/02 11:12:02 nobu Exp $

include dirname(dirname(__FILE__))."/mydirname.php";

eval( '
function '.$myprefix.'_search( $keywords , $andor , $limit , $offset , $userid )
{
	return eguide_search_base( "'.$myprefix.'" , $keywords , $andor , $limit , $offset , $userid ) ;
}

' ) ;

if (file_exists('eguide_search_base')) return;

function eguide_search_base($myprefix, $queryarray, $andor, $limit, $offset, $userid){
	global $xoopsDB;
	$sql = "SELECT eid,uid,title,edate,cdate, summary FROM ".$xoopsDB->prefix($myprefix)." WHERE status=0";
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
		$ret[$i]['description'] = $myrow['summary'];
		$i++;
	}
	return $ret;
}
?>