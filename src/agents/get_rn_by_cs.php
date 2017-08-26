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
	$qr = "SELECT name FROM part97 WHERE callsign='$cs'";
	$r = query($qr);
	$name = $r[0]["name"];
} // end if

echo "{ \"name\": \"$name\" }";

?>
