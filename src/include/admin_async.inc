<?php 

	// -------- Async Stuff ------------
	echo "<div id=\"tabs3\">\n";
	echo "Recent async jobs:<br>\n";

	$q = "SELECT * from async ORDER BY jobid DESC";
	$r = query($q);
	if ( count($r) > 0 ) {
		$q_dt = "SELECT * from datatypes";
		$r_dt = query($q_dt);

		foreach ( $r_dt as $row_dt ) {
			$id = $row_dt["typeid"];
			$label = $row_dt["label"];
			$types[$id] = $label;	
			$isenum[$id] = $row_dt["enum"];
		}

		echo "<table width=\"80%\" border=0>\n";
		echo "<tr><th>Job ID</th><th>Filename</th><th>Owner</th><th>Search on</th><th>Update</th><th>Data</th><th>Job State</th><th>Progress</th><th>Timestamp</th></tr>\n";
		foreach ($r as $row) {
			$ts = date("Y-m-d H:i:s", $row["timestamp"]);
			$stat = $row["status"];

			if ( "t" == $isenum[ $row["updatetype"] ] ) {
				$data = query( "SELECT value from enumtypes WHERE datatype=" . $row["updatetype"] . " AND id=" . $row["data"] )[0]["value"];
			} else {
				$data = $row["data"];
			} // end if

			if ( preg_match("/^IMPORT/", $row["data"] ) ) { 
				printf( "<tr><td>%s</td><td><a href=\"showasync.php?file=%s\">%s</a></td><td>%s</td><td><em>n/a</em></td><td><em>n/a</em></td><td><em>Bulk Data Import</em></td><td><a href=\"showasync.php?file=%s.err\">%s</a></td><td>%s%%</td><td>%s</td></tr>\n", $row["jobid"], $row["filename"], $row["filename"], $row["callsign"],$row["filename"], $jobstate[$row["state"]], $row["progress"], $ts );
			} else if ( $row["state"] == 0 || $row["state"] == 5 ) {		
				printf( "<tr><td>%s</td><td><a href=\"showasync.php?file=%s\">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"showasync.php?file=%s.err\">%s</a></td><td>%s%%</td><td>%s</td></tr>\n", $row["jobid"], $row["filename"], $row["filename"], $row["callsign"], $types[$row["searchtype"]], $types[$row["updatetype"]], $data, $row["filename"], $jobstate[$row["state"]], $row["progress"], $ts );
			} else {
				printf( "<tr><td>%s</td><td><a href=\"showasync.php?file=%s\">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s%%</td><td>%s</td></tr>\n", $row["jobid"], $row["filename"], $row["filename"], $row["callsign"], $types[$row["searchtype"]], $types[$row["updatetype"]], $data, $jobstate[$row["state"]], $row["progress"], $ts );
			}
		} // end forearch
		echo "</table>\n";
	} else {
		echo "<em>There are no async jobs in the system.</em>\n";
	} // end if

	echo "<br>\n";

	if ( file_exists("/var/www/trivnet/jobs/asyncEngine.stat") ) {
		$info = stat("/var/www/trivnet/jobs/asyncEngine.stat");
		$tm = $info["atime"];
		$delta = time() - $tm;
		echo "Last Async Engine checkin was $delta seconds ago.<br>\n";
	} else {
		echo "No checkin file from Async Engine exists.<br>\n";
	} // end if 

	echo "</div>";
?>
