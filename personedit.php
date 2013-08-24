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
			$t = query("SELECT typeid,enum FROM datatypes WHERE name='$what'");
			$typeid = $t[0]["typeid"];
			$isenum = $t[0]["enum"];

			if ( 't' == $isenum ) {
				// Populate a table of enumerated values for this datatype
				$_enums = query("SELECT id, value FROM enumtypes WHERE datatype=$typeid");
				foreach( $_enums as $x ) {
					$id = $x["id"];
					$vl = $x["value"];
					$enums[$typeid][$id] = $vl;
				} // end foreach
			} // end if

			$w = query("SELECT value FROM persondata WHERE personid=$who AND datatype=$typeid");
			$was = $w[0]["value"];

			if ( count($was) > 0 ) {
				$q .= "UPDATE persondata SET value='$value' WHERE ( datatype=$typeid AND personid=$who );\n";
				if ( isset( $enums[$typeid][$value] ) ) {
					$_was = $enums[$typeid][$was];
					$_value = $enums[$typeid][$value];
					$q .= "INSERT INTO updatesequence VALUES ('$who', '$now', '$mycall', 0, 'Changed $what from $_was to $_value');\n";
				} else {
					$q .= "INSERT INTO updatesequence VALUES ('$who', '$now', '$mycall', 0, 'Changed $what from $was to $value');\n";
				} // end if
			} else {
				$q .= "INSERT INTO persondata VALUES ( $who, $typeid, '$value' );\n";
				if ( isset( $enums[$typeid][$value] ) ) {
					$_value = $enums[$typeid][$value];
					$q .= "INSERT INTO updatesequence VALUES ($who, '$now', '$mycall', 0, 'Set $what to $_value');\n";
				} else {
					$q .= "INSERT INTO updatesequence VALUES ($who, '$now', '$mycall', 0, 'Set $what to $value');\n";
				} // end if
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
