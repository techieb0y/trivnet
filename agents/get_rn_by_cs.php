<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");

// header("Content-type: text/xml");
header("Content-type: text/plain");

$ok = 1;

// cs is the callsign 
$cs = trim(strtoupper($_GET["cs"]));

$qr = "SELECT name FROM part97 WHERE callsign='$cs'";
$r = query($qr);

if ( count($r) != 1 ) { $ok = 0; }

$name = $r[0]["name"];

// simulate delay
// usleep( rand(0,2000000) );

if ( count($r) != 2 ) { $ok = 0; }

echo "{ \"name\": \"$name\" }";

?>
