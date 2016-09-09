<?php
header("Content-type: image/png");

// file_get_contents("http://10.145.67.1:8080/render/?width=384&height=128&target=trivnet.inmedtent&from=-2hours&yStep=1&lineMode=connected&hideLegend=true");

passthru("curl -q \"http://10.145.67.1:8080/render/?width=384&height=128&target=trivnet.inmedtent&from=-2hours&yStep=1&lineMode=connected&hideLegend=true\"");

?>
