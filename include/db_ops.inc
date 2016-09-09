<?php
require_once("config.inc");
require_once("send-metric.inc");

/********************** Common functions - these are abstract redirectors *****/

function connect() {
	// Input: none
	// Returns: database handle

	require("config.inc");

	$db = 0;	
	$vars = array( "dbname" => "DB_NAME", "host" => "DB_HOST", "user" => "DB_USER", "password" => "DB_PASS" );

	if ( "pgsql" == $DB_TYPE ) {
		$str = "";
		// Put together the connect string with array magic and variable variables
		foreach ( $vars as $key => $var ) {
			if ( isset($$var) && ( strlen($$var) > 1 ) ) {
				$str .= $key . "=" . $$var . " ";
			} // end if
		} // end foreach
	
		// echo "pgsql connect string is: $str \n";

		$db = pg_connect( trim($str) );

		if ( !isset($db) ) {
			die("Could not connect to the database\n");
		} else {
			return $db;
		} // end if

	} else if ( "mysql" == $DB_TYPE ) {
		// MySQL is simple, and uses fixed ordering of connect params: host, user, pass; then 'select' the db
		$db = mysql_connect( $DB_HOST, $DB_USER, $DB_PASS );
		if ( !isset($db) ) {
			die("Could not connect to the database\n");
		} else {
			mysql_select_db($DB_NAME);
			return $db;
		} // end if

	} else {
		die("Did not understand database type of $DB_TYPE\n");
	} // end if

} // end connect

function query($string) {
	require("config.inc");
	// Input: SQL Query string
	// Returns: result array. rows as first index; associative array of fields under that

	// echo "*** $string\n";
	// error_log("Q: " . $string, 0);

	// Make sure we're connected. This lets us get away with being lazy.
	if ( !isset($db) ) {
		connect();
	} // end if

	if ( "pgsql" == $DB_TYPE ) {
		return( query_pgsql( $string ) );
	} else if ( "mysql" == $DB_TYPE ) {
		return( query_mysql( $string ) );
	} else {
		die("Did not understand database type of $DB_TYPE");
	} // end if

} // end query

function query_simple($string) {
	require("config.inc");
	// Input: SQL Query string
	// Returns: result array. rows as first index; associative array of fields under that

//	echo "*** $string\n";
//	error_log("QS: " . $string, 0);

	// Make sure we're connected. This lets us get away with being lazy.
	//if ( !isset($db) ) {
	//	connect();
	//} // end if

	$result = 0;

	if ( "pgsql" == $DB_TYPE ) {
		$result = query_pgsql( $string );
	} else if ( "mysql" == $DB_TYPE ) {
		$result = query_mysql( $string );
	} else {
		die("Did not understand database type of $DB_TYPE");
	} // end if

	// query will return an array with one row (one result) which itself is
	// and array of one row (the single column of the output)

	return( $result[0][0] );

} // end query

/************************* Functions for PostgreSQL **************************/

function query_pgsql( $string ) {
	//require("config.inc");
	//if ( !isset($db) ) {
	//	connect();
	//} // end if

// error_log($string, 0);
$before = microtime(true);

	$return = array();

	// $res = pg_query( $db, $string );
	$res = pg_query( $string );
	if ( $res ) {
		unset($return);
		while ( $z = pg_fetch_assoc($res) ) {
			$return[] = $z;
		} // end while
	
		if ( isset($return) ) {
			return($return);
		}
$after = microtime(true);
$delta = $after - $before;
syslog(LOG_DEBUG, "Query performed in $delta msec");
sendFullMetric("trivnet.psql.time", $delta);

	} else {
		// FIXME
		 error_log("DB Query Error - query was <<$string>> - error was " . pg_result_error($res), 0);
	} // end if

} // end query_pgsql

/************************* Functions for MySQL **************************/

function query_mysql( $string ) {
	//require("config.inc");
	//if ( !isset($db) ) {
	//	connect();
	//} // end if

	$return = array();

	$res = mysql_query( $string, $db );
	if ( isset($res) ) {
		unset($return);
		while ( $z = mysql_fetch_assoc($res) ) {
			$return[] = $z;
		} // end while

		return($return);

	} else {
		error_log("DB Query Error - query was <<$string>>", 0);
	} // end if

} // end query_mysql
