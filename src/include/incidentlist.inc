<?php 

require_once("include/head.inc");
require_once("include/config.inc");
require_once("include/db_ops.inc");

$q = 'SELECT * FROM incidents ORDER BY id DESC';
$r = query($q);

echo "<table>\n";
echo "<tr><th>Incident</th><th>Status</th></tr>\n";

foreach ( $r as $row ) {	
	$id = $row["id"];
	$title = $row["title"];
	$status = $row["status"];
	echo "<tr><td><a href=\"incident.php?id={$id}\">{$title}</td><td>{$status}</td></tr>\n";
} // end foreach

echo "</table>\n";

require_once("include/foot.inc");
?>
