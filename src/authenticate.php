<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta charset="utf-8">
</head>

<body>

<?php
	require_once("include/db_ops.inc");
	require_once("include/config.inc");

	$username = $_POST["username"];

	$q = "SELECT id FROM users WHERE username = \$1";
	$p[] = $username;
	$res = pg_query_params( connect(), $q, $p );

	if (pg_num_rows($res) > 0 ) {
		$r = array();
		while ( $z = pg_fetch_assoc($res) ) {
			$r[] = $z;
		} // end while
	}

	if ( count($r) > 0 ) {
		// We found a user with this username, a good start.

			$id = $r[0]["id"];

			$qc = "SELECT * FROM credentials WHERE id = \$1";
			$pc[] = $id;
			$rc = pg_query_params( connect(), $qc, $pc );

			if (pg_num_rows($rc) > 0 ) {
				$r2 = array();
				while ( $z = pg_fetch_assoc($rc) ) {
					$r2[] = $z;
				} // end while
			}

			if ( count($r2) > 0 ) {
				// ...and there are credential mapping entries for the user, even better!

				foreach ( $r2 as $cred ) {
					$type = $cred["type"];
					$data = $cred["data"];

					echo "User ID: $id<br>\n";
					echo "Credential Type: $type\n";
					echo "Credential Data: $data\n";
					echo "<br>\n"; 
				}

			}
	} else {
		// No ID found for this username
		echo "Login Failed";
	}

echo "<hr>\n";
echo "<pre>\n";
print_r($_POST);
echo "</pre>\n";

?>

</body>
</html>
