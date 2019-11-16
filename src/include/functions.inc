<?php

function tn_help($line) {
	if ( count($line) > 1 ) {
		if ( isset(  $help_text[ $line[1] ] ) ) {
			foreach ( $help_text[ $line[1] ] as $help_line) { echo $help_line . "\n\r"; }
		} else {
			echo "Sorry, I couldn't find help about '" . $line[1] . "'.\n\r";
		} // end if
	} else {
		echo "Available commands: (shortcut keys in caps)\n\r";
		echo "   Getting started and moving around:\n\r";
		echo "\tmycall quit exit logout\n\r";
		echo "   Manipulating data:\n\r";
		echo "\tlistfields addfield delfield\n\r"; 
		echo "\tsearch info update batch\n\r"; 
		echo "   Working with others:\n\r";
		echo "\twhoami users tell\n\r";
	} // end if
	$return = "";
} // end help()

function tn_enable($line) {
	$privlevel = 1;
	$prompt = "#";
	$return = "";
} // end enable

function tn_disable($line) {
	$privlevel = 0;
	$prompt = ">";
	$return = "";
}

function tn_addfield($line) {
	// if ( 1 != $privlevel ) { echo "You must be in admin mode to run this.\n\r"; break; }
	// Drop the verb
	array_shift($line);

	if ( ! count($line) > 0 ) {
		echo "addfield [exact | substring] fieldname Description\n";
		return 1;
	}

	$q = "SELECT 1+max(typeid) AS newid FROM datatypes";
	$r = query($q);
	$newid = $r[0]["newid"];

	$type = array_shift($line);
	$_type = "false";
	if ( ! ( ("exact" == $type) || ("substring" == $type) ) ) { echo "Invalid type specified\n"; return 1; }
	if ( "exact" == $type ) { $_type = "true"; }

	$field = array_shift( $line);
	$label = implode( " ", $line );

	$q2 = "INSERT INTO datatypes VALUES ( $newid, '$field', '$label', $_type )";
	$r2 = query($q2);

	return 0;
} // end addfield

function tn_batch($line) {
	// Async batch-processing input function
	// Arguments: [optional] search field name (or take default), [required] update field
	// Prompts: New value for update field
	// Then, one search field data value per line, until 'END' or 'DONE'

	global $mycall;

	// drop the verb
	array_shift($line);

	$parts = count($line);
	echo "Line has $parts elements\n";
	if ( 0 == $parts ) { echo "Required update field not specified.\n"; return(1); }
	if ( $parts > 2 ) { echo "Too many arguments specified.\n"; return(1); }

	if ( 1 == $parts ) {
		// use default search field
		// Get the system default search field, if they don't specify one
			$qd = "SELECT callsign,defsearch FROM defaults WHERE ( callsign='$mycall' OR callsign='none' )";
			$rd = query($qd);

			$update = $line[0];

			$defs = array();
			if ( $rd ) {
				foreach ( $rd as $r ) {
					$cs = trim($r["callsign"]);
					$ds = $r["defsearch"];
					$defs[$cs]["defsearch"] = $ds;
				} // end foreach

				if ( isset( $defs[$mycall]["defsearch"] ) && $defs[$mycall]["defsearch"] != "" ) {
					$field = $defs[$mycall]["defsearch"];
				} else {
					$field = $defs["none"]["defsearch"];
				} // end if
			} else {
				// No defaults set?
				echo "Error, unable to determine default search field\n";
				return(1);
			} // end if
		} else {
			// both types specified
			$field = $line[0];
			$update = $line[1];
		} // end if

		if ( "status" != $update ) {
			$q0 = "SELECT typeid FROM datatypes WHERE name='$update'";
			$r0 = query($q0);
		} else {
			$r0[0]["typeid"] = 0;
		}

		$q1 = "SELECT typeid FROM datatypes WHERE name='$field'";
		$r1 = query($q1);

		global $fp;

		echo "\n\rValue for ${update}>";
		$value = trim( fgets($fp,4094) );

		echo "Updating " . $update . " to \"$value\" for each $field; enter one per line, then \"END\"\n";

		// Open buffer file
		$tmp = tmpfile();

		$counter=0;

		$val = "";
		echo "\n\rbatch($field)>";
		while ( ($val != "END") && !feof($fp) ) {
			$val = trim( fgets($fp,4094) );
			fwrite($tmp, $val . "\n");
			$counter++;
			echo "\n\rbatch($field)>";
		} // end while

		if ($counter == 1) { echo "No search values entered; aborting.\n"; return(1); }
		
		// Get async queue ID
		$q_id = "SELECT nextval('async_jobid_seq') AS jobid";
		$r_id = query($q_id);
		$async_id = $r_id[0]["jobid"];

		// Create job file
		fflush($tmp);
		$jobfile = "./jobs/" . $mycall . "-" . $async_id;
		$h2 = fopen($jobfile, "w+");
		fseek($tmp, 0);
		stream_copy_to_stream($tmp, $h2);

		fclose($h2);
		fclose($tmp);

		// Finalize async queue job
		$q_submit = "INSERT INTO async VALUES ( $async_id, '$jobfile', '$mycall', " . $r1[0]["typeid"] . ", " . $r0[0]["typeid"] . ", '$value', 1)";
		$r_submit = query($q_submit);	
		echo "Batch Job Submitted\n";
		return(0);
} // end batch
	

function tn_delfield($line) {
	// if ( 1 != $privlevel ) { echo "You must be in admin mode to run this.\n\r"; break; }
	$del = $line[1];
	$q = "DELETE FROM datatypes WHERE name='$del'";
	$r = query($q);
} // end delfield

function tn_listfields($line) {
	unset($out);
	unset($label);
	// Header
	$qh = "SELECT * FROM datatypes ORDER BY typeid";
	$rh = query($qh);
	$l = 0; $ll = 0;
	foreach ( $rh as $row ) {
		$typeid = $row["typeid"];
		$name = $row["name"];
		$_ex = $row["exact"];
		if ( strlen($name) > $l ) { $l = strlen($name); }
		$label = $row["label"];
		if ( strlen($label) > $ll ) { $ll = strlen($label); }
		$out[$name] = $label;
		( $_ex == "t" ) ? $ex[$name] = "exact"  : $ex[$name] = "substring";
	} // end foreach

	$l++; $ll++;

	foreach ( $out as $key => $value ) {
		printf("\t%" . $l . "s  %" . $ll . "s  %s\n\r", $key, $value, $ex[$key] );
	} // end foreach
} // end listfields

function tn_search($line) {
	if ( count($line) > 1 ) {
		// Drop the verb
		array_shift($line);

		// search bibnum F204
		// search F204

		// Fetch fields list
		$qh = "SELECT name FROM datatypes";
		$rh = query($qh);
		foreach ( $rh as $row ) { $dt[] = $row["name"]; }

		$field="";

		if ( in_array( $line[0], $dt ) ) {
			// The first thing is a fieldname; search by it
			$field = array_shift( $line);
			echo "Search field: $field\n";
		} else {
			// Didn't recognize first object as a field; use defaults

			// Get the system default search field, if they don't specify one
			global $mycall;
			$qd = "SELECT callsign,defsearch FROM defaults WHERE ( callsign='$mycall' OR callsign='none' )";
			$rd = query($qd);

		$defs = array();
			if ( $rd ) {
				foreach ( $rd as $r ) {
					$cs = trim($r["callsign"]);
					$ds = $r["defsearch"];
					$defs[$cs]["defsearch"] = $ds;
				} // end foreach

			if ( isset( $defs[$mycall]["defsearch"] ) && $defs[$mycall]["defsearch"] != "" ) {
				$field = $defs[$mycall]["defsearch"];
			} else {
				$field = $defs["none"]["defsearch"];
			} // end if
			echo "Search field: $field\n";
			} else {
				// No defaults set?
			} // end if
		} // end if

		$criteria = array_shift( $line);

		$q0 = "SELECT typeid,exact FROM datatypes WHERE name='$field'";
		$r0 = query($q0);
	
		if ( count($r0) > 1 ) { die("Multiple field name match"); }

		if ( $r0[0]["exact"] == "t" ) {
			$q1 = "SELECT personid FROM persondata WHERE datatype=" . $r0[0]["typeid"] . " AND value ilike '" . $criteria . "'";
		} else {
			$q1 = "SELECT personid FROM persondata WHERE datatype=" . $r0[0]["typeid"] . " AND value ilike '%" . $criteria . "%'";
		} // end if

		$q2 = "SELECT * from persondata WHERE personid IN ( $q1 )";
		$theBigArray = array();

		// Header
		$qh = "SELECT * FROM datatypes ORDER BY typeid";
		$rh = query($qh);
		$sln = array(); // Associative array of the longest value length, keyed to the datatype id (used for formatting below)
		foreach ( $rh as $row ) {
			$typeid = $row["typeid"];
			$sln[$typeid] = 0;
		} // end foreach

		foreach ( $rh as $row ) {
			$typeid = $row["typeid"];
			// $name = $row["name"];
			$label = $row["label"];
			$theBigArray[0][$typeid] = $label;
			$n = strlen($label);
			if ( $n > $sln[$typeid] ) { $sln[$typeid] = $n; }
		} // end foreach

		// Get results
		$r = query($q2);
		if ( count($r) > 0 ) {
			foreach($r as $row) {
				$name = $row["datatype"];
				$person = $row["personid"];
				$value = $row["value"];
				$theBigArray[$person][$name] = $value;
				$n = strlen($value);
				if ( $n > $sln[$name] ) { $sln[$name] = $n; }
			} // end foreach
		} // end if

		// Pretty-print results
		foreach ( $theBigArray as $key => $row ) {
			if ( 0 == $key ) { echo "ID\t"; } else { echo $key . "\t"; }
			foreach ( $theBigArray[0] as $k => $f ) {
				// Print fields one at a time, with the max padding length from the previous scan.
				@printf("% -" . $sln[$k] . "s  ", $row[$k]);
			} // end foreach
			echo "\n\r";
		} // end foreach
	} else {
		echo "Usage: search <fieldname> <search string>\n\r";
	} // end if
	unset($theBigArray);
} // end search

function tn_now($line) {
	echo date("r") . "\n\r";
} // end now

function tn_details($line) {
	$who = array_shift($line);

	// Figure out what they mean -- is the first item a personID, or a dataType, or a data value?
	
		// Fetch fields list
		$qh = "SELECT typeid,name,exact FROM datatypes";
		$rh = query($qh);
		foreach ( $rh as $row ) {
			$id = $row["typeid"];
			$dt[$id] = $row["name"];
		} // end foreach

		$field="";
		$handoff=0;

		if ( in_array( $line[0], $dt ) ) {
			// The first thing is a fieldname; search by it
			$field = array_shift( $line);
			$q = "SELECT personid FROM persondata WHERE datatype=" . array_search($field, $dt) . " AND value ilike '%${line[0]}%'"; // FIXME: Injection hole
			$r = query($q);
			if ( count($r) > 0 ) {
				$handoff=$r[0]["personid"];
			}
		} else {
			// Not a fieldname. Direct personID?
			$who = array_shift($line);
			if ( eregi( "[a-z]", $who ) ) {
				// Has letters -- definitely not a personID

				$data = $who; unset($who);
				
				// Get the system default search field, if they don't specify one
				global $mycall;
				$qd = "SELECT callsign,defsearch FROM defaults WHERE ( callsign='$mycall' OR callsign='none' )";
				$rd = query($qd);
		
				foreach ( $rd as $r ) {
					$cs = trim($r["callsign"]);
					$ds = $r["defsearch"];
					$defs[$cs]["defsearch"] = $ds;
				} // end foreach
	
				if ( isset( $defs[$mycall]["defsearch"] ) && $defs[$mycall]["defsearch"] != "" ) {
					$field = $defs[$mycall]["defsearch"];
				} else {
					$field = $defs["none"]["defsearch"];
				} // end if
				echo "Search field: $field\n";

				$q = "SELECT personid FROM persondata where datatype = " . array_search($field, $dt) . " AND value='$data'"; // FIXME: Injection hole
				$r = query($q);
				$handoff = $r[0]["personid"];

			} else {
				// no letters, so maybe it's a personID...
				$q = "SELECT id FROM people WHERE id=$who";
				$r = query($q);

			if ( count($q) == 1 ) {
				// Was a personID, assume it's what they meant
				$handoff=$r[0]["id"];
			} // end if


	
			} // end if
		} // end if

	$q = "SELECT * FROM updatesequence WHERE personid=$handoff ORDER BY timestamp DESC";
	$r = query($q);
	if ( count($r) > 0 ) {
		foreach( $r as $row ) {
			$mesg = $row["value"];
			$who = $row["source"];
			$when = date("r", $row["timestamp"]);
			echo "$when\t$who\t$mesg\n\r";
		} // end foreach
	} // end if
} // end details

function tn_update($line) {
		global $mycall;

		$find = "";		// This is the user-specified search type
		$search = "";	// This is the query string
		$searchexact = "";	// Search query exactness
		$who = "";		// This is the personid we determine to be what the query string gets us
		$what = "";		// This is the type to update
		$value = "";	// This is the value for the thing we said to update

		$searchtype = "";	// This is the datatype that we get from the search type

	if ( count($line) > 1 ) {
		// Drop the verb
		array_shift($line);
		$param1 = array_shift( $line);

		// The first parameter. Is it a known datatype?
		$r = query("SELECT typeid,exact FROM datatypes WHERE name='$param1'");
		if ( count($r) == 0 ) {
			// No match for datatype, so it must be a search string
			$search = $param1;
			unset($param1);
			// So we still don't know what they want to search on; use the defaults.
			$rd = query("SELECT callsign,defsearch FROM defaults WHERE ( callsign='$mycall' OR callsign='none' )");
	
			foreach ( $rd as $r ) {
				$cs = trim($r["callsign"]);
				$ds = $r["defsearch"];
				$defs[$cs]["defsearch"] = $ds;
			} // end foreach

			if ( isset( $defs[$mycall]["defsearch"] ) && $defs[$mycall]["defsearch"] != "" ) {
				$find = $defs[$mycall]["defsearch"];
			} else {
				$find = $defs["none"]["defsearch"];
			} // end if
			$r = query("SELECT typeid,exact FROM datatypes WHERE name='$find'");
			$searchtype = $r[0]["typeid"];
			$searchexact = $r[0]["exact"];
			echo "Search field: $find\n";
		} else {
			// We got a match; assume it's what the user meant
			$searchtype = $r[0]["typeid"];
			$searchexact = $r[0]["exact"];
			// echo "Search field: $param1 ($searchtype)\n";
			$find = $param1;
			unset($param);
			// Single-word of search input
			$search = array_shift($line);
		} // end if

		// So now we know what kind of thing we're looking for.
		// Now, do the search.
		if ( $searchexact == "f" ) {
			$q = "SELECT personid FROM persondata WHERE datatype=$searchtype AND value ilike '%$search%'";
		} else {
			$q = "SELECT personid FROM persondata WHERE datatype=$searchtype AND value='$search'";
		} // end if
		// echo $q . "\n";
		$r = query($q);

		// Sanity checks
		if ( count($r) == 0 ) { echo "No matches; aborting update\n"; return 255; }
		if ( count($r) > 1 ) { echo "Multiple matches; aborting update\n"; return 255; }
	
		// Now we know who we're talking about
		$who = $r[0]["personid"];	

		printf("Searching for a %s of '%s'\n", $find, trim($search));
		// echo "Got personid: $who\n";

		// Now, what do we do with it?
		// Did they enter a datatype?

		$param2 = array_shift($line);
		if ( "status" == $param2 ) {
			// special case handler for did-specify, status message updating
			$what = 0;
		} else {
			$r = query("SELECT typeid FROM datatypes WHERE name='$param2'");
			if ( count($r) == 1 ) {
				// Found a match!
				$what = $r[0]["typeid"];
				$value = implode(" ", $line);
			} else {
				// fall back to the defaults
				$rd = query("SELECT callsign,defupdate FROM defaults WHERE ( callsign='$mycall' OR callsign='none' )");
		
				foreach ( $rd as $r ) {
					$cs = trim($r["callsign"]);
					$ds = $r["defupdate"];
					$defs[$cs]["defupdate"] = $ds;
				} // end foreach
	
				if ( isset( $defs[$mycall]["defupdate"] ) && $defs[$mycall]["defupdate"] != "" ) {
					$find = $defs[$mycall]["defupdate"];
				} else {
					$find = $defs["none"]["defupdate"];
				} // end if
				$r = query("SELECT typeid FROM datatypes WHERE name='$find'");
				$what = $r[0]["typeid"];
				$value = $param2 . " " . implode(" ", $line);
			} // end if
		} // end if


		$q = "BEGIN;\n";
		if ( $what != 0 ) {
			// Updated a persondata value
			// Are they setting it new, or changing existing?
			$w = query("SELECT value FROM persondata WHERE personid=$who AND datatype=$what");
			if ( count($w) > 0 ) {
				$was = $w[0]["value"];
				$mesg = sprintf("Changed %s from %s to %s\n", $find, $was, $value);
				echo $mesg;
				$q .= "UPDATE persondata SET value='$value' WHERE personid=$who AND datatype=$what;\n";
			} else {
				$mesg = sprintf("Set %s to %s\n", $find, $value);
				echo $mesg;
				$q .= "INSERT INTO persondata VALUES ($who, $what, '$value');\n";
			} // end if
		} else {
			// Status message update only
			$mesg = $value;
		} // end if

		$_tac = query("SELECT tactical FROM sessions WHERE callsign='$mycall'");
		$tac = $_tac[0]["tactical"];

		$q .= "INSERT INTO updatesequence VALUES ( $who, " . time() . ", '$mycall/$tac [P]', 0, '" . trim($mesg) . "' );\n";
		$q .= "COMMIT;\n";
		
		// echo $q;

		$r = query($q);
	} else {
		echo "update [search type] <search string> [datatype] New value ...\n\r";
	} // end if
} // end update

function tn_tell($line) {
	if ( count($line) > 1 ) {
		// Drop the verb
		array_shift($line);
		$who = array_shift( $line);
		if ( $who == "ALL" ) { $who = "all"; }
		if ( $who == "*" ) { $who = "all"; }
		echo "Sending message to " . $who . "\n\r";
		$what = implode( " ", $line );
		global $mycall;
		$q = "INSERT INTO messages VALUES ( " . time() . ", '$mycall', '$what', '$who' );";
		$r = query_simple($q);
	} else {
		echo "tell ( all | <callsign> ) Message text...\n\r";
	} // end if
} // end tell

function tn_whoami($line) {
	global $mycall;
	$tac = query("SELECT tactical FROM sessions WHERE callsign='$mycall'");
	echo "Current FCC callsign is " . $mycall . "\n\r";
	echo "Current tactical callsign is " . $tac[0]["tactical"] . "\n\r";
	$return = "";
} // end whoami

function tn_who($line) {
	echo "Current users:\n\r";
	$who = list_sessions();
	foreach( $who as $w ) {
		$idle = time() - $w["timestamp"];
		echo "\t" . $w["callsign"] . "\tIdle: $idle\n";
	} // end forearch
	$return = "";
} // end who

function tn_mycall($line) {
	global $mycall;
	$_sid = query("SELECT sessionid FROM sessions WHERE callsign='$mycall'");
	echo "\n\rNew tactical callsign>";
	global $fp;
	$newtac = trim( fgets($fp,4094) );
	pkt_stop_session($mycall);
	pkt_start_session($mycall, $newtac);
	unset($newtac);
	$return = "";
} // end mycall

function tn_exit($line) {
	bye();
} // end quit

?>
