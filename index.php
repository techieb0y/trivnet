<?php
	ini_set( "include_path", "./include/" );

// Trivnetdb 2.0 was designed and built by KD8GBL 

//	The original trivnetdb author has a web page with more inforamtion at:
//	http://msuarc.egr.msu.edu/kb8zqz/packet/

include("head.inc");

// Overall summery

$r_sum = query("select count(persondata.value) as num, enumtypes.value as option from persondata,enumtypes where persondata.datatype=1 and enumtypes.id=persondata.value::integer and enumtypes.datatype=persondata.datatype group by persondata.value, enumtypes.value order by option");

// $r_sum = query("select datatypes.label, enumtypes.value as option, count(persondata.value) as num from persondata, datatypes, enumtypes where persondata.datatype=datatypes.typeid and enumtypes.id=persondata.value::integer and persondata.datatype in ( select typeid from datatypes where enum='t' ) group by label, option");

if ( count($r_sum) > 0 ) {
	echo "<table>\n";
	foreach ($r_sum as $row_summary) {
		echo "<tr><td>" . $row_summary["option"] . "</td><td>" . $row_summary["num"] . "</td></tr>\n";
	}
	echo "</table>\n";
} // end if

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

require_once("include/foot.inc");

echo "</body></html>";
?>
