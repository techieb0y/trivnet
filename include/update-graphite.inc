<?php
require_once("/var/www/trivnet/include/db_ops.inc");
require_once("/var/www/trivnet/include/config.inc");
require_once("/var/www/trivnet/include/send-metric.inc");

function updateMT() {
	global $config;

	$s = $config["status"];
	$v = $config["medtentstatus"];

	$inmedtentq = "SELECT count(value) as count FROM persondata WHERE datatype='$s' AND value='$v'";
	$r = query($inmedtentq)[0]["count"];
	sendFullMetric("trivnet.inmedtent", $r);
}

function updateFinished() {
	global $config;
	$done = query("select count(value) as done from persondata where datatype=" . $config["status"] . " and value='2'")[0]["done"];
	$total = query("select count(id) as total from people")[0]["total"];
	$percent = floor(100 * ($done/$total));
	sendFullMetric("trivnet.finishedRunners", $done);
	sendFullMetric("trivnet.finishedPercent", $percent);
}

?>
