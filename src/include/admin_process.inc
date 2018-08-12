<?php 

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
	} else if ( "headtailinit" == $mode ) {
		foreach ( $RACEID as $k => $r ) {
			query("INSERT INTO race VALUES ( $k, 0, 0 );");
			header("Location: http://" . $_SERVER["HTTP_HOST"] . "/" . $_SERVER["SCRIPT_NAME"]);
		}
	} else if ( "headtail" == $mode ) {
		echo "<pre>";
		print_r($_POST);

		echo "\n\n";

		$matrix = array();
		foreach ($_POST["head"] as $race => $pos) {
			$q = "UPDATE race SET head=$pos WHERE raceid=$race;";
			echo $q . "\n";
		}
		
		$matrix = array();
		foreach ($_POST["tail"] as $race => $pos) {
			$q = "UPDATE race SET tail=$pos WHERE raceid=$race;";
			echo $q . "\n";
		}

		echo "</pre>";
	} else {
		die("In invalid operation mode was selected.");
	} // end if

?>
