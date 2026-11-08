<?php
	require_once("include/sessions.inc");
	require_once("include/db_ops.inc");
	require_once("include/config.inc");

	@session_start();

	stop_session();

	header("HTTP/401 Unauthorized");
	$loc_parts = explode($_SERVER["REQUEST_URI"], "/");
	$loc_parts = array_slice($loc_parts, 0, -1);
	$loc_parts[] = "login.php";
	$loc = implode("/", $loc_parts);
	header("Location: " . $loc);
?>
