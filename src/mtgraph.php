<?php
header("Content-type: image/png");
header("cache-control: private, no-cache");

$num = ceil(1.25*$_GET["num"]);
$step = ceil($num/6);
$cmd = "curl -s \"http://10.145.67.1:8080/render/?width=384&height=128&target=trivnet.inmedtent&from=-2hours&yStep=$step&lineMode=connected&hideLegend=true&yMin=0&yMax=$num\"";
// syslog(LOG_DEBUG, $cmd);
passthru($cmd);

?>
