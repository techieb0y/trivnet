#!/usr/bin/php
<?php

error_reporting(E_ERROR);

if ( isset($_SERVER["SERVER_SOFTWARE"]) ) { die("This script is the command-line component of Trivnet; it is not to be run via a browser."); }

/*
 *  2008 Jan 02 - KD8GBL (Peter Corbett, peter@corbettdigital.net)
 *  This is the 145.67 / MN Packet Network's re-written version of KB8ZQZ's
 *  TrivNet DB. The original was in perl, and had no database normalization.
 *  This new version uses better database design and provides many extra
 *  features.
 */

$privlevel = 0;
$return = "";

$lastmsg = time();

require_once("include/config.inc");
require_once("include/db_ops.inc");
require_once("include/constants.inc");
require_once("include/sessions.inc");
require_once("include/cmdlist.inc");
require_once("include/functions.inc");

// We get invoked by (x)inetd, which handles the socket aspects for us.
// The downside is that we have several copies of the PHP parser in-memory
// at a time, but they're shared, so that's really not so bad.

global $db;
$db = connect();

$fp=fopen("php://stdin", "r");

echo "[KD8GBL's less-trivial database]\n\r";

echo "\n\rCallsign or tactical call>";
$mycall = trim( fgets($fp,4094) );

start_session($mycall);

// Get defaults
$q_def = "SELECT * from defaults WHERE callsign='none' OR callsign='$mycall'";
$r_def = query($q_def);

foreach ( $r_def as $r ) {
	$cs = trim($r["callsign"]);
	$ds = $r["defsearch"];
	$du = $r["defupdate"];
	$defs[$cs]["defsearch"] = $ds;
	$defs[$cs]["defupdate"] = $du;
} // end foreach

// print_r($defs);

if ( isset( $defs[$mycall]["defsearch"] ) && $defs[$mycall]["defsearch"] != "" ) {
	$defsearch = $defs[$mycall]["defsearch"];
} else {
	$defsearch = $defs["none"]["defsearch"];
} // end if

if ( isset( $defs[$mycall]["defupdate"] ) && $defs[$mycall]["defupdate"] != "" ) {
	$defupdate = $defs[$mycall]["defupdate"];
} else {
	$defupdate = $defs["none"]["defupdate"];
} // end if

echo "Defeault search attribute is $defsearch\n";
echo "Default update attribute is $defupdate\n";

// go into processing loop
$return = "";

echo "For help type 'help'\n\r";

while ( !feof($fp) ) {

	$supressReturn=0;
	// Read out the message buffer
	$q = "SELECT * FROM messages WHERE ( dest='$mycall' OR dest='all') AND timestamp > $lastmsg ORDER BY timestamp ASC LIMIT 5";
	$r = query($q);
	if ( count($r) > 0 ) {
		foreach ($r as $row) {
			echo date( "c", $row["timestamp"] ) . " " . $row["callsign"] . " " . $row["message"] . "\n\r";
			$lastmsg = $row["timestamp"];
			$supressReturn=1;
		} // end foreach
	} else {
		// No new messages
		$lastmsg = time();
	} // end if

	// Now do processing

	echo $return . $prompt;

	$rawline=trim( fgets($fp,4094) );
	$line = explode( " ", $rawline );
	$verb=$line[0];

	$return = "";

	unset($candidates);

	if ( strlen($verb) > 1 ) {
		foreach ( $cmds as $func => $word ) {
			foreach ( $word as $w ) {
				if ( preg_match( "/^" . $verb . "/i", $w ) ) {
					$candidates[] = $func;
				} // end if
			} // end foreach
		} // end foreach

		//	print_r($candidates);

		if ( count($candidates) > 1 ) {
			echo "Ambigous command\n";
		} else if ( count($candidates) == 0 ) {
			echo "No such command\n";
		} else {
			echo "Calling: " . $candidates[0] . "\n";
			call_user_func($candidates[0], $line);
		} // endif

	} // end if

	// Update my idle counter
	touch_session($mycall);
} // end of the main everything loop

function bye() {
	echo "\n\rBye!\n\r";
	exit(0);
}

bye();

?>
