<?php 

// This is the admin page

require_once("include/db_ops.inc");
require_once("include/config.inc");

if ( isset($_GET["mode"]) ) {
	$mode = $_GET["mode"];

	if ( "datatypes" == $mode ) {
		if ( isset($_GET["deletetype"]) && $_GET["deletetype"] != "NULL" )	{	
			$q = "DELETE FROM datatypes WHERE typeid=" . $_GET["deletetype"];
			$r = query($q);
			header("Location: http://" . $_SERVER["HTTP_HOST"] . "/" . $_SERVER["SCRIPT_NAME"]);
		} // end if

		if ( count($_POST) > 0 ) {
			if ( isset($_POST["addtype_name"]) && isset($_POST["addtype_label"]) && ( strlen( $_POST["addtype_name"] ) > 0 ) && ( strlen( $_POST["addtype_label"]) > 0 ) ) {	
				$q = "SELECT 1+MAX(typeid) AS newid FROM datatypes";
				$r = query($q);
				$num = $r[0]["newid"];
				if ( !isset($num) ) { $num = 0; }

				if ( isset($_POST["exact"]) && ( $_POST["exact"] != "true" ) ) { $exact="false"; }
				if ( !isset($_POST["exact"]) ) { $exact="false"; } else { $exact="true"; }

				$q = "INSERT INTO datatypes VALUES ( $num, '" . $_POST["addtype_name"] . "', '" . $_POST["addtype_label"] . "', $exact)";
				$r = query($q);
			} // end if

			// Send back
			header("Location: http://" . $_SERVER["HTTP_HOST"] . "/" . $_SERVER["SCRIPT_NAME"]);
		} // end if
	} else if ( "enumtypes" == $mode ) {
		if ( isset($_GET["enumdt"]) && isset($_GET["enumid"]) ) {
			// Delete an enumerated value
			$_dt = $_GET["enumdt"];
			$_id = $_GET["enumid"];
			$query = "DELETE FROM enumtypes WHERE id=$_id and datatype=$_dt";
			query($query);
			header("Location: http://" . $_SERVER["HTTP_HOST"] . "/" . $_SERVER["SCRIPT_NAME"]);
		} else if ( isset($_POST["enumval"]) && isset($_POST["enumdt"]) && isset($_POST["enumid"]) ) { 
			// echo "<pre>"; print_r($_POST); echo "</pre>";
			// Add an enumerated value
			$value = $_POST["enumval"];
			$_id = $_POST["enumid"];
			$dt = $_POST["enumdt"];
			$query="INSERT INTO enumtypes VALUES ($_id, $dt, '$value')";
			query($query);
			// echo $query;
			header("Location: http://" . $_SERVER["HTTP_HOST"] . "/" . $_SERVER["SCRIPT_NAME"]);
		} // end if
	} else if ( "quickmesg" == $mode ) {
		$q = "BEGIN;\n";
		if ( isset($_POST["addquick"]) && strlen($_POST["addquick"]) > 0 ) {
			// Do an add
			$q .= "INSERT INTO quickmesg VALUES ( '" . $_POST["addquick"] . "');\n";
		} // end if

		if ( isset($_GET["delquick"]) && $_GET["delquick"] != "NULL" ) {
			// Do an remove
			$q .= "DELETE FROM quickmesg WHERE text='" . $_GET["delquick"] . "';\n";
		} // end if
		$q .= "COMMIT;";

		$r = query($q);

		// Send back
		header("Location: http://" . $_SERVER["HTTP_HOST"] . "/" . $_SERVER["SCRIPT_NAME"]);
	} else {
		die("In invalid operation mode was selected.");
	} // end if

} else {
	// No mode, show the form
	require_once("include/head.inc");

	echo "<link rel=\"stylesheet\" href=\"/trivnet/css/flick/jquery-ui-1.8.24.custom.css\">\n";
	echo "<script src=\"/trivnet/js/jquery.js\"></script>\n";
	echo "<script src=\"/trivnet/js/jquery-ui-1.8.24.custom.min.js\"></script>\n";

	echo "
		<script>
		$(function() {
				$( \"#tabs\" ).tabs();
				});
	</script>
		";

	echo "<div id=\"tabs\">\n";
	echo "<ul>\n";
	echo "<li><a href=\"#tabs1\">Data Types</a></li>\n";
	echo "<li><a href=\"#tabs5\">Quick Status Messages</a></li>\n";
	echo "<li><a href=\"#tabs2\">Bulk Import</a></li>\n";
	echo "<li><a href=\"#tabs3\">Async Jobs</a></li>\n";
	echo "<li><a href=\"#tabs9\">Debug Info</a></li>\n";
	echo "</ul>\n";

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
			if ( $z["typeid"] == $config["multidefault"] ) { $flag = "<img src=\"images/key.png\">"; }
			$_en = $z["enum"];

			echo "<tr><td>" . $z["typeid"] . "</td><td>$flag</td><td><code>" .  $z["name"] . "</code></td><td><b>" . $z["label"] . "</b></td><td>$match</td>\n";

			// Some types are special; don't let users try to delete them.

			$x = query("SELECT COUNT(value) as num FROM persondata WHERE datatype=" . $z["typeid"]);
			$y = query("SELECT COUNT(updatetype) as num FROM async WHERE updatetype=" . $z["typeid"]);
			$w = query("SELECT COUNT(searchtype) as num FROM async WHERE searchtype=" . $z["typeid"]);

			if ( ($z["typeid"] == $config["multidefault"]) || ($z["typeid"] == 0) ) {
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
					if ( $q[0]["count"] > 0 ) {
						echo "<tr><td>" . $_enum["id"] . "</td><td>" . $_enum["value"] . "</td>";
						echo "<td>[in use]</td></tr>";
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

	$flag = "<img src=\"images/key.png\">";
	echo "</table>";
	echo "<br>$flag indicates Multi-Edit search key datatype.<br>\n";
	//	echo "<hr>";
	echo "<form method=\"POST\" action=\"admin.php?mode=datatypes\">";

	echo "<em>Add datatype:</em>";
	echo "short name: <input name=\"addtype_name\" size=12>";
	echo "label: <input name=\"addtype_label\" size=16>";
	echo "<input name=\"exact\" id=\"exact\" type=checkbox value=\"true\"><label for=\"exact\">Exact Match</label><br>\n";

	echo "<br>";
	echo "<input type=\"submit\">";
	echo "</form>";
	echo "</div>\n";

	echo "<div id=\"tabs5\">\n";
	echo "<b>Preset messages:</b>\n";

	// -------- QuickMesg Stuff ------------

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

	// -------- Bulk Import Stuff ------------
	echo "<div id=\"tabs2\">\n";

	$dir = opendir("csvdata") or die("Could not open CSV data directory");
	while ( $f = readdir($dir) ) {
		$filelist[] = $f;
	} // end while

	sort($filelist);
	echo "<form action=\"import2.php\" method=\"post\" enctype=\"multipart/form-data\">\n";
	echo "Data filename: ";
	echo "<input type=\"file\" name=\"persondata\"> (or select existing: ";
	echo "<select name=\"csvfile\">\n";
	foreach( $filelist as $f ) {
		if ( is_file("csvdata/" . $f) && is_readable("csvdata/" . $f) && $f != "." && $f != ".." ) {
			echo "<option>$f\n";
		} // end if
	} // end foreach
	echo "</select>)<br>\n";
	echo "Data delimiter: ";
	echo "<select name=\"delim\">";
	echo "<option value=\"0\">Determine Automatically";
	echo "<option value=\"comma\">Comma";
	echo "<option value=\"tab\">Tab";
	echo "<option value=\"pipe\">Pipe";
	echo "</select><br>\n<input value=\"Preview data\" type=\"submit\">";
	echo "</form>";
	echo "</div>\n";

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
		}
		$types[0] = "Message";

		echo "<table width=\"80%\" border=0>\n";
		echo "<tr><th>Job ID</th><th>Filename</th><th>Owner</th><th>Search on</th><th>Update</th><th>Data</th><th>Job State</th><th>Progress</th><th>Timestamp</th></tr>\n";
		foreach ($r as $row) {
			$ts = date("Y-m-d H:i:s", $row["timestamp"]);
			$stat = $row["status"];

			if ( preg_match("/^IMPORT/", $row["data"] ) ) { 
				printf( "<tr><td>%s</td><td><a href=\"showasync.php?file=%s\">%s</a></td><td>%s</td><td><em>n/a</em></td><td><em>n/a</em></td><td><em>Bulk Data Import</em></td><td><a href=\"showasync.php?file=%s.err\">%s</a></td><td>%s%%</td><td>%s</td></tr>\n", $row["jobid"], $row["filename"], $row["filename"], $row["callsign"],$row["filename"], $jobstate[$row["state"]], $row["progress"], $ts );
			} else if ( $row["state"] == 0 || $row["state"] == 5 ) {		
				printf( "<tr><td>%s</td><td><a href=\"showasync.php?file=%s\">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"showasync.php?file=%s.err\">%s</a></td><td>%s%%</td><td>%s</td></tr>\n", $row["jobid"], $row["filename"], $row["filename"], $row["callsign"], $types[$row["searchtype"]], $types[$row["updatetype"]], $row["data"], $row["filename"], $jobstate[$row["state"]], $row["progress"], $ts );
			} else {
				printf( "<tr><td>%s</td><td><a href=\"showasync.php?file=%s\">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s%%</td><td>%s</td></tr>\n", $row["jobid"], $row["filename"], $row["filename"], $row["callsign"], $types[$row["searchtype"]], $types[$row["updatetype"]], $row["data"], $jobstate[$row["state"]], $row["progress"], $ts );
			}
		} // end forearch
		echo "</table>\n";
	} else {
		echo "<em>There are no async jobs in the system.</em>\n";
	} // end if

	echo "<br>\n";

	if ( file_exists("/tmp/asyncEngine.stat") ) {
		$info = stat("/tmp/asyncEngine.stat");
		$tm = $info["atime"];
		$delta = time() - $tm;
		echo "Last Async Engine checkin was $delta seconds ago.<br>\n";
	} else {
		echo "No checkin file from Async Engine exists.<br>\n";
	} // end if 

	echo "</div>";
	echo "<div id=\"tabs9\">\n";
	echo "Session ID: " . session_id() . "<br>\n";
	echo "<pre>";
	print_r($_SESSION);
	echo "</pre>\n";
	// print_r( list_sessions() );
	
	echo "<table border=1 width=\"66%\">";
	echo "<tr><th colspan=2>Callsign</th><th>Tactical Call</th><th>Age (sec)</th><th>Timestamp</th></tr>\n";
	foreach ( list_sessions() as $ses ) {
		if ( $ses["sessionid"] == session_id() ) { $isThisSess = "*"; } else { $isThisSess = ""; }
		echo "<tr><td>$isThisSess</td><td>" . $ses["callsign"] . "</td><td>" . $ses["tactical"] . "</td><td>". sprintf("%.2f", $ses["age"]) . "</td><td>" . $ses["timestamp"] . "</td></tr>\n";
	} // end foreach
	echo "</table>";

	echo "</div></div>";

	require_once("include/foot.inc");

	echo "</body></html>";
} // end if
?>
