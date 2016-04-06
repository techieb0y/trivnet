<?php
error_reporting(E_ALL);

$now = time();

$use_file = 0;

require_once("include/head.inc");

if ( $_FILES["searchkey_file"]["size"] > 0 ) {
	// uploaded file, instead of entering data into textarea
	$filename = "/var/www/trivnet/jobs/" . basename($_FILES['searchkey_file']['name']);
	if ( file_exists($filename) ) { copy($filename, $filename . ".old"); }
	move_uploaded_file($_FILES['searchkey_file']['tmp_name'], $filename);
	echo "Overriding textarea with " . filesize($filename) . " bytes of file content";
	$use_file = 1;
}

echo "<pre>"; print_r($_POST); echo "</pre>";

$mile = $_POST["mile"];
$datatype = $_POST["datatype"];
$canned_status = $_POST["quickmesg"];
$custom_status = $_POST["custom"];

if ( strlen($custom_status) > 0 ) {
	$status = $custom_status;
} else {
	$status = $canned_status;
} // end if 

$mesg = $status;
if ( strlen($mile) > 0 ) {
	$mesg .= " at mile $mile";
} // end if

$updatetype = $_POST["updatetype"];
$updatevalue = $_POST["updatevalue"];

echo "<pre>";

if ( $use_file == 1 ) {
	if ( $_POST["autodiff"] == "on" ) {
		$fn2 = $filename . ".old";
		if ( file_exists($fn2) ) {
			// Old file exists, and we should diff against it
			$buf = `diff -y --suppress-common-lines $filename $fn2 | awk '{print $1}'`;
			$search_keys = trim($buf);
			echo "Pre-processing of input yielded " . strlen($search_keys) . " bytes of data\n";
		} else {
			// User said to diff against an old version, but we don't have one with a matching name.
			// Just use the whole uploaded file
			echo "Ignoring empty previous-version file during pre-processing\n";
			$search_keys = trim(file_get_contents($filename));
		} // end if
	} else {
		// Use whole preexisting file
		$search_keys = trim(file_get_contents($filename));
	} // end if
} else {
	// Use data typed into form
	$search_keys = $_POST["searchkeys"];
}

// Fix line-ending mess
$keys = str_replace("\r", "\n", $search_keys);
$keys = str_replace("\r\n", "\n", $keys);
$keys = str_replace("\n\r", "\n", $keys);
$search_keys = str_replace("\n\n", "\n", $keys);

$data = explode("\n", $search_keys);

$tmp = tmpfile() or die("Unable to open tempfile.");

// For uploaded files, this is the same as copying said file.
// But they could have typed it in, so we write it out to a temp file
foreach($data as $key) {
	fwrite($tmp, $key . "\n");
} // end foreach
fflush($tmp);

$q_id = "SELECT nextval('async_jobid_seq') AS jobid";
$r_id = query($q_id);
$jobid = $r_id[0]["jobid"];

$jobfile = "/var/www/trivnet/jobs/" . str_replace(" ", "_", $mycall) . "-" . $jobid;
$fh = fopen($jobfile, "w+");
fseek($tmp, 0);
stream_copy_to_stream($tmp, $fh);
fclose($fh);
fclose($tmp);

if ( $updatetype != 0 ) { $data = $updatevalue; } else { $data = $mesg; }

$q_submit = "INSERT INTO async VALUES ( $jobid, '$jobfile', '$mycall', '$datatype', '$updatetype', '$data', 1, 0, '" . time() . "');";
echo "<pre>" . $q_submit . "</pre><br>\n";
$r_submit = query($q_submit);

echo "Submitted async job $jobid<br>\n";

?>
