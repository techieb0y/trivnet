<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");
require_once("../include/sessions.inc");

// header("Content-type: text/xml");
header("Content-type: text/plain");

$ok = 0;
global $config;
$msgtyp = $config["message"];

$status = pg_escape_string( $_GET["status"] );
$id = $_GET["personid"];

session_start();
// session_register("mycall");
$tac = $_SESSION["tactical"];
$call = $_SESSION["callsign"];
if ( strlen($tac) > 0 ) { 
	$mycall = $tac . "/" . $call;
} else {
	$mycall = $call;
} // end if


if ( has_session($mycall) ) {
	$now = time();
	$qr = "INSERT INTO updatesequence VALUES ( $id, $now, '$mycall', $msgtyp, '$status' )";
	syslog(LOG_DEBUG, $qr);
	$r = query($qr);
	$ok = 1;
} else {
	header("401 Unauthorized");
}

echo "{ \"result\": $ok }";

?>
