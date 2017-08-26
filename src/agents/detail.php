<?php 

header("Content-type: text/plain");

$raceid=279;

// require_once("../include/head.inc");
require_once("../include/config.inc");
require_once("../include/db_ops.inc");

$id=0;

if ( isset($_GET["id"]) ) {
	$id = $_GET["id"];
} // end if


// ------- Re-dispplay the person's info, for confirmation that we're looking at
// the right person

// Pre-load the enumerated data value sets for display
$q_enum = "SELECT * FROM enumtypes";
$r_enum = query($q_enum);

foreach( $r_enum as $k => $v ) {
	$dt = $v["datatype"];
	$val = $v["value"];
	$eid = $v["id"];
	$enums[$dt][$eid] = $val;
} // end foreach

$theBigArray = array();

// Header
$qh = "SELECT typeid,name,label FROM datatypes ORDER BY typeid";
$rh = query($qh);
foreach ( $rh as $row ) {
	$typeid = $row["typeid"];
	$name = $row["name"];
	$label = $row["label"];
	$theBigArray[$typeid] = $name; 
} // end foreach

$theQuery = "SELECT * FROM crosstab('SELECT * from persondata WHERE personid=$id', 'SELECT typeid FROM datatypes ORDER BY typeid') as ( personid int";
foreach ( $theBigArray as $fieldName ) {
	$theQuery .= ", " . $fieldName . " varchar ";
} // end foreach
$theQuery .= ")";

// echo "<pre> $theQuery </pre>\n";

// Get results
$r_pd = query( $theQuery );

// Fetch the enum-ness of all the datatypes
$r_dts = query("SELECT typeid, name, enum FROM datatypes");
foreach($r_dts as $dt) {
	$_name = $dt["name"];
	$_id = $dt["typeid"];
	$_en = $dt["enum"];
	$dts[$_name]["id"] = $_id;
	$dts[$_name]["enum"] = $_en;
} // end foreach

$mdt = $config["message"];
$q = "SELECT timestamp,source,datatype,value from updatesequence WHERE personid=$id AND datatype=$mdt ORDER BY timestamp desc";
$r = query($q);

// $outputArray["datatypes"] = $theBigArray;
$outputArray["personData"] = $r_pd;
$outputArray["updateSequence"] = $r;

echo json_encode($outputArray, JSON_PRETTY_PRINT);
?>
