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

} // end connect

function query_simple($string) {
	require("config.inc");
	// Input: SQL Query string
	// Returns: result array. rows as first index; associative array of fields under that

//	echo "*** $string\n";
error_log("QS: " . $string, 0);

	// Make sure we're connected. This lets us get away with being lazy.
	//if ( !isset($db) ) {
	//	connect();
	//} // end if

	$result = 0;

	$result = query( $string );

	// query will return an array with one row (one result) which itself is
	// and array of one row (the single column of the output)

	return( $result[0][0] );

} // end query

function query( $string ) {
	require("config.inc");
	if ( !isset($db) ) {
		connect();
	} // end if

	error_log("Q: " . $string, 0);
	// error_log($string, 0);

	$return = array();

	$res = pg_query( connect(), $string );
	if ( $res ) {
		unset($return);
		while ( $z = pg_fetch_assoc($res) ) {
			$return[] = $z;
		} // end while
	
		if ( isset($return) ) {
			return($return);
		}
	} else {
		// FIXME
		 error_log("DB Query Error - query was <<$string>> - error was " . pg_result_error($res), 0);
		return( array() ); // return an explicit empty array instead of null
	} // end if

} // end query
