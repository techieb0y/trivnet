<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");
require_once("../include/sessions.inc");

// header("Content-type: text/xml");
header("Content-type: text/plain");

$ok = 0;
global $config;
$msgtyp = $config["message"];

$status = pg_escape_string( connect(), $_GET["status"] );
$id = $_GET["personid"];

$tac = $_SESSION["tactical"];
$call = $_SESSION["username"];
if ( strlen($tac) > 0 ) { 
	$mycall = $tac . "/" . $call;
} else {
	$mycall = $call;
} // end if

$now = time();
$qr = "INSERT INTO updatesequence VALUES ( $id, $now, '$mycall', $msgtyp, '$status' )";
syslog(LOG_DEBUG, $qr);
$r = query($qr);
$ok = 1;

echo "{ \"result\": $ok }";

?>
