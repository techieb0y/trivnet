<?php
session_start();

include("include/db_ops.inc");

$q = 'UPDATE incidents SET status=\'closed\' WHERE id=$1';
$p[0] = $_POST["id"];
$r = pg_query_params( connect(), $q, $p );

header("Location: index.php");
?>
