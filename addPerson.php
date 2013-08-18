<?php
require_once("include/db_ops.inc");
require_once("include/config.inc");
require_once("include/constants.inc");

connect();

// Create a new Person, and immediatly jump to their details screen

$r = query("SELECT MAX(id) + 1 AS nextID FROM people;");
$next =  $r[0]["nextid"];
echo "Got: $next";
$s = query("INSERT INTO people VALUES ( '$next' )");

header("Location: detail.php?id=$next");
?>
