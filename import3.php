<?php

	require_once("include/head.inc");

	$delimname = $_POST["delim"];

	$delims["tab"] = "\t";
	$delims["comma"] = ",";
	$delims["pipe"] = "|";
	
	$delim = $delims[$delimname];

	foreach( $_POST as $key => $value ) {
		if ( preg_match("/field([0-9]){1,}/", $key, $match) ) {
			$k = str_replace("field", "", $match[0]);
			$fields[$k] = $value;
		} // end if
	} // end foreach 
	
	// get selected file
	$file = $_POST["csvfile"];
	
	$q_id = "SELECT nextval('async_jobid_seq') AS jobid";
	$r_id = query($q_id);
	$jobid = $r_id[0]["jobid"];

	$dtastring = "IMPORT" . "!" . $delimname . "!" . serialize($fields);

	$timestamp = time();

	$q = "INSERT INTO async VALUES ( '$jobid', '$file', '$mycall', '0', '0', '$dtastring', '1', '0', $timestamp )";
	$r = query($q);
	
	echo "Job submitted!";
	echo "<a href=\"admin.php\">Back</a>";

?>	
