<?php 

	echo "<div id=\"tabs1\">\n";
	echo "<b>Data types:</b><br>\n";

	echo "<table>\n";
	echo "<tr><th>id</th><th>&nbsp;</th><th>name</th><th>Label</th><th>Match Type</th><th>&nbsp;</th></tr>";

	$q = "SELECT * FROM datatypes ORDER BY typeid";
	$r = query($q);

	if ( count( $r ) > 0 ) {
		foreach ( $r as $z ) {
			unset($delMsg);
			unset($x); unset($y); unset($w);

			$flag = "";
			if ( $z["exact"] == "t" ) { $match = "Exact"; } else { $match = "Substring"; }
			if ( $z["typeid"] == $config["multidefault"] ) { $flag = "🔑"; } // U+1F511
			if ( $z["typeid"] == $config["message"] ) { $flag = "🔊"; }  // U+1F50A
			if ( $z["typeid"] == $config["status"] ) { $flag = "ℹ️"; }	// U+FE0F
			$_en = $z["enum"];

			echo "<tr><td>" . $z["typeid"] . "</td><td>$flag</td><td><code>" .  $z["name"] . "</code></td><td><b>" . $z["label"] . "</b></td><td>$match</td>\n";

			// Some types are special; don't let users try to delete them.

			$x = query("SELECT COUNT(value) as num FROM persondata WHERE datatype=" . $z["typeid"]);
			$y = query("SELECT COUNT(updatetype) as num FROM async WHERE updatetype=" . $z["typeid"]);
			$w = query("SELECT COUNT(searchtype) as num FROM async WHERE searchtype=" . $z["typeid"]);

			if ( ($z["typeid"] == $config["multidefault"]) || ($z["typeid"] == $config["status"]) || ($z["typeid"] == $config["message"]) ) {
				$delMsg = "[reserved]";
			}  else if ( ($x[0]["num"] > 0) || ($y[0]["num"] > 0) || ($w[0]["num"] > 0) ) {
				$delMsg = "[in use]";
			} // end if

			if ( isset($delMsg) ) {
				echo "<td>$delMsg</td>\n";
			} else {
				echo "<td>[<a href=\"admin.php?mode=datatypes&deletetype=" . $z["typeid"] . "\">delete</a>]</td>\n";
			} // end if

			// Handle enumerated types here

			if ( 't' == $_en ) {

			echo "<tr><td colspan=2>&nbsp;</td><td colspan=5><table width=\"100%\" border=1>\n";
				echo "<tr><th>ID</th><th colspan=2>Value</th></tr>\n";
				$_entable = query("SELECT id, value FROM enumtypes WHERE datatype=" . $z["typeid"] . " ORDER BY id");
				foreach( $_entable as $_enum ) {
					$id = $_enum["id"];

					// Check to see if these are in use, so we prevent deletion
					$dt = $z["typeid"];
					$query = "SELECT COUNT(persondata) FROM persondata WHERE datatype='$dt' AND value='$id'";
					$q = query($query);
					if ( ( $dt == $config["status"] ) && ( $id == $config["medtentstatus"] ) ) {
						echo "<tr><td>" . $_enum["id"] . "</td><td>" . $_enum["value"] . "</td><td><img src=\"symbol/11\"></td></tr>";
					} else if ( $q[0]["count"] > 0 ) {
						echo "<tr><td>" . $_enum["id"] . "</td><td>" . $_enum["value"] . "</td><td>[in use]</td></tr>";
					} else {
						echo "<tr><td>" . $_enum["id"] . "</td><td>" . $_enum["value"] . "</td>";
						echo "<td><a href=\"admin.php?mode=enumtypes&enumid=" . $_enum["id"] . "&enumdt=" . $z["typeid"] . "\">[X]</a></td></tr>";
					} // end if
				} // end foreach

				$enumid = ++$id;
				echo "<form action=\"admin.php?mode=enumtypes\" method=POST>";
				echo "<tr><td><i>$enumid</i></td>";
				echo "<td>";
				echo "<input type=hidden name=\"enumdt\" value=\"" . $z["typeid"] . "\">";
				echo "<input type=hidden name=\"enumid\" value=\"$enumid\">";
				echo "<input name=\"enumval\">";
				echo "</td><td>";
				echo "<input type=submit value=\"Add\">";
				echo "</td></form></tr>";
			echo "</table></td></tr>\n";

			} // end if

			echo "</tr>" . "\n";
		} // end foreach
	} // end if

	$flag = "🔑";
	echo "</table>";
	//	echo "<hr>";
	echo "<form method=\"POST\" action=\"admin.php?mode=datatypes\">";

	echo "<em>Add datatype:</em>";
	echo "short name: <input name=\"addtype_name\" size=12>";
	echo "label: <input name=\"addtype_label\" size=16>";
	echo "<input name=\"exact\" id=\"exact\" type=checkbox value=\"true\"><label for=\"exact\">Exact Match</label><br>\n";

	echo "<br>";
	echo "<input type=\"submit\">";
	echo "</form>";

	echo "<br>";
	echo "<li>🔑 indicates Multi-Edit search key datatype\n";
	echo "<li>ℹ️ indicates Status datatype\n";
	echo "<li>🔊 indicates Message datatype\n";
	echo "</div>\n";

?>
