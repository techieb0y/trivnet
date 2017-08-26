<?php 

	// -------- Bulk Import Stuff ------------
	echo "<div id=\"tabs2\">\n";

	$dir = opendir("csvdata") or die("Could not open CSV data directory");
	while ( $f = readdir($dir) ) {
		$filelist[] = $f;
	} // end while

	sort($filelist);
	echo "<form action=\"import2.php\" method=\"post\" enctype=\"multipart/form-data\">\n";
	echo "Data filename: ";
	echo "<input type=\"file\" name=\"persondata\"> (or select existing: ";
	echo "<select name=\"csvfile\">\n";
	foreach( $filelist as $f ) {
		if ( is_file("csvdata/" . $f) && is_readable("csvdata/" . $f) && $f != "." && $f != ".." ) {
			echo "<option>$f\n";
		} // end if
	} // end foreach
	echo "</select>)<br>\n";
	echo "Data delimiter: ";
	echo "<select name=\"delim\">";
	echo "<option value=\"0\">Determine Automatically";
	echo "<option value=\"comma\">Comma";
	echo "<option value=\"tab\">Tab";
	echo "<option value=\"pipe\">Pipe";
	echo "</select><br>\n<input value=\"Preview data\" type=\"submit\">";
	echo "</form>";
	echo "</div>\n";

?>
