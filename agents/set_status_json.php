<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");
require_once("../include/sessions.inc");

global $config;

$ok = 0;

// Make sure this is permitted
session_start();
// session_register("mycall");
$tac = $_SESSION["tactical"];
$call = $_SESSION["callsign"];
if ( strlen($tac) > 0 ) { 
	$mycall = $tac . "/" . $call;
} else {
	$mycall = $call;
} // end if

// Read the data
$rawData = file_get_contents('php://input');
$data = json_decode($rawData);

$value = $data->searchKey;

$typeid = $config["multidefault"];

$q_id = "SELECT personid FROM persondata WHERE value='$value' AND datatype='$typeid'";
$r_id=query($q_id);

if ( count($r_id) == 1 ) {
	$id = $r_id[0]["personid"];
	$ok = 1;
} // end if

$status = $data->data;

if ( has_session($mycall) ) {
	$now = time();
	$dt = $config["message"];
	$qr = "INSERT INTO updatesequence VALUES ( $id, $now, '$mycall', $dt, '$status' )";
	$r = query($qr);
} else {
	$ok = 0;
	header("401 Unauthorized");
}

$input_id = $data->id;
$ret["id"] = $input_id;

if ( 1 == $ok ) {
	$ret["result"] = "OK";
} else {
	$ret["result"] = "FAIL";
} // end if
	
$return = json_encode($ret);
echo $return;
?>
