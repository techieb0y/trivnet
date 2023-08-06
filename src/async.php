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
		$q = "SELECT * from async ORDER BY jobid";
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
		runOnce();
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

	case "--daemon":
		$scaleFactor = 1;
		$idleLastRun = false;
		$stop = 0;

		while ( $stop == 0 ) {
			housekeeping();
			echo "Sleeping for $scaleFactor seconds\n";
			sleep($scaleFactor);

			$q = "SELECT count(jobid) as num from async WHERE state=1";
			$r = query($q);
			$num = $r[0]["num"];
			echo "$num jobs available to run\n";

			if ( $num > 0 ) {				
							$idleLastRun = false;
							$scaleFactor = 1;
							echo "Running a job\n";
							runOnce();
			} else {
				echo "No jobs available\n";
				if ( $idleLastRun ) {
					echo "Consequtive idle periods, doing exponential backoff\n";
					if ( $scaleFactor < 31 ) {
						$scaleFactor = 2*$scaleFactor;
					}
				}
				$idleLastRun = true;
			}
		}
	break;

	default:
	echo "Usage: [ --list | --detail JOBID  | --hold JOBID | --release JOBID | --runonce ]\n";
	break;
}

function runOnce() {
		global $config;
		$q = "SELECT count(jobid) as num from async WHERE state=3";
		$r = query($q);
		if ( $r[0]["num"] < $config["async_count"] ) {
			$q = "SELECT jobid from async WHERE state=1 ORDER BY jobid ASC";
			$r = query($q);
			if ( ! empty($r) ) {
				if ( count($r) > 0 ) {
					$once_id = $r[0]["jobid"];
					runJob($once_id);
				} // end if
			} // end if
		} // end if
		// We also do some housekeeping functions within runOnce
		housekeeping();
}	

function runJob($jobId) {
	declare(ticks = 1);
	// pcntl_signal(SIGTERM, function ($signo) use (&$jobId) { $r = query("UPDATE async SET state=2, timestamp=" . time() . " WHERE jobid=$jobId"); syslog(LOG_NOTICE, "Got SIGTERM"); die("Aborting\n");} );
	// pcntl_signal(SIGINT, function ($signo) use (&$jobId) { $r = query("UPDATE async SET state=2, timestamp=" . time() . " WHERE jobid=$jobId"); syslog(LOG_NOTICE, "Got SIGINT"); die("Aborting\n"); } );

	$r = query("SELECT * from async WHERE jobid=$jobId");
	$file = $r[0]["filename"];
	$callsign = $r[0]["callsign"];
	$searchtype = $r[0]["searchtype"];
	$updatetype = $r[0]["updatetype"];
	$data = $r[0]["data"];
	$state = $r[0]["state"];
	if ( $state != 1 ) { echo "Cannot run job $id\n"; return(0); }

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
		// Explode into parts: header, delim, serialized data
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
				$intermediateVal = trim($val);
				// TODO: This is a hack; replace with proper paramaterized queries.
				$trueVal = str_replace("'", "''", $intermediateVal);

				if ( "NULL" != $fields[$key] ) {
					if ( $bibNum_dataType[0]["typeid"] == $fields[$key] ) {
						$qq .= sprintf("INSERT INTO persondata VALUES ( %d, %d, '%s' );\n", $id, $fields[$key], strtoupper($trueVal) );
					} else {
						$qq .= sprintf("INSERT INTO persondata VALUES ( %d, %d, '%s' );\n", $id, $fields[$key], $trueVal );
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

		foreach ($workingSet as $row) {
			global $config;
			
			if ( trim($row )== "END" ) { break; }
			// Marathon special-case handling for bib numbers, which will almost always be the multi-edit default type.
			if ( $searchtype == $config["multidefault"] ) {
				$q = 'SELECT personid FROM persondata WHERE datatype = $1 AND value = $2;';

				$p[0] = $searchtype;
				$p[1] = strtoupper(trim($row));

				$res = pg_query_params( connect(), $q, $p );
				$r = array();
				while ( $z = pg_fetch_assoc($res) ) {
					$r[] = $z;
				} // end while			
			} else {
				$q = 'SELECT personid FROM persondata WHERE datatype = $1 AND value = $2;';

				$p[0] = $searchtype;
				$p[1] = trim($row);

				$res = pg_query_params( connect(), $q, $p );
				$r = array();
				while ( $z = pg_fetch_assoc($res) ) {
					$r[] = $z;
				} // end while
			} // end if

			if ( (pg_num_rows($res) > 0) && (count($r) > 1) ) {
				fwrite($errlog, "Duplicate entry for $personid\n");
				$err++;
			} else if ( count($r) == 1 ) {
				$msg = $config["message"];
				$personid = $r[0]["personid"];
				$q = "BEGIN;\n";

				$_tac = query("SELECT tactical FROM sessions WHERE callsign='$callsign'");
				$tac = $_tac[0]["tactical"];

				if ( $msg == $updatetype ) {
					// We're updating the status message, but no actual data values
					$q .= "INSERT INTO updatesequence VALUES ('$personid', '$now', '$callsign/$tac [B]', $updatetype, '$data');\n";
					$q .= "COMMIT;\n";
				} else {
					// We're updating a data value, and generating a status message to say we did so
					$_dt = query("SELECT name,enum FROM datatypes WHERE typeid=$updatetype");
					if ( 't' == $_dt[0]["enum"] ) {
						$st = query("SELECT value FROM enumtypes WHERE datatype=$updatetype and id=$data");
						$statusText = "Set " . $_dt[0]["name"] . " to " . $st[0]["value"];
					} else {
						$statusText = "Set " . $_dt[0]["name"] . " to " . $data;
					} // end if 
					$q .= "SELECT * FROM sp_upsert_persondata('$personid', '$updatetype', '$data');\n";
					$q .= "INSERT INTO updatesequence VALUES ('$personid', '$now', '$callsign/$tac [B]', $msg, '$statusText');\n";
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

	touch("/var/www/html/jobs/asyncEngine.stat");
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
	touch("/var/www/html/jobs/asyncEngine.stat");
} // end housekeeping

?>

