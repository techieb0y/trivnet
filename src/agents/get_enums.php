<?php

require_once("../include/config.inc");
require_once("../include/db_ops.inc");

header("Content-type: application/json");

$datatype = $_GET["datatype"];

$q_isenum = "SELECT enum FROM datatypes WHERE typeid=$datatype";
$r = query($q_isenum);

if ( $r[0]["enum"] ) {

	$q_enumlist = "SELECT id,value FROM enumtypes WHERE datatype=$datatype";
	$r_enumlist = query($q_enumlist);

	echo json_encode($r_enumlist);
}

?>
