<?php

require_once("include/head.inc");

// Did they select a file to upload?
if ( $_FILES["persondata"]["size"] > 0 ) {
	// uploaded file, instead of entering data into textarea
	$filename = "./jobs/" . basename($_FILES['persondata']['name']);
	move_uploaded_file($_FILES['persondata']['tmp_name'], $filename);
	echo "Using " . filesize($filename) . " bytes of person data file contents<br>\n";
	$use_file = 1;
}

if ( $use_file == 1 ) {
	$file = $filename;
} else {
	$file = "./csvdata/" . $_POST["csvfile"];
} // end if

echo "File: $file<br>\n";

$delim_pre = $_POST["delim"];

$delims["tab"] = "\t";
$delims["comma"] = ",";
$delims["pipe"] = "|";

// Open file handle
$fh = fopen($file, "r");

if ( $delim_pre == "0" ) {
	// Get first line and figure out number of fields, delimiters
	$rawfirstline = fgets($fh);
	foreach( $delims as $name => $symbol ) {
		$where = strpos($rawfirstline, $symbol);
		$results[$name] = $where;
	} // end foreach
	asort($results);
	// Now that $results is sorted by the position of the delimiter char, we shall assume that the delimiter possibility with
	// the lowest non-zero distance is the right one. This breaks if the first char is actually delimiter, however.
	$counter = 0;

	// Do guesswork
	foreach ( $results as $name => $distance ) {
		if ( isset($distance) && ( $distance > 0 ) ) {
			$delim = $delims[$name];
			$counter++;
		} // end if
	} // end loop
	if ( $counter > 1 ) {
		echo "Got more than one candidate delimiter; please verify this guess!<br>\n";
	} else {
		echo "Selecting '$delim' as value separator<br>\n";
	} // end if

} else {
	// User specified a delimiter; don't guess.
	$delim = $delims[$delim_pre];
} // end if

fseek($fh, 0, SEEK_SET);

$firstline = fgetcsv($fh, 0, $delim);
$fields = count($firstline);
$lines[] = $firstline;

while ( !feof($fh) ) {
	// do the loop, read in the next bunch.
	$lines[] = fgetcsv($fh, 0 , $delim);
	$line_num++;
	if ( $line_num > 9 ) {
		break;
	} // end if
} // end while


$q = "SELECT * FROM datatypes ORDER BY typeid";
$r = query($q);

// Pretty-print
echo "<form action=\"import3.php?delim=$delim\" method=\"POST\">";
echo "<table>\n<tr>";
for ( $i=0; $i < $fields; $i++ ) {
	echo "<td><select name=\"field$i\">";
	echo "<option value=\"NULL\">Ignore\n";
	foreach($r as $row) { echo "<option value=" . $row["typeid"] . ">" . $row["label"] . "\n"; }
	echo "</select></td>\n";
} // end for
echo "</tr>\n";

foreach ( $lines as $line ) {
	echo "<tr>";
	foreach ( $line as $field ) { echo "<td>$field</td>"; }
	echo "</tr>\n";
} // end foreach
echo "</table>\n";
$delimname = array_search($delim, $delims);
echo "<input type=\"hidden\" name=\"delim\" value=\"$delimname\">\n";
echo "<input type=\"hidden\" name=\"numfields\" value=\"$fields\">\n";
echo "<input type=\"hidden\" name=\"csvfile\" value=\"$file\">\n";
echo "<input type=\"submit\" value=\"Import\"></form>\n";
echo "</body></html>";
?>	
