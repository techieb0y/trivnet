<?php
	require_once("../include/db_ops.inc");
	require_once("../include/sessions.inc");
	header("Content-type: application/json");

$noauth = array( time(), 'Sysop', 'You are not authorized.', 'all' );
$nomesg = array( time(), 'Sysop', 'There are no messages.', 'all' );
session_start();

$num = $_GET["limit"];

if ( isset($_SESSION["callsign"]) ) { $mycall = $_SESSION["callsign"]; } else { $mycall = "N0CALL"; }
if ( !has_session($mycall) ) {
	header("HTTP/401 Unauthorized");
		echo json_encode($noauth);
	// header("WWW-Authenticate: Basic");
} else {
	touch_session($mycall);
	$q = "SELECT DISTINCT messages.callsign, messages.dest, messages.message, to_timestamp(messages.timestamp) AS timestamp, sessions.symbol, sessions.tactical FROM messages,sessions WHERE messages.callsign=sessions.callsign AND ( dest='$mycall' OR dest='all' OR messages.callsign='$mycall') ORDER BY timestamp DESC LIMIT $num";
	// $q = "SELECT callsign, dest, message, to_timestamp(timestamp) AS timestamp FROM messages ORDER BY timestamp DESC LIMIT $num";
	$r = query($q);
	if ( count($r) > 0 ) {
		echo json_encode($r);
	} else {
		header("HTTP/1.1 204 No Messages");
		echo json_encode($nomesg);
	} // end if
} // end if
?>
