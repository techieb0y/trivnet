<?php

	require_once("include/config.inc");
	require_once("include/constants.inc");
	require_once("include/db_ops.inc");
	require_once("include/sessions.inc");

	session_start();
	$mycall = $_SESSION["callsign"];
	if ( !has_session($mycall) ) {
		exit(0);
	}

	// ------------

	// print_r($_GET);
	$tuples = explode(",", $_GET["updateString"]);
	$who = $_GET["personID"];
	// print_r($tuples);
	$now = time();

	$q = "BEGIN;\n";
	foreach ($tuples as $pair) {
		$parts = explode(":", $pair);
		$what = $parts[0];
		$value = $parts[1];

		if ( strlen($what) > 0 ) {
			$t = query("SELECT typeid FROM datatypes WHERE name='$what'");
			$typeid = $t[0]["typeid"];
			$w = query("SELECT value FROM persondata WHERE personid=$who AND datatype=$typeid");
			$was = $w[0]["value"];

			if ( count($was) > 0 ) {
				$q .= "UPDATE persondata SET value='$value' WHERE ( datatype=$typeid AND personid=$who );\n";
				$q .= "INSERT INTO updatesequence VALUES ('$who', '$now', '$mycall', 0, 'Changed $what from $was to $value');\n";
			} else {
				$q .= "INSERT INTO persondata VALUES ( $who, $typeid, '$value' );\n";
				$q .= "INSERT INTO updatesequence VALUES ($who, '$now', '$mycall', 0, 'Set $what to $value');\n";
			} // end if
		}
	} // end foreach
	$q .= "COMMIT;\n";
	$result = query($q);

	$path = explode("/", $_SERVER["SCRIPT_NAME"]);
	$i = count($path);
	$newpath = implode( "/", array_slice($path, 0, $i-1) );
	$loc = "http://" .  $_SERVER["SERVER_NAME"] . $newpath;
	header("Location: " . $loc . "/detail.php?id=$who");
?>
