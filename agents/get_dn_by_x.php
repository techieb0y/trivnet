<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");

// header("Content-type: text/xml");
header("Content-type: text/plain");

$ok = 1;

$dn = "";

// x is the datatype of the search key; q is the search key
$q = $_GET["q"];
$x = $_GET["x"];

$qr = "SELECT personid FROM persondata WHERE datatype=$x AND ( value = '$q' OR value = '" . strtoupper($q) . "')";
$r = query($qr);

if ( count($r) != 1 ) { $ok = 0; }

$id = $r[0]["personid"];

$qr = "SELECT datatype,value FROM persondata WHERE personid=$id AND datatype IN ( SELECT typeid FROM datatypes WHERE name='firstname' OR name='lastname');";

$r = query($qr);

// simulate delay
usleep( rand(0,2000000) );

if ( count($r) != 2 ) { $ok = 0; }

foreach($r as $k) { 
	$dt = $k["datatype"];
	$v = $k["value"];
	$out[$dt] = $v;
} // end foreach

// $fn = $out[3];
// $ln = $out[4];

foreach ( $out as $o ) {
	$dn .= $o . " ";
};

// $dn = $fn . " " . $ln;

echo "{ \"result\": $ok, \"personid\": \"$id\", \"displayname\": \"$dn\" }";

?>
