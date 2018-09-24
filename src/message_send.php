<?php
session_start();

include("include/db_ops.inc");

$to = "all";

if ( isset($_POST["sendto"]) ) {
	$to = $_POST["sendto"];
	if ( ("ALL" == $to) || ("*" == $to) || ("everyone" == $to) || ("any" == $to) || (null == $to) ) {
		$to = "all";
	} // end if
} // end if

$from = $_SESSION["callsign"];
$text = pg_escape_string($_POST["text"]);

$q = "INSERT INTO messages VALUES ( " . time() . ", '$from', '$text', '$to' )";
$r = query($q);

header("Location: messaging.php");
?>
