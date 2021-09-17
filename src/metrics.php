<?php
header("Content-type: text/plain");



ini_set( "include_path", "./include/" );
require_once("include/db_ops.inc");
require_once("include/config.inc");

$status = $config["status"];

$qh = "SELECT id,value FROM enumtypes WHERE datatype=${status} ORDER BY value";
$rh = query($qh);
foreach ( $rh as $row ) {
        $id= $row["id"];
        $value= $row["value"];
        $theBigArray[$id] = $value;
} // end foreach


$q = "SELECT value, count(value) as cnt FROM persondata WHERE datatype=${status} GROUP BY value";
$inmed = query($q);

foreach($inmed as $m) {
        $id = $m["value"];
        $label = $theBigArray[$id];
        echo "trivnet_status{label=\"${label}\"}        " . $m["cnt"]  . "\n";
}

?>