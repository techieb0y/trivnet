<?php

require("include/db_ops.inc");
require("include/sessions.inc");

session_start();
$tac = $_SESSION["tactical"];
$call = $_SESSION["callsign"];
if ( strlen($tac) > 0 ) { 
	$mycall = $tac . "/" . $call;
} else {
	$mycall = $call;
} // end if

if ( !has_session($mycall) ) {
	exit(0);
}

$inc = $_POST["id"];
$who = $_POST["who"];

$q = "INSERT INTO incidentlink VALUES ( $inc, $who )";
$r = query($q);

header("Location: incident.php?id=$inc");
?>
