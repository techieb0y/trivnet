<?php 

	// -------- Race head/tail position ------------
    echo "<div id=\"tabs4\">\n";
    echo "<b>Race head and tail positions:</b>\n";

	$q = "SELECT * FROM race ORDER BY raceid ASC";

    $res = pg_query( connect(), $q );
    $r = array();

	if ( pg_num_rows($res) > 0 ) {
		while ( $z = pg_fetch_assoc($res) ) {
			$r[] = $z;
		} // end while

		echo "<form method=\"POST\" action=\"admin.php?mode=headtail\">";
		echo "<table>\n";
			foreach ( $r as $z ) {
				echo "<tr><td>" . $RACENAME[ $z["raceid"] ] . "</td>";
				$t = "tail[" . $z["raceid"] . "]";
				$h = "head[" . $z["raceid"] . "]";
				echo "<td>Tail: <input value=\"" . $z["tail"] . "\" name=\"$t\" size=4>";
				echo "<td>Head: <input value=\"" . $z["head"] . "\" name=\"$h\" size=4>";
				echo "</td></tr>\n";
			} // end foreach
		echo "<tr><td><input type=\"submit\"></td></tr>\n";
		echo "</table></form>";
	} else {
		echo "<form method=\"POST\" action=\"admin.php?mode=headtailinit\">";
		echo "<input type=submit value=\"Initialize\">";
		echo "</form>";
	}

	echo "</div>\n";
?>
