<?php 

require_once("include/head.inc");
require_once("include/config.inc");
require_once("include/db_ops.inc");

$id=0;

if ( isset($_GET["id"]) ) {
	$id = $_GET["id"];
} // end if

$q = "SELECT * from incidents WHERE id=$id";
$r = query($q);

if ( count( $r ) > 0 ) {
	foreach ( $r as $row) {
		$title = $row["title"];
		$status = $row["status"];
		echo "<b>{$title}</b> ({$status})\n";
	} // end foreach
} // end if

echo "<br>\n";

echo "<form action=\"updateincident.php?id={$id}\" method=\"POST\">";
echo "<input type=text size=128 name=\"message\">";
echo "<input type=\"submit\" value=\"Post\">";
echo "</form>\n";

echo "<br>\n";

$q = "SELECT * from incidentsequence WHERE incident=$id ORDER BY timestamp desc";
$r = query($q);

echo "<table width=\"100%\">\n";

echo "<tr><th>Timestamp</th><th>Message</th></tr>\n";

if ( count( $r ) > 0 ) {
	foreach ( $r as $row) {
		$when = date( "r", $row["timestamp"] );
		$mesg = $row["message"];
		echo "<tr><td>$when</td><td>$mesg</td></tr>\n";
	} // end foreach
} // end if

echo "</table>\n";
echo "<b>" . count($r) . " updates</b><br>\n";

echo "🔗 <b>Linked to this incident:</b>";

$qh = "SELECT typeid,name,label FROM datatypes ORDER BY typeid";
$rh = query($qh);
foreach ( $rh as $row ) {
	$typeid = $row["typeid"];
	$name = $row["name"];
	$label = $row["label"];
	$theBigArray[$typeid] = $name; 
} // end foreach

$theQuery = "SELECT personid,bibnum,firstname,lastname FROM crosstab('SELECT * from persondata WHERE personid IN (SELECT person FROM incidentlink WHERE incident={$id})', 'SELECT typeid FROM datatypes ORDER BY typeid') as ( personid int";
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
	echo "Nobody linked to this incident.";
} // end if

require_once("include/foot.inc");
?>
