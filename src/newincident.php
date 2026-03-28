<?php
session_start();

include("include/db_ops.inc");

$r = query("SELECT MAX(id) + 1 AS next FROM incidents;");
$next =  $r[0]["next"];

$title = pg_escape_string(connect(), $_POST["title"]);
$text = "Incident \"{$title}\" opened";
$q = "BEGIN; INSERT INTO incidents VALUES ( '$next', '$title', 'open' ); INSERT INTO incidentsequence VALUES ({$next}, extract(epoch from NOW()), '{$text}' ); COMMIT;";
$r = query($q);

header("Location: incident.php?id={$next}");
?>
