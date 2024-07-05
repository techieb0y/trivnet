<?php
	ini_set( "include_path", "./include/" );

// Trivnetdb 2.0 was designed and built by KD8GBL 

//	The original trivnetdb author has a web page with more inforamtion at:
//	http://msuarc.egr.msu.edu/kb8zqz/packet/

$begin = 0;

include("head.inc");

// Overall summery
$sdt = $config["status"];
echo "Status summary:<br>\n";


$q_sum = 'select count(persondata.value) as num, enumtypes.value as option from persondata,enumtypes where persondata.datatype = $1 and enumtypes.id=persondata.value::integer and enumtypes.datatype=persondata.datatype group by persondata.value, enumtypes.value order by option;';
$p_sum[] = $sdt;

$res = pg_query_params( connect(), $q_sum, $p_sum );
$r_sum = array();
while ( $z = pg_fetch_assoc($res) ) {
    $r_sum[] = $z;
} // end while

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

echo "<img src=\"symbol/11\">Currently in the med tent:";

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

$res = pg_query( connect(), $theQuery );

if ( pg_num_rows($res) > 0 ) {
    $r_sum = array();
    while ( $z = pg_fetch_assoc($res) ) {
        $r_sum[] = $z;
    } // end while
    $inmed = query($theQuery);

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

echo "</td>\n";
echo "</tr></table>";

// Latching statuses

echo "<table>\n";

$q_latch = 'select label,count(personid) as num from latchlog full outer join latchtypes on latchtypes.id=latchlog.latchid group by label;';

$res = pg_query( connect(), $q_latch );
$r = array();
while ( $z = pg_fetch_assoc($res) ) {
	echo "<tr><td>" . $z["label"] . "</td><td>" . $z["num"] . "</td></tr>\n";
} // end while

echo "</table>\n";



echo "<hr>\n";

echo "Message summary:<br>\n";

$msgdt = $config["message"];

// $q_summary = "select value, count(value) as num from updatesequence where datatype=0 and value ilike '%out at%' group by message";

//$q_summary = "select message,count(message) as num from updatesequence where message not ilike '%out%'  and message not like '%bed%' group by message order by message;";

$q_summary = "select value, count(value) as num from updatesequence where datatype = \$1 and value not like '%Search performed%' group by value;";
$p_summary[] = $msgdt;

$res = pg_query_params( connect(), $q_summary, $p_summary );

if (pg_num_rows($res) > 0 ) {
    $r_summary = array();
    while ( $z = pg_fetch_assoc($res) ) {
        $r_summary[] = $z;
    } // end while


	echo "<table>\n";
	foreach ($r_summary as $row_summary) {
		$v = $row_summary["value"];
		if ( preg_match("/^Set/", $v) ) {
			echo "<tr class=\"audit\">";
		} else if ( preg_match("/^Changed/", $v) ) {
			echo "<tr class=\"audit\">";
		} else {
			echo "<tr>";
		}

		echo "<td>" . $v . "</td><td>" . $row_summary["num"] . "</td></tr>\n";
	}
	echo "</table>\n";
} 

echo "<br><hr>";
echo "<a href=\"help.html\" target=\"_new\">TrivnetDB Documentation</a>";
echo "<br>";


@include "include/sha.inc";

echo "Trivnetdb v2.7 ($sha) - by KD8GBL<br>\n";

require_once("include/foot.inc");

echo "</body></html>";
?>
