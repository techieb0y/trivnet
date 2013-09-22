<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");
require_once("../include/sessions.inc");

global $config;

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

syslog(LOG_DEBUG, "Got search key of $value");
syslog(LOG_DEBUG, "Multi-edit default datatype id is $typeid");

$q_id = "SELECT personid FROM persondata WHERE value='$value' AND datatype='$typeid'";
syslog(LOG_DEBUG, "Query is: $q_id");
$r_id=query($q_id);
$id = $r_id[0]["personid"];

syslog(LOG_DEBUG, "Found personID $id");

$status = $data->data;

if ( has_session($mycall) ) {
	$now = time();
	$qr = "INSERT INTO updatesequence VALUES ( $id, $now, '$mycall', 0, '$status' )";
	syslog(LOG_DEBUG, "Using query of $qr");
	$r = query($qr);
	$ok = 1;
} else {
	header("401 Unauthorized");
	syslog(LOG_DEBUG, "Session error; returning 401");
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
syslog(LOG_DEBUG, "Done");
?>
