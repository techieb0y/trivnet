
	<table class="menu">
	<tr class="menu">
		<td> <a class="menu" href="index.php">Home</a> </td>
		<td> <a class="menu" href="query.php">Search</a> </td>
		<td> <a class="menu" href="multiedit.php">Multi-Edit</a> </td>
		<td> <a class="menu" href="turboedit.php">Turbo Edit</a> </td>
		<td> <a class="menu" href="batch.php">Batch Edit</a> </td>
		<td> <a class="menu" href="messaging.php">Messaging</a> </td>
		<td> <a class="menu" href="admin.php">Admin</a> </td>
	</tr>
	</table>

	<!-- end foot.inc -->
<?php
	global $begin;
	$delta = microtime(true) - $begin;
	// syslog(LOG_DEBUG, "Page output in $delta msec");
	sendFullMetric("trivnet.page.time", $delta);
?>
