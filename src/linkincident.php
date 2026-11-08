<?php

require("include/db_ops.inc");
require("include/sessions.inc");

$tac = $_SESSION["tactical"];
$call = $_SESSION["username"];
if ( strlen($tac) > 0 ) { 
	$mycall = $tac . "/" . $call;
} else {
	$mycall = $call;
} // end if


$inc = $_POST["id"];
$who = $_POST["who"];

$q = "INSERT INTO incidentlink VALUES ( $inc, $who )";
$r = query($q);

header("Location: incident.php?id=$inc");
?>
