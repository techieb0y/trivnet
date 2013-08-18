<?php

// Who's Where?

// In med tent

$r_med = query("SELECT statustypes.id, count(people.id) AS num FROM statustypes, people WHERE statustypes.id=people.status GROUP BY statustypes.id ORDER BY statustypes.id");

foreach($r_med as $row) {
	$type = $row["id"];
	$r_sn = query("SELECT status FROM statustypes WHERE id=$type");
	echo "Status: " . $r_sn[0]["status"];

	echo "<ul>\n";

	// For each status type, get who's got that type set
	$r_st = query("SELECT id FROM people WHERE status=$type");
	foreach( $r_st as $r ) {

		$r_p = query("SELECT datatype,value FROM persondata WHERE datatype=3 or datatype=4 AND personid=" . $r["id"]);
		foreach( $r_p as $p ) {
			$dt = $p["datatype"];
			$vl = $p["value"];
			$things[$dt] = $vl;
		} // end foreach
		
		foreach($things as $p_key=>$p_row) {
			if ( $p_key == 3 ) { $fname = $p_row; }
			if ( $p_key == 4 ) { $lname = $p_row; }
		} // end foreach

		echo "<li><a href=\"detail.php?id=" . $r["id"] . "\">$fname $lname</a>\n";

	} // end foreach

	echo "</ul>\n";

} // end foreach

?>
