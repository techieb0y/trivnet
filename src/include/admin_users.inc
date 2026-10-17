<?php 

	echo "<div id=\"tabs10\">\n";
	
	echo "<table border=1 width=\"66%\">";
	echo "<tr><th>ID</th> <th>Username</th> <th>Display Name</th></tr>\n";

	$q = "SELECT * from users ORDER BY id ASC";
	$r = query($q);

	foreach ( $r as $who ) {
		echo "<tr><td>" . $who["id"] . "</td><td>" . $who["username"] . "</td><td>" . $who["display"] . "</td></tr>\n";
	} // end foreach
	echo "</table>";

	echo "</div></div>";

?>
