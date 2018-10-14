<?php

// Utility to update our cache of FCC license data

// 1. Download http://wireless.fcc.gov/uls/data/complete/l_amat.zip
// 2. Unzip into a temp directory
// 3. Run 'php fcc-util.php' with an argument of that temp dir
// 4. In psql:
// 5. SET client_encoding TO latin1;
// 6. DELETE from part97;
// 7. \copy part97 FROM /tmp/load-XXXX
//     (where load-XXXX is the filename returned from this tool)

// Get the input directory
$dirname = $argv[1];

// Set up the output file
$lines = 0;
// $lf = tempnam("/tmp", "load-");
$lf = "/tmp/trivnet-fcc.out";
$loadfile = fopen($lf, "w+");

// Open up the individual input files
// EN = Entity
// HD = Header
// AM = Amateur

// Docs:
// https://www.fcc.gov/sites/default/files/public_access_database_definitions_v2.pdf

// HD - Header (fields 1-indexed)
// 2 = Unique ID
// 5 = Call Sign
// 6 = Status

$fh_HD = fopen( $dirname . "HD.dat", "r" ) or die("ERROR: Could not open HD.dat\n");
$fh_EN = fopen( $dirname . "EN.dat", "r" ) or die("ERROR: Could not open EN.dat\n");

// echo "Processing HD.dat\n";
while ( $ech = fgets($fh_HD) ) {
	$data = str_getcsv($ech, "|");
	$lines++;
	// if ( 0 == $lines % 1000 ) { echo "."; } 

	$fccuniqueid = $data[1];
	$call = $data[4];
	$licstat = $data[5];

	if ( $licstat == "A" ) {
		if ( strlen($call) > 0 ) {
			$hams[$fccuniqueid] = $call;	
		} // sanity check the length.
	} // end if
} // end while

$lines = 0;

// echo "\nProcessing EN.dat\n";
while ( $ech = fgets($fh_EN) ) {
	$data = str_getcsv($ech, "|");
	$lines++;
//	if ( 0 == $lines % 1000 ) { echo "."; } 

	$fccuniqueid = $data[1];
	$call = $data[4];
	$name = $data[7];

	if ( isset( $hams[$fccuniqueid] ) ) {
		$str = sprintf("%s\t%s\t0\n", $call, $name);
		fwrite( $loadfile, $str);
	} // end if
} // end while

fclose($fh_EN);
fclose($fh_HD);
fclose($loadfile);

echo "$lf\n";
?>
