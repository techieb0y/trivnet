<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");

// header("Content-type: text/xml");
header("Content-type: text/plain");

$username = trim(strtolower($_GET["username"]));
$qr = "SELECT id FROM users WHERE username='$username'";
$r = query($qr);
if ( count($r) > 0 ) {
	// echo "{ \"id\": \"$id\" }";
	echo json_encode($r[0]);
} else {
	echo "{ \"id\": null }";
}

?>
