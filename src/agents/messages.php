<?php
require_once("../include/db_ops.inc");
require_once("../include/sessions.inc");
header("Content-type: application/json");

$authdata = apache_request_headers();
$display = $authdata["OIDC_CLAIM_display_name"];
$username = $authdata["OIDC_CLAIM_preferred_username"];

$noauth = array( time(), 'Sysop', 'You are not authorized.', 'all' );
$nomesg = array( time(), 'Sysop', 'There are no messages.', 'all' );
session_start();

$num = $_GET["limit"];

if ( isset( $_GET["start"] ) ) {
	$start = $_GET["start"];
} else {
	$start = time();
}

$q = "SELECT DISTINCT username, dest, message, timestamp, symbol, tactical FROM messages WHERE( dest='$username' OR dest='all' OR callsign='$username') AND timestamp < $start ORDER BY timestamp DESC LIMIT $num";

$r = query($q);
if ( count($r) > 0 ) {
	echo json_encode($r);
} else {
	header("HTTP/1.1 204 No Messages");
	echo json_encode($nomesg);
} // end if
?>
