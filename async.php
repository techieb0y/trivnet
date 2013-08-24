#!/usr/bin/php
<?php

require_once("include/db_ops.inc");
require_once("include/config.inc");
require_once("include/constants.inc");
require_once("include/sessions.inc");

/* 
 * Async job processing engine
 * Reads jobs from the queue and processes them.
 * Currently, 'jobs' are:
 * - sets of search criteria, each of which will have a single data value updated to a new value.
 * - set of data, each of which will be added as new persondata entries
 */

// print_r($argv);

// Options:
// --list - show the list of jobs
// --run X - run job id X, provided it exists and is runable
// --detail X - display status of job id X
// --hold X - if X is runable, flag it as on hold 
// --release X - if X is on hold, unset the flag and make it runnable
// --runonce - Run the top runnable job (for cron invocation)

array_shift($argv);

if ( isset($argv[1]) ) {
	$id = $argv[1];
}

switch ($argv[0]) {
	case "--list":
		$q = "SELECT * from async";
	$r = query($q);
	if ( count($r) > 0 ) {
		foreach ($r as $job) {
			$s = $job["state"];
			echo $job["jobid"] . ": " . $jobstate[$s] . "\n";
		} // end foreach
	} // end if
	break;

	case "--run":
		runJob($id);
	break;

	case "--runonce":
		global $config;
		$q = "SELECT count(jobid) as num from async WHERE state=3";
		$r = query($q);
		if ( $r[0]["num"] < $config["async_count"] ) {
			$q = "SELECT jobid from async WHERE state=1 ORDER BY jobid ASC";
			$r = query($q);
			if ( count($r) > 0 ) {
				$once_id = $r[0]["jobid"];
				runJob($once_id);
			} // end if
		} // end if
		// We also do some housekeeping functions within runOnce
		housekeeping();
	break;

	case "--detail":
		$q = "SELECT * from async WHERE jobid=$id";
	$r = query($q);
	$file = $r[0]["filename"];
	$callsign = $r[0]["callsign"];
	$searchtype = $r[0]["searchtype"];
	$updatetype = $r[0]["updatetype"];
	$data = $r[0]["data"];
	$state = $r[0]["state"];

	$qst = "SELECT * from datatypes WHERE typeid=$searchtype";
	$qut = "SELECT * from datatypes WHERE typeid=$updatetype";
	$rst = query($qst); $rut = query($qut);
	$search = $rst[0]["name"];
	$update = $rut[0]["name"];

	$lines = count( file($file) );

	printf("%d: [%s] set %s to '%s' for %d %s's, submiteed by %s\n", $id, $jobstate[$state], $update, $data, $lines, $search, $callsign );
	break;

	case "--hold":
		$q = "UPDATE async SET state=4, timestamp=" . time() . " WHERE jobid=$id";
	$r = query($q);
	break;

	case "--release":
		$q = "UPDATE async SET state=1, timestamp=" . time() . " WHERE jobid=$id";
	$r = query($q);
	break;

	default:
	echo "Usage: [ --list | --detail JOBID  | --hold JOBID | --release JOBID | --runonce ]\n";
	break;
}

function runJob($jobId) {
	declare(ticks = 1);
	pcntl_signal(SIGTERM, function ($signo) use (&$jobId) { $r = query("UPDATE async SET state=2, timestamp=" . time() . " WHERE jobid=$jobId"); syslog(LOG_NOTICE, "Got SIGTERM"); die("Aborting\n");} );
	pcntl_signal(SIGINT, function ($signo) use (&$jobId) { $r = query("UPDATE async SET state=2, timestamp=" . time() . " WHERE jobid=$jobId"); syslog(LOG_NOTICE, "Got SIGINT"); die("Aborting\n"); } );

	$r = query("SELECT * from async WHERE jobid=$jobId");
	$file = $r[0]["filename"];
	$callsign = $r[0]["callsign"];
	$searchtype = $r[0]["searchtype"];
	$updatetype = $r[0]["updatetype"];
	$data = $r[0]["data"];
	$state = $r[0]["state"];
	if ( $state != 1 ) { echo "Cannot run job $id\n"; break; }

	$workingJob = $jobId;
	$q = query("UPDATE async SET state=3, timestamp=" . time() . " WHERE jobid=$jobId");

	$ut = query("SELECT * from datatypes WHERE typeid=$updatetype");
	$what = $ut[0]["name"];

	// Read the data file
	$workingSet = file($file);
	$nonmatch = 0;
	$now = time();

	$setSize = count($workingSet);
	$interval = floor($setSize/100);
	if ( $interval < 1 ) { $interval = 1; }

	$count = 0;

	$errlog = fopen($file . ".err", "w+");

	if ( preg_match("/^IMPORT!/", $data) ) {
		// Magic keyword telling us to use the bulk dataset import rules
		// Explore into parts: header, delim, serialized data
		$stuff = explode("!", $data);
		$fields = unserialize($stuff[2]);
		$delimname = $stuff[1];

		$delims["tab"] = "\t";
		$delims["comma"] = ",";
		$delims["pipe"] = "|";

		$delim = $delims[$delimname];
	
		// Get the current highest personid for starting import
		$q = "SELECT 1+MAX(id) AS newid FROM people";
		$nid = query($q);
		$id = $nid[0]["newid"];
		$beginId = $id;
		// Marathon special-case handling for bib numbers -- M/F prefixes normalized to uppercase.
		$bibNum_dataType = query("SELECT typeid FROM datatypes WHERE name = 'bibnum'");

		foreach ( $workingSet as $line ) {
			$qq = "BEGIN;\n";
			$qq .= "INSERT INTO people VALUES ( " . $id . " );\n";

			// Break the line up
			$lineparts = str_getcsv($line, $delim, "\"");

			foreach ( $lineparts as $key => $val ) {	
				// Remove quote-mark separators, and trailing whitespace
				$trueVal = trim($val);

				if ( "NULL" != $fields[$key] ) {
					if ( $bibNum_dataType[0]["typeid"] == $fields[$key] ) {
						$qq .= sprintf("INSERT INTO persondata VALUES ( %d, %d, '%s' );\n", $id, $fields[$key], strtoupper(addslashes($trueVal))  );
					} else {
						$qq .= sprintf("INSERT INTO persondata VALUES ( %d, %d, '%s' );\n", $id, $fields[$key], addslashes($trueVal) );
					} // end if
					$abort = 0;
				} // end if (handle 'ignore' choice)
			} // end foreach (fields per line)

			if ( 1 == $abort ) { 
				$qq .= "ABORT;\n"; 
			} else {
				$qq .= "COMMIT;\n"; 
			} // end if

			// run query
			$r = query($qq);

			// Update progress
			$count++;
			if ( ($count % $interval) == 0 ) {
				$pct = floor(100*($count/$setSize));
				syslog(LOG_DEBUG, "Record $count of $setSize - $pct");
				echo "Record $count of $setSize - $pct\n";
				$q = "UPDATE async SET progress = '$pct', timestamp=" . time() . " WHERE jobid=$jobId";
				$r = query($q);
			} // end if

			// increment personId
			$id++;
		} // end foreach ( lines in file )

		// Done!
		$r = query("UPDATE async SET state=0, progress=100, timestamp=" . time() . " WHERE jobid=$jobId");

	} else {
		// Do the normal stuff

		// Count of errors
		$err = 0;

		// Marathon special-case handling for bib numbers
		$bibNum_dataType = query("SELECT typeid FROM datatypes WHERE name = 'bibnum'");

		foreach ($workingSet as $row) {
			if ( trim($row )== "END" ) { break; }
			if ( $searchtype == $bibNum_dataType[0]["typeid"] ) {
				$r = query("SELECT personid FROM persondata WHERE datatype=$searchtype AND value='" . strtoupper(trim($row)) . "'");
			} else {
				$r = query("SELECT personid FROM persondata WHERE datatype=$searchtype AND value='" . trim($row) . "'");
			} // end if
			if ( count($r) > 1 ) {
				fwrite($errlog, "Duplicate entry for $personid\n");
				$err++;
			} else if ( count($r) == 1 ) {
				$personid = $r[0]["personid"];
				// 0 is the update type for a status-message-only, non-persondata update
				// The typical marathon application for this at is the 'Runner crossed finish line' notice.
				$q = "BEGIN;\n";

				if ( "0" == $updatetype ) {
					$q .= "INSERT INTO updatesequence VALUES ('$personid', '$now', '$callsign', $updatetype, '$data');\n";
					$q .= "COMMIT;\n";
				} else {
					$_en = query("SELECT enum FROM datatypes WHERE typeid=$updatetype");
					if ( 't' == $_en[0]["enum"] ) {
						$st = query("SELECT value FROM enumtypes WHERE datatype=$updatetype and id=$data");
						$_dtn = query("SELECT name FROM datatypes WHERE typeid=$updatetype");
						$statusText = "Set " . $_dtn[0]["name"] . " to " . $st[0]["value"];
					} else {
						$statusText = "Set " . $_dtn[0]["name"] . " to " . $data;
					} // end if
					$q .= "UPDATE persondata SET value='$data' WHERE personid=$personid AND datatype=$updatetype;\n";
					$q .= "INSERT INTO updatesequence VALUES ('$personid', '$now', '$callsign', 0, '$statusText');\n";
					$q .= "COMMIT;\n";
				} // end if

				// echo $q;
				$r = query($q);
			} else {
				fwrite($errlog, "No match found for $personid\n");
				$nonmatch++;
				$err++;
			} // end if
			// Update progress
			$count++;
			if ( ($count % $interval) == 0 ) {
				$pct = floor(100*($count/$setSize));
				syslog(LOG_DEBUG, "Record $count of $setSize - $pct");
				echo "Record $count of $setSize - $pct\n";
				$q = "UPDATE async SET progress = '$pct', timestamp=" . time() . " WHERE jobid=$jobId";
				$r = query($q);
			} // end if

		} // end foreach
		if ( $err > 0 ) { 
			$r = query("UPDATE async SET state=5, timestamp=" . time() . " WHERE jobid=$jobId;");
		} else {
			$r = query("UPDATE async SET state=0, timestamp=" . time() . " WHERE jobid=$jobId;");
		} // end if

		fwrite($errlog, "Didn't find $nonmatch inputs\n");
		echo "Didn't find $nonmatch inputs\n";
		fflush($errlog);
		fclose($errlog);
	} // end if - bulk-import special case

	touch("/tmp/asyncEngine.stat");
} // end runJob

function housekeeping() {
	global $config;
	// Clean up timed-out sessions
	$sdb = list_sessions();
	if ( count($sdb) > 0 ) {
		foreach( $sdb as $s ) {
			if ( $s["age"] > $config["idle_timeout"] ) {
				echo "Cleaning session " . $s["sessionid"] . "\n";
				adminDeleteSession($s["sessionid"]);
			} // end if
		} // end foreach
	} // enf if
	touch("/tmp/asyncEngine.stat");
} // end housekeeping

?>
