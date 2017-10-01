<?php

require("include/db_ops.inc");
require("include/sessions.inc");

session_start();
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

$foo = $_SERVER["SCRIPT_NAME"];
$bar = str_replace("update", "detail", $foo);
$baz = $bar . "?id=$id";
header("Location: http://" . $_SERVER["HTTP_HOST"] . "/" . $baz);

?>
