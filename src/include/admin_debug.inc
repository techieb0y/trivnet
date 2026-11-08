<?php 

	echo "<div id=\"tabs9\">\n";
	echo "Session ID: " . session_id() . "<br>\n";
	echo "<pre>";
	print_r($_SESSION);
	echo "</pre>\n";
	// print_r( list_sessions() );

	echo "<br>\n";
	$authdata = apache_request_headers();
	$exp = $authdata["OIDC_CLAIM_exp"];
	$delta = $exp - time();
	echo "OIDC Session expires at {$exp} (in {$delta} seconds)<br>\n";
	echo "<br>\n";

	echo "<table border=1 width=\"66%\">";
	echo "<tr><th colspan=2>Username</th><th>Tactical Call</th><th>Age (sec)</th><th>Timestamp</th></tr>\n";
	foreach ( list_sessions() as $ses ) {
		if ( $ses["sessionid"] == session_id() ) { $isThisSess = "*"; } else { $isThisSess = ""; }
		echo "<tr><td>$isThisSess</td><td>" . $ses["userid"] . "</td><td>" . $ses["tactical"] . "</td><td>". sprintf("%.2f", $ses["age"]) . "</td><td>" . $ses["timestamp"] . "</td></tr>\n";
	} // end foreach
	echo "</table>";

	echo "</div>";

?>
