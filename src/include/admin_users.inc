<?php 

	echo "<div id=\"tabs10\">\n";
	
	echo "<table border=1 width=\"66%\">";
	echo "<tr><th>ID</th> <th>Username</th>";
	echo "<th>Display Name</th>";
	echo "<th>Status</th>";
	echo "<th>Batch</th>";
	echo "<th>Admin</th>";
	echo "</tr>\n";

	$q = "SELECT * from users ORDER BY id ASC";
	$r = query($q);

	foreach ( $r as $who ) {
		echo "<tr>\n";
		echo "<td>" . $who["id"] . "</td>\n";
		echo "<td>" . $who["username"] . "</td>\n";
		echo "<td>" . $who["display"] . "</td>\n";

		echo "<td>";
			if ( $who["auth"] & $FLAGS["DISABLED"] ) { echo "⛔️ Disabled"; } else { echo "✅ Enabled"; }
		echo "</td>\n";

		echo "<td>";
			if ( $who["auth"] & $FLAGS["BATCH"] ) { echo "✅ Allowed"; } else { echo "⛔️ Denied"; }
		echo "</td>\n";

		echo "<td>";
			if ( $who["auth"] & $FLAGS["ADMIN"] ) { echo "✅ Admin"; } else { echo "⛔️ Not Admin"; }
		echo "</td>\n";

		echo "</tr>\n";
	} // end foreach
	echo "</table>";

	echo "</div></div>";

?>
