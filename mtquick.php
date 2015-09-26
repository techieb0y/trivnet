<?php

include("include/config.inc");
include("include/sessions.inc");
include("include/db_ops.inc");

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

if ( isset($_GET["personid"]) ) {
	$id = $_GET["personid"];
} // end if
if ( isset($_GET["direction"]) ) {
	$dir = $_GET["direction"];
} // end if

$time = time();
$text = "ERROR: Unspecified med-tent event";

if ( $dir == 3 ) { $text = "Runner entered med tent"; }
if ( $dir == 4 ) { $text = "Runner left med tent"; }

$pre = query("SELECT * FROM persondata WHERE personid=$id AND datatype=1");
if ( count($pre) == 0 ) {
	$qq = "INSERT INTO persondata VALUES ( $id, 1, $dir );";
} else {
	$qq = "UPDATE persondata SET value='$dir' WHERE personid=$id AND datatype=1;";
} // end if

$q = "BEGIN;\n INSERT INTO updatesequence VALUES ( $id, $time, '$mycall', 0, '$text' );\n" . $qq . "COMMIT;";

$r = query($q);

// echo "<pre>" . $q . "</pre>";

$foo = $_SERVER["SCRIPT_NAME"];
$bar = str_replace("mtquick.php", "detail.php?id=$id", $foo);
header("Location: http://" . $_SERVER["HTTP_HOST"] . "/" . $bar);
?>
