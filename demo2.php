<?php

$rawData = file_get_contents('php://input');
$data = json_decode($rawData);
$f = fopen("/tmp/demo2-" . date("U"), "w+");

fwrite($f, "Raw data:\n");
fwrite($f, $rawData );

fwrite($f, "\n");

fwrite($f, "Parsed data:\n");
fwrite($f, print_r($data, true) );

fwrite($f, "\n");


$id = $data->id;

$ret["id"] = $id;
$ret["result"] = "OK";

fwrite($f, "Return array:\n" . print_r($ret, true) . "\n" );

$return = json_encode($ret);
echo $return;
fwrite($f, "Returning:\n");
fwrite($f, $return);
fwrite($f, "\n");
fflush($f);
fclose($f);
?>
