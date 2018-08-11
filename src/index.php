<?php
	ini_set( "include_path", "./include/" );

// Trivnetdb 2.0 was designed and built by KD8GBL 

//	The original trivnetdb author has a web page with more inforamtion at:
//	http://msuarc.egr.msu.edu/kb8zqz/packet/

$begin = 0;

include("head.inc");

echo "<table width=\"100%\"><tr>";
$done = query("select count(value) as done from persondata where datatype=" . $config["status"] . " and value='" . $config["finishstatus"] . "'")[0]["done"];
$total = query("select count(id) as total from people")[0]["total"];
$percent = floor(100 * ($done/$total));
echo "<td style=\"background-color: green;\" width=\"$percent%\">$percent% Crossed Finish Line</td>";
echo "<td style=\"background-color: black\">&nbsp;</td>";
echo "</tr></table>\n";


echo "Per-race Head and Tail locations:<br>\n";
echo "<table width=\"100%\">";
$races = query("select * from race");
foreach ( $races as $r ) {
	echo "<tr>";
	$left = floor( ( 100 * ( $r["tail"] / 26.2 ) ) );
	$right = floor( 100 - ( 100 * ( $r["head"] / 26.2 ) ) );
	$middle = 100 - ( $left + $right );
	if ( $left > 0 ) {
		echo "<td width=\"$left\" style=\"background-color: red;\">&nbsp;</td>";
	}
	echo "<td width=\"$middle\" style=\"background-color: white;\"><img src=\"getAPRS.php?sym=48\" style=\"left: 0;\">&nbsp;<img src=\"getAPRS.php?sym=59\" style=\"right: 0;\"></td>";
	echo "<td width=\"$right\" style=\"background-color: blue;\">&nbsp;</td>";
	echo "</tr>\n";
}
echo "</table><br>\n";


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


$theQuery = "SELECT personid,bibnum,firstname,lastname FROM crosstab('SELECT * from persondata WHERE personid IN (SELECT personid FROM persondata WHERE datatype=''" . $config["status"] . "'' AND value=''" . $config["medtentstatus"] . "'')', 'SELECT typeid FROM datatypes ORDER BY typeid') as ( personid int";
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
		$bn = $m["bibnum"];
		echo "<li>$bn - <a href=\"detail.php?id=$pid\">$fn $ln</a>\n";
	} //end foreach
	echo "</ul>";
} else {
	echo "Med tent is empty.";
} // end if


if ( $config["use_graphite"] ) {
	if ( getenv("slownet") == "true" ) {
		syslog(LOG_DEBUG, "Skipping display of graph for DSTAR network");
		echo "<td>&nbsp;</td>\n";
	} else {
		$num = count($inmed);
		echo "<td><img src=\"mtgraph.php?num=$num\"></td>\n";
	} // end if
}

echo "</td>\n";
echo "</tr></table>";

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

echo "<br><hr>";
$sha = getenv('TRIVNET-SHA'); 
echo "Trivnetdb v2.6 ($sha) - by KD8GBL<br>\n";

require_once("include/foot.inc");

echo "</body></html>";
?>
