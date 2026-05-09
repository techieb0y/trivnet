<?php
session_start();

include("include/db_ops.inc");

$r = query("SELECT COALESCE((SELECT MAX(id) FROM incidents), 1) as next;");
$next =  $r[0]["next"];

error_log("Next incident ID is {$next}");

$title = pg_escape_string(connect(), $_POST["title"]);
$text = "Incident \"{$title}\" opened";
$q = "BEGIN; INSERT INTO incidents VALUES ( '{$next}', '{$title}', 'open' ); INSERT INTO incidentsequence VALUES ('{$next}', extract(epoch from NOW()), '{$text}' ); COMMIT;";

error_log($q);

$r = query($q);

header("Location: incident.php?id={$next}");
?>
