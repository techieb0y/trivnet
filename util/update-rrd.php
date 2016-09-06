<?php
require_once("/var/www/trivnet/include/db_ops.inc");
require_once("/var/www/trivnet/include/config.inc");

global $config;

$s = $config["status"];
$v = $config["medtentstatus"];

$inmedtentq = "SELECT count(value) as count FROM persondata WHERE datatype='$s' AND value='$v'";
// echo $inmedtentq . "\n";
$r = query($inmedtentq)[0]["count"];
// echo "Got: $r\n";

$run = "rrdtool update /var/www/trivnet/medtent.rrd N:$r";
echo $run . "\n";
exec($run);
?>
