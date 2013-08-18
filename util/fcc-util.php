<?php

// Utility to update our cache of FCC license data

// 1. Download http://wireless.fcc.gov/uls/data/complete/l_amat.zip
// 2. Unzip into a temp directory
// 3. Run 'php fcc-util.php' with an argument of that temp dir
// 4. In psql:
// 5. SET client_encoding TO latin1;
// 6. DELETE from part97;
// 7. COPY part97 FROM /tmp/load-XXXX
//     (where load-XXXX is the filename returned from this tool)

// Get the input directory
$dirname = $argv[1];

// Set up the output file
$lines = 0;
$lf = tempnam("/tmp", "load-");
$loadfile = fopen($lf, "w+");

// Open up the individual input files
// EN = Entity
// HD = Header

$fh_HD = fopen( $dirname . "HD.dat", "r" ) or die("ERROR: Could not open HD.dat\n");
$fh_EN = fopen( $dirname . "EN.dat", "r" ) or die("ERROR: Could not open EN.dat\n");

echo "Processing HD.dat\n";
while ( $ech = fgets($fh_HD) ) {
	$data = str_getcsv($ech, "|");
	$lines++;
	if ( 0 == $lines % 1000 ) { echo "."; } 

	$call = $data[4];
	$licstat = $data[5];

	if ( $licstat == "A" ) {
		if ( strlen($call) > 0 ) {
			$hams[$call] = "A";	
		} // sanity check the length.
	} // end if
} // end while

$lines = 0;

echo "\nProcessing EN.dat\n";
while ( $ech = fgets($fh_EN) ) {
	$data = str_getcsv($ech, "|");
	$lines++;
	if ( 0 == $lines % 1000 ) { echo "."; } 

	$call = $data[4];
	$name = $data[7];

	if ( isset( $hams[$call] ) && ( $hams[$call] == "A" ) ) {
		$str = sprintf("%s\t%s\n", $call, $name);
		fwrite( $loadfile, $str);
		$hams[$call] = "B";
	} // end if
} // end while

fclose($fh_EN);
fclose($fh_HD);
fclose($loadfile);

echo "\nDone preparing load file $lf\n";
?>
