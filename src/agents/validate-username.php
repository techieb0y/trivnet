<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");

// header("Content-type: text/xml");
header("Content-type: text/plain");

$username = trim(strtolower($_GET["username"]));
// FIXME: make this work with multiple sessions for a user.
$qr = "SELECT users.id,users.display,credentials.type,sessions.symbol,sessions.tactical FROM users,credentials,sessions WHERE users.id=credentials.id AND users.username = \$1";

$p[] = $username;
$res = pg_query_params( connect(), $qr, $p );

if (pg_num_rows($r) > 0 ) {
    $r = array();
    while ( $z = pg_fetch_assoc($res) ) {
        $r[] = $z;
    } // end while
}

if ( count($r) > 0 ) {
	// echo "{ \"id\": \"$id\" }";
	echo json_encode($r[0]);
} else {
	echo "{ \"id\": null }";
}

?>
