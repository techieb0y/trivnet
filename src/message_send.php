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
$text = $_POST["text"];

$q = "INSERT INTO messages VALUES ( " . time() . ", '$from', '$text', '$to' )";
$r = query($q);

$foo = $_SERVER["SCRIPT_NAME"];
$bar = str_replace("e_send", "ing", $foo);
header("Location: http://" . $_SERVER["HTTP_HOST"] . "/" . $bar);
?>
