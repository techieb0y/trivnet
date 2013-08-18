<?php

	require_once("include/config.inc");
	require_once("include/constants.inc");
	require_once("include/db_ops.inc");
	require_once("include/sessions.inc");

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

	// ------------

	// print_r($_GET);
	$what = $_POST["setStatus"];
	$who = $_GET["personID"];
	$now = time();

	if ( ( isset($what) ) && ( strlen($what) > 0 ) ) {
		$sname = query("SELECT status FROM statustypes WHERE id=$what");

		$q = "BEGIN;\n";
		$q .= "UPDATE people SET status='$what' WHERE ( id=$who );\n";
		$msg = "Changed satus to " . $sname[0]["status"] . "\n";
		$q .= "INSERT INTO updatesequence VALUES ('$who', '$now', '$mycall', 0, '$msg');\n";
		$q .= "COMMIT;\n";
		$result = query($q);
	} // end sanity-check if

	$path = explode("/", $_SERVER["SCRIPT_NAME"]);
	$i = count($path);
	$newpath = implode( "/", array_slice($path, 0, $i-1) );
	$loc = "http://" .  $_SERVER["SERVER_NAME"] . $newpath;
 	header("Location: " . $loc . "/detail.php?id=$who");
?>
