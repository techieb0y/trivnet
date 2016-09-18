<?php 

	// -------- QuickMesg Stuff ------------
    echo "<div id=\"tabs5\">\n";
    echo "<b>Preset messages:</b>\n";

	$q = "SELECT * FROM quickmesg ORDER BY text ASC";
	$r = query($q);

	echo "<form method=\"POST\" action=\"admin.php?mode=quickmesg\">";
	echo "<table>\n";
	if ( count( $r ) > 0 ) {
		foreach ( $r as $z ) {
			echo "<tr><td>" .  $z["text"] . "</td>";
			echo "<td>[<a href=\"admin.php?mode=quickmesg&delquick=" . urlencode($z["text"]) . "\">delete</a>]</td></tr>\n";
		} // end foreach
	} // end if

	echo "<tr><td> <input name=\"addquick\" size=64> </td><td>[add new]</td></tr>\n";

	echo "</table>";

	echo "<br>";
	echo "<input type=\"submit\">";
	echo "</form>";

	echo "</div>\n";

?>
