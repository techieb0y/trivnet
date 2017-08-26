<?php

	// $q = "SELECT * FROM messages WHERE ( dest='$mycall' OR dest='all') ORDER BY timestamp DESC LIMIT 5";
	$q = "SELECT messages.callsign, messages.dest, messages.message, to_timestamp(messages.timestamp) AS timestamp, sessions.tactical FROM messages,sessions WHERE messages.callsign=sessions.callsign AND ( dest='$mycall' OR dest='all') ORDER BY timestamp DESC LIMIT 5";
	$r = query($q);
	if ( count($r) > 0 ) {
		echo "<div align=\"center\"><table class=\"messages\">\n";
		$rc = 0;
		foreach( $r as $row ) {
			if ( $row["dest"] == $mycall ) {
				echo "<tr class=\"tome\"><td>";
			} else if ( ($rc % 2) == 0 ) {
				echo "<tr class=\"msgItem evenRow\"><td>";
			} else {
				echo "<tr class=\"msgItem oddRow\"><td>";
			} // end if

			echo $row["callsign"] . " &rarr; " . $row["dest"] . "<br>" . $row["tactical"] . "</td><td>" . $row["message"] . "</td><td>" . $row["timestamp"] . "</td></tr>\n";
			$rc++;
		} // end foreach
		echo "</table></div>\n";
	} // end if
?>
