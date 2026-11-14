<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");

header("Content-type: application/json");

$datatype = $_GET["datatype"];

$q = "SELECT text FROM quickmesg";
$r = query($q);

if ( $r[0]["text"] ) {
	echo json_encode($r[0]["text"]);
}
?>
