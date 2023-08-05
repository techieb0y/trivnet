<?php

function has_session() {
	require_once("db_ops.inc");
	require("config.inc");

	$q = 'SELECT timestamp, extract(epoch FROM now())-timestamp AS age FROM sessions WHERE timestamp <> 0 AND sessionid = $1;';
	$p[] = session_id();
	$res = pg_query_params( connect(), $q, $p );

	if ( pg_num_rows($res) > 0 ) {
		$r = array();
		while ( $z = pg_fetch_assoc($res) ) {
			$r[] = $z;
		} // end while

		if ( count($r) > 0 ) {
			$age = $r[0]["age"];
			if ( $age > $config["idle_timeout"] ) {
				// They have an old session, most likely disconnected.
				$qn = "SELECT callsign FROM sessions WHERE sessionid='" . session_id() . "'";
				$rn = query($qn);
				$call = $rn[0]["callsign"];

				$q = "UPDATE sessions SET timestamp=0 WHERE sessionid='" . session_id() . "';";
				$r = query($q);
				error_log("Disconnected timed-out ($age secs)  session for $call", 0);
				return false;
			} else {
				return true;
				error_log("Detected duplicate session for " . session_id(), 0);
			} // end if - idle timeout
		} else {
			return false;
		} // end if	-- has session
	} else {
		return false;
	}
} // end has_session

function start_session() {
	require_once("db_ops.inc");

	if ( has_session() ) {
		// That call already has a session
	} else {
		$call = $_SESSION["callsign"];
		$sym = $_SESSION["symbol"];
		$tac = $_SESSION["tactical"];
		$q3 = "BEGIN;";
		$t = time();
		$q3 .= "INSERT INTO sessions VALUES ('" . session_id() . "','$call', '$t', '$sym', '$tac');";
		$q3 .= "INSERT INTO messages VALUES ('$t', '$call', 'logged on', 'Sysop' );";
		$q3 .= "INSERT INTO pidmap VALUES (" . getmypid() . ",'" . $call . "');";
		$q3 .= "COMMIT;";
		query_simple($q3);
		// echo "$newid\n";
	} // end if
} // end start_session

function stop_session() {
	require_once("db_ops.inc");
	$qn = "SELECT callsign FROM sessions WHERE sessionid='" . session_id() . "'";
	$rn = query($qn);
	$call = $rn[0]["callsign"];

	$newid = substr( "VOID-" . md5( time() . session_id() ), 0, 32 );

	$q = "BEGIN;";
	$q .= "UPDATE sessions SET timestamp=0,sessionid='$newid' WHERE sessionid='" . session_id() . "';";
	$q .= "INSERT INTO messages VALUES (" . time() . ", '$call', 'logged off', 'Sysop' );";
	$q .= "DELETE FROM pidmap WHERE pid=" . getmypid() . ";";
	$q .= "COMMIT;";
	query_simple($q);
	syslog(LOG_DEBUG, $q);

	$_SESSION = array();
	session_destroy();
	session_regenerate_id();
} // end stop_session

function list_sessions() {
	require_once("db_ops.inc");
	$q = "SELECT sessionid, callsign, tactical, symbol, timestamp, extract(epoch FROM now())-timestamp AS age FROM sessions WHERE timestamp <> 0 AND sessionid NOT LIKE 'VOID%';";
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
	$q .= "INSERT INTO messages VALUES (" . time() . ", '$call', 'logged out due to inactivity', 'Sysop' );";
	$q .= "DELETE FROM pidmap WHERE pid=" . getmypid() . ";";
	$q .= "COMMIT;";
	query_simple($q);

	$_SESSION = array();
	session_destroy();
} // end adminDeleteSession

?>
