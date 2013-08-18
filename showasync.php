<?php
header("Content-type: text/plain");
$file = $_GET["file"];
readfile($file);
flush();
?>
