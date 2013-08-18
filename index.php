<?php
	ini_set( "include_path", "./include/" );

// Trivnetdb 2.0 was designed and built by KD8GBL 

//	The original trivnetdb author has a web page with more inforamtion at:
//	http://msuarc.egr.msu.edu/kb8zqz/packet/

include("head.inc");

include("inmedtent.inc");

echo "<hr>";

// Overall summery
/*
$q_summary = "select message, count(message) as num from updatesequence where message in ( select text from quickmesg ) group by message";

//$q_summary = "select message,count(message) as num from updatesequence where message not ilike '%out%'  and message not like '%bed%' group by message order by message;";

$r_summary = query($q_summary);
if ( count($r_summary) > 0 ) {
	echo "<table>\n";
	foreach ($r_summary as $row_summary) {
		echo "<tr><td>" . $row_summary["message"] . "</td><td>" . $row_summary["num"] . "</td></tr>\n";
	}
	echo "</table>\n";
} // end if
*/

echo "<hr>\n";

// $q_summary = "select value, count(value) as num from updatesequence where datatype=0 and value ilike '%out at%' group by message";
$q_summary = "select value, count(value) as num from updatesequence where datatype=0 group by value";

//$q_summary = "select message,count(message) as num from updatesequence where message not ilike '%out%'  and message not like '%bed%' group by message order by message;";

$r_summary = query($q_summary);
if ( count( $r_summary) > 0 ) {
	echo "<table>\n";
	foreach ($r_summary as $row_summary) {
		echo "<tr><td>" . $row_summary["value"] . "</td><td>" . $row_summary["num"] . "</td></tr>\n";
	}
	echo "</table>\n";
} 


echo "<br><hr>";
echo "Trivnetdb v2.5 - by KD8GBL<br>\n";

/*
 *  // Display the build version, if the current directory is an SVN
 *  // working copy. 
 *	$ver = `svn info | grep Revision | awk '{print $2}'`;
 *	echo "Build: $ver<br>\n";
 */

echo "</body></html>";

?>
