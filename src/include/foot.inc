
	<table class="menu">
	<tr class="menu">
	<?php
		$tabs["index.php"] = "Home";
		$tabs["query.php"] = "Search";
		$tabs["multiedit.php"] = "Multi-Edit";
		// $tabs["turboedit.php"] = "Turbo Edit";
		$tabs["batch.php"] = "Batch Edit";
		$tabs["messaging.php"] = "Messaging";
		$tabs["admin.php"] = "Admin";

		foreach ( $tabs as $taburl => $tabname ) {
			if ( strpos( $_SERVER["PHP_SELF"], $taburl ) ) { 
				echo "<td> <a class=\"menu activetab\" href=\"$taburl\">$tabname</a></td>";
			} else {
				echo "<td> <a class=\"menu\" href=\"$taburl\">$tabname</a></td>";
			}
		}
	?>
	</tr>
	</table>
	
	<!-- end foot.inc -->
<?php
	global $begin;
	$delta = microtime(true) - $begin;
	// syslog(LOG_DEBUG, "Page output in $delta msec");
	sendFullMetric("trivnet.page.time", $delta);
?>
