<?php

	require_once("include/config.inc");
	require_once("include/constants.inc");
	require_once("include/db_ops.inc");
	require_once("include/sessions.inc");

	session_start();

	global $config;
	$msgdt = $config["message"];

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

		if ( strlen($what) > 0 ) {
			$value = pg_escape_string(connect(), $parts[1]);

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

			$q_who = 'SELECT value FROM persondata WHERE personid = $1 AND datatype = $2;';
			$p[0] = $who;
			$p[1] = $typeid;
			$res = pg_query_params( connect(), $q_who, $p );
			$w = array();
			if ( pg_num_rows($res) == 1) {
				$z = pg_fetch_assoc($res);
				$was = $z["value"];

				$q .= "UPDATE persondata SET value='$value' WHERE ( datatype=$typeid AND personid=$who );\n";
				if ( isset( $enums[$typeid][$value] ) ) {
					$_was = $enums[$typeid][$was];
					$_value = $enums[$typeid][$value];
					$q .= "INSERT INTO updatesequence VALUES ('$who', '$now', '$mycall', $msgdt, 'Changed $what from $_was to $_value');\n";
				} else {
					$q .= "INSERT INTO updatesequence VALUES ('$who', '$now', '$mycall', $msgdt, 'Changed $what from $was to $value');\n";
				} // end if
			} else {
				$q .= "INSERT INTO persondata VALUES ( $who, $typeid, '$value' );\n";
				if ( isset( $enums[$typeid][$value] ) ) {
					$_value = $enums[$typeid][$value];
					$q .= "INSERT INTO updatesequence VALUES ($who, '$now', '$mycall', $msgdt, 'Set $what to $_value');\n";
				} else {
					$q .= "INSERT INTO updatesequence VALUES ($who, '$now', '$mycall', $msgdt, 'Set $what to $value');\n";
				} // end if
			} // end if
		}
	} // end foreach
	$q .= "COMMIT;\n";
	// syslog(LOG_DEBUG, $q);
	$result = query($q);

	header("Location: detail.php?id=$who");
?>
