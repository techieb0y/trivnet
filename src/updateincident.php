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

$id = $_GET["id"];
$mesg = $_POST["message"];
$text = pg_escape_string(connect(), $_POST["message"]);

$q = "INSERT INTO incidentsequence VALUES ( $id, extract(epoch from NOW()), '{$text}' )";
$r = query($q);

header("Location: incident.php?id=$id");
?>
