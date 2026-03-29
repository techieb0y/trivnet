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

$id = $_POST["id"];
$q = 'INSERT INTO incidentsequence VALUES ( $1, extract(epoch from NOW()), $2 )';
$p[0] = $id;
$p[1] = pg_escape_string(connect(), $_POST["message"]);

$r = pg_query_params( connect(), $q, $p );

header("Location: incident.php?id=$id");
?>
