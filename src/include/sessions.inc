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

function stop_session() {
	require_once("db_ops.inc");
	$q = "DELETE FROM sessions WHERE sessionid=\$1;";
	$p[] = session_id();
	$r = pg_query_params( connect(), $q, $p );

	unset($p);

	$q = "INSERT INTO messages VALUES (\$1, \$2, 31, 'N/A' , 'logged out', 'Sysop' );";
	$p[] = time();
	$p[] = $_SESSION["username"];
	$r = pg_query_params( connect(), $q, $p );

	unset($_SESSION);

	session_destroy();
	session_start();
	session_regenerate_id();
} // end stop_session

function adminDeleteSession($s) {
	require_once("db_ops.inc");
	$qn = "SELECT username FROM sessions WHERE sessionid='" . $s . "'";
	$rn = query($qn);
	$call = $rn[0]["username"];

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
