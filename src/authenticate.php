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

	$qr = "SELECT id FROM users WHERE username='$username'";
	$r = query($qr);
	if ( count($r) > 0 ) {
		// We found a user with this username, a good start.

			$id = $r[0]["id"];

			$qc = "SELECT * FROM credentials WHERE id='$id'";
			$rc = query($qc);

			if ( count($rc) > 0 ) {
				// ...and there are credential mapping entries for the user, even better!

				foreach ( $rc as $cred ) {
					$type = $cred["type"];
					$date = $cred["data"];

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

?>

</body>
</html>
