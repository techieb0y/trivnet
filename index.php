<?php
	ini_set( "include_path", "./include/" );

// Trivnetdb 2.0 was designed and built by KD8GBL 

//	The original trivnetdb author has a web page with more inforamtion at:
//	http://msuarc.egr.msu.edu/kb8zqz/packet/

include("head.inc");

echo "<table width=\"100%\"><tr>";
// 2 is a magic number for 'crossed finish line' enum value of status type.
$done = query("select count(value) as done from persondata where datatype=" . $config["status"] . " and value='2'")[0]["done"];
$total = query("select count(id) as total from people")[0]["total"];
$percent = floor(100 * ($done/$total));
echo "<td style=\"background-color: green;\" width=\"$percent%\">$percent% Crossed Finish Line</td>";
echo "<td style=\"background-color: black\">&nbsp;</td>";
echo "</tr></table>\n";

// Overall summery
$sdt = $config["status"];
echo "Status summary:<br>\n";

$r_sum = query("select count(persondata.value) as num, enumtypes.value as option from persondata,enumtypes where persondata.datatype=$sdt and enumtypes.id=persondata.value::integer and enumtypes.datatype=persondata.datatype group by persondata.value, enumtypes.value order by option");

// $r_sum = query("select datatypes.label, enumtypes.value as option, count(persondata.value) as num from persondata, datatypes, enumtypes where persondata.datatype=datatypes.typeid and enumtypes.id=persondata.value::integer and persondata.datatype in ( select typeid from datatypes where enum='t' ) group by label, option");

echo "<table><tr>\n";
echo "<td width=\"50%\">";

if ( count($r_sum) > 0 ) {
	echo "<table>\n";
	foreach ($r_sum as $row_summary) {
		echo "<tr><td>" . $row_summary["option"] . "</td><td>" . $row_summary["num"] . "</td></tr>\n";
	}
	echo "</table>\n";
} // end if

echo "</td><td width=\"50%\">";

echo "<img src=\"getAPRS.php?sym=11\">Currently in the med tent:";

$qh = "SELECT typeid,name,label FROM datatypes ORDER BY typeid";
$rh = query($qh);
foreach ( $rh as $row ) {
	$typeid = $row["typeid"];
	$name = $row["name"];
	$label = $row["label"];
	$theBigArray[$typeid] = $name; 
} // end foreach


$theQuery = "SELECT personid,firstname,lastname FROM crosstab('SELECT * from persondata WHERE personid IN (SELECT personid FROM persondata WHERE datatype=''" . $config["status"] . "'' AND value=''" . $config["medtentstatus"] . "'')', 'SELECT typeid FROM datatypes ORDER BY typeid') as ( personid int";
foreach ( $theBigArray as $fieldName ) {
	$theQuery .= ", " . $fieldName . " varchar ";
} // end foreach
$theQuery .= ") WHERE firstname IS NOT NULL AND lastname IS NOT NULL";

$inmed = query($theQuery);

if ( count($inmed) > 0 ) {
echo "<ul>";
	foreach($inmed as $m) {
		$pid = $m["personid"];
		$fn = $m["firstname"];
		$ln = $m["lastname"];
		echo "<li>$pid - <a href=\"detail.php?id=$pid\">$fn $ln</a>\n";
	} //end foreach
	echo "</ul>";
} else {
	echo "Med tent is empty.";
} // end if

echo "</td></tr></table>";

echo "<hr>\n";

echo "Message summary:<br>\n";

$msgdt = $config["message"];

// $q_summary = "select value, count(value) as num from updatesequence where datatype=0 and value ilike '%out at%' group by message";
$q_summary = "select value, count(value) as num from updatesequence where datatype=$msgdt and value not like '%Search performed%' group by value";

//$q_summary = "select message,count(message) as num from updatesequence where message not ilike '%out%'  and message not like '%bed%' group by message order by message;";

$r_summary = query($q_summary);
if ( count( $r_summary) > 0 ) {
	echo "<table>\n";
	foreach ($r_summary as $row_summary) {
		echo "<tr><td>" . $row_summary["value"] . "</td><td>" . $row_summary["num"] . "</td></tr>\n";
	}
	echo "</table>\n";
} 

echo "<div id=\"graph\" style=\"position: absolute; left: 450px; top: 225px;\">\n";
echo "<img src=\"mtgraph.php\">\n";
echo "</div>\n";

echo "<br><hr>";
echo "Trivnetdb v2.6 - by KD8GBL<br>\n";

/*
 *  // Display the build version, if the current directory is an SVN
 *  // working copy. 
 *	$ver = `svn info | grep Revision | awk '{print $2}'`;
 *	echo "Build: $ver<br>\n";
 */

require_once("include/foot.inc");

echo "</body></html>";
?>
