<?php
require_once("include/db_ops.inc");
require_once("include/config.inc");
require_once("include/constants.inc");

connect();

// Create a new Person, and immediatly jump to their details screen

global $config;
$type = $config["multidefault"];

$r = query("SELECT MAX(id) + 1 AS nextID FROM people;");
$next =  $r[0]["nextid"];
echo "Got: $next";
$s = query("BEGIN; INSERT INTO people VALUES ( '$next' ); INSERT INTO persondata VALUES ( $next, $type, null ); COMMIT;");

header("Location: detail.php?id=$next");
?>
