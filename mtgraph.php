<?php
header("Content-type: image/png");

$num = ceil(1.25*$_GET["num"]);
$cmd = "curl -s \"http://10.145.67.1:8080/render/?width=384&height=128&target=trivnet.inmedtent&from=-2hours&yStep=1&lineMode=connected&hideLegend=true&yMin=0&yMax=$num\"";
passthru($cmd);

?>