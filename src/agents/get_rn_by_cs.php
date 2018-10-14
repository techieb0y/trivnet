<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");

// header("Content-type: text/xml");
header("Content-type: text/plain");

// cs is the callsign 
$cs = trim(strtoupper($_GET["cs"]));

if ("GUEST" == $cs) {
	$name = "Guest";
} else {
	$qr = "SELECT name,symbol FROM part97 WHERE callsign='$cs'";
	$r = query($qr);
	$name = $r[0]["name"];
	$symbol = $r[0]["symbol"];
} // end if

// echo "{ \"name\": \"$name\" }";
echo json_encode($r[0]);

?>
