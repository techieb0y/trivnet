<?php 
header("Content-type: text/plain");

// This is the XHR-JSON backend to the basic search page.

require_once("../include/config.inc");
require_once("../include/db_ops.inc");

$mdt = $config["message"];

// Pre-load the enumerated data value sets for display
$q_enum = "SELECT * FROM enumtypes order by datatype, id";
$r_enum = query($q_enum);

foreach( $r_enum as $k => $v ) {
	$dt = $v["datatype"];
	$val = $v["value"];
	$eid = $v["id"];
	$enums[$dt][$eid] = $val;
} // end foreach

if ( isset($_SESSION["criteria"] ) || ( isset($_GET) && ( count($_GET) > 1 ) ) ) {
	if ( isset( $_GET["offset"] ) ) { $offset = $_GET["offset"]; } else { $offset = 0; }

	// echo count($_GET);

	if ( strlen($_GET[0]) > 0 ) { 
		$q_base = "SELECT DISTINCT personid FROM updatesequence WHERE ";
	} else {
		$q_base = "SELECT DISTINCT personid FROM persondata WHERE ";
	} // end special handling for status messages

	$q2 = "SELECT * from persondata WHERE personid IN (";
	$prefix = "";

	$q_ex = "SELECT typeid,exact FROM datatypes";
	$r_ex = query($q_ex);

	$exactness = array();
	foreach ( $r_ex as $rk => $rv ) {
		$typeid = $rv["typeid"];
		$exactness[$typeid] = $rv["exact"];
	} // end foreach

	$q = $q_base;

	if ( strlen($_GET[0]) > 0 ) {
		$q = $q_base . "(datatype=$mdt AND value ILIKE ''%" . $_GET[0] . "%'')";
	} else {
	// echo "<pre>" . print_r($_GET) . "</pre>";
	foreach ( $_GET as $key => $param ) {

		if ( ($key != "type" ) && isset($param) && ( strlen($param) > 0 ) ) {

			if ( "t" == $exactness[$key] ) {
				$q .= $prefix . "( datatype=''$key'' AND value = ''$param'' ) ";
			} else {				
				$q .= $prefix . "( datatype=''$key'' AND value ILIKE ''%$param%'' ) ";
			} // end if

			if ( "AND" == $_GET["type"] ) {
				$prefix = "INTERSECT " . $q_base;
			} else {
				$prefix = "OR ";
			} // end if
		} // end if
	} // end foreach

	} // end special-case handling for status messages

	$qq = $q2 . $q . ")";

	$theBigArray = array();

	// Header
	$qh = "SELECT typeid,name,label FROM datatypes ORDER BY typeid";
	$rh = query($qh);
	foreach ( $rh as $row ) {
		$typeid = $row["typeid"];
		$name = $row["name"];
		$label = $row["label"];
		$theBigArray[$typeid] = $name;
		$reverseArray[$name] = $typeid;
	} // end foreach

	$theQuery = "SELECT * FROM crosstab('" . $qq . " ORDER BY personid', 'SELECT typeid FROM datatypes ORDER BY typeid') as ( personid int";
	foreach ( $theBigArray as $fieldName ) {
		$theQuery .= ", " . $fieldName . " varchar ";
	} // end foreach
	$theQuery .= ")";

	// Get results

	$r = query( $theQuery );
	
//	echo "<pre>" . print_r($r) . "</pre>";



	foreach ( $r as $key => $row ) {
		$pers = $row["personid"];
		foreach ( $row as $k => $f ) {
			if ( $k != "status") {
			// if ( ($k != "personid") && ($k != "status") ) {
				$tid = $reverseArray[$k];
				if ( isset( $enums[$tid] ) ) {
					$data[$key][$k] = $enums[$tid][$f];
				} else {
					$data[$key][$k] = $f;
				} // end if
			} // end if
		} // end foreach
	} // end foreach


 echo json_encode($data, JSON_PRETTY_PRINT);
} // end if

?>
