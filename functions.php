<?php
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
?>