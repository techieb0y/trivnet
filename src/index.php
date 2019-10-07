<?php
	ini_set( "include_path", "./include/" );

// Trivnetdb 2.0 was designed and built by KD8GBL 

//	The original trivnetdb author has a web page with more inforamtion at:
//	http://msuarc.egr.msu.edu/kb8zqz/packet/

$begin = 0;

include("head.inc");

echo "<table width=\"100%\" border=2>";
$races = query("select * from race");
foreach ($races as $r) {
    $rn = $r["raceid"];
    
    $left = floor((100 * ($r["tail"] / $RACELENGTH[$rn])));
    $right = floor(100 - (100 * ($r["head"] / $RACELENGTH[$rn])));
    
    echo "<tr>";
    echo "<td colspan=2 style=\"width: 100%; background: yellow;\">" . $RACENAME[$rn] . "</td></tr>\n";
    
    $q = "SELECT count(value) AS done FROM persondata WHERE datatype=" . $config["status"] . " AND value='" . $config["finishstatus"] . "' AND personid IN (SELECT personid FROM persondata WHERE datatype IN (SELECT typeid FROM datatypes WHERE name = 'race') AND value='" . $r["raceid"] . "')";
    $done = query($q)[0]["done"];
    $total = query("select count(personid) as total from persondata WHERE personid IN (SELECT personid FROM persondata WHERE datatype IN (SELECT typeid FROM datatypes WHERE name = 'race') AND value='" . $r["raceid"] . "')")[0]["total"];
    $percent = floor(100 * ($done / $total));
    echo "<tr><td width=\"25%\">$percent% Crossed Finish Line<br>\n";
    
    if ($r["raceid"] == 4) {
        echo "Lead wheeler past mile " . $r["head"];
        if ($left > 0) {
            echo "; End of race sweep past mile " . $r["tail"] . "; ";
        }
    } else {
        if ($left > 0) {
            echo "End of race sweep past mile " . $r["tail"] . "; ";
        }
        echo "Lead runner past mile " . $r["head"];
    }
    
    echo "</td><td>";
    
    echo "<table width=\"100%\"><tr>";
    echo "<td style=\"background-color: green;\" width=\"$percent%\">&nbsp;</td>";
    echo "<td style=\"background-color: black\">&nbsp;</td>";
    echo "</tr></table>";
    
    echo "<table width=\"100%\"><tr>";
    if ($left > 0) {
        echo "<td width=\"$left%\" style=\"background-color: red;\">&nbsp;</td>";
    }
    echo "<td style=\"width: 16px;\">&#x1F9F9;</td>\n";
    echo "<td style=\"background-color: grey;\">&nbsp;</td>\n";
    if ($r["raceid"] == 4) {
        echo "<td style=\"width: 16px;\">&#x267F;</td>\n";
        echo "<td width=\"$right%\" style=\"background-color: blue; color: white;\">&nbsp;</td>";
    } else {
        echo "<td style=\"width: 16px;\">&#x1F3C3;</td>\n";
        echo "<td width=\"$right%\" style=\"background-color: blue; color: white;\">&nbsp;</td>";
    }
    echo "</tr></table>\n";
    echo "</td>";
    echo "</tr>";
}
echo "</table>\n";

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
$sha = getenv('TRIVNET-SHA'); 
echo "Trivnetdb v2.6 ($sha) - by KD8GBL<br>\n";

require_once("include/foot.inc");

echo "</body></html>";
?>
