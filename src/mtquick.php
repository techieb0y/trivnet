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

if ( $dir == $config["medtentstatus"] ) { $text = "Runner entered med tent"; }
if ( $dir == $config["lefttentstatus"] ) { $text = "Runner left med tent"; }

$mtdt = $config["status"];
$msgdt = $config["message"];

$pre = query("SELECT * FROM persondata WHERE personid=$id AND datatype=$mtdt");
if ( count($pre) == 0 ) {
	$qq = "INSERT INTO persondata VALUES ( $id, $mtdt, $dir );";
} else {
	$qq = "UPDATE persondata SET value='$dir' WHERE personid=$id AND datatype=$mtdt;";
} // end if

$medTentLatchID = $config["medtendlatchid"];

$q = "BEGIN;\n INSERT INTO updatesequence VALUES ( $id, $time, '$mycall', $msgdt, '$text' );\n INSERT INTO latchlog VALUES ($id, $medTentLatchID) on conflict do nothing;\n" . $qq . "COMMIT;";
$r = query($q);

// echo "<pre>" . $q . "</pre>";

header("Location: detail.php?id=$id");
?>
