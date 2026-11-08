<?php
require_once("include/db_ops.inc");
require_once("include/sessions.inc");

$to = "all";

if ( isset($_POST["sendto"]) ) {
	$to = $_POST["sendto"];
	if ( ("ALL" == $to) || ("*" == $to) || ("everyone" == $to) || ("any" == $to) || (null == $to) ) {
		$to = "all";
	} // end if
} // end if

$from  = $_SESSION["username"];
$mytac = $_SESSION["tactical"];
$mysym = $_SESSION["symbol"];

$text = pg_escape_string(connect(), $_POST["text"]);

$q = "INSERT INTO messages VALUES ( " . time() . ", '$from', '$mysym', '$mytac', '$text', '$to' )";
$r = query($q);

header("Location: messaging.php");
?>
