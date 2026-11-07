<?php

session_start();

function list_sessions() {
	require_once("db_ops.inc");
	$q = "SELECT sessionid, userid, tactical, symbol, timestamp, extract(epoch FROM now())-timestamp AS age FROM sessions WHERE timestamp <> 0 AND sessionid NOT LIKE 'VOID%';";
	$r = query($q);
	return($r);
} // end list_sessions

function touch_session() {
	$q = "UPDATE sessions SET timestamp=" . time() . " WHERE sessionid='" . session_id() . "';";
	$r = query($q);
} // end touch_session

function adminDeleteSession($s) {
	require_once("db_ops.inc");
	$qn = "SELECT callsign FROM sessions WHERE sessionid='" . $s . "'";
	$rn = query($qn);
	$call = $rn[0]["callsign"];

	$q = "BEGIN;";
	$q .= "DELETE FROM sessions WHERE sessionid='" . $s . "';";
	$q .= "INSERT INTO messages VALUES (" . time() . ", '$call', 31, 'N/A' , 'logged out due to inactivity', 'Sysop' );";
	$q .= "DELETE FROM pidmap WHERE pid=" . getmypid() . ";";
	$q .= "COMMIT;";
	query_simple($q);

	$_SESSION = array();
	session_destroy();
} // end adminDeleteSession

?>
