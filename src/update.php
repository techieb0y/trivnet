<?php

require("include/db_ops.inc");
require("include/sessions.inc");

$tac = $_SESSION["tactical"];
$call = $_SESSION["username"];
if ( strlen($tac) > 0 ) { 
	$mycall = $tac . "/" . $call;
} else {
	$mycall = $call;
} // end if


$id = $_GET["id"];
$quickmesg = $_POST["quickmesg"];
$customtext = $_POST["customtext"];

$mt = $config["message"];

if ( strlen($customtext) > 0 ) {
	$text = $customtext;

	$q = "INSERT INTO updatesequence VALUES ( $id, " . time() . ", '$mycall', $mt, '$text' )";
	$r = query($q);
} else {
	if ( $quickmesg != "null" ) {
		$text = $quickmesg;

		$q = "INSERT INTO updatesequence VALUES ( $id, " . time() . ", '$mycall', $mt, '$text' )";
		$r = query($q);
	} // end if
} // end if

header("Location: detail.php?id=$id");
?>
