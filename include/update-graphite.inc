<?php
require_once("/var/www/trivnet/include/db_ops.inc");
require_once("/var/www/trivnet/include/config.inc");


function updateMT() {
global $config;

$s = $config["status"];
$v = $config["medtentstatus"];

$inmedtentq = "SELECT count(value) as count FROM persondata WHERE datatype='$s' AND value='$v'";
// echo $inmedtentq . "\n";
$r = query($inmedtentq)[0]["count"];
// echo "Got: $r\n";

// $run = "rrdtool update /var/www/trivnet/medtent.rrd N:$r";
// echo $run . "\n";
// exec($run);

$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
socket_connect($socket, "localhost", 2003) or die("Could not open socket to carbon.");

$now = time();
$data = "trivnet.inmedtent $r $now\n" ;
$bytes = socket_write($socket, $data);
// echo "Wrote $bytes bytes.\n";
socket_close($socket);
}

?>
