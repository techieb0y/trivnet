<?php
	ob_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta charset="utf-8">
</head>

<body>

<?php
	require_once("include/constants.inc");
	require_once("include/db_ops.inc");
	require_once("include/config.inc");

	$username = $_POST["username"];

	$q = "SELECT id,auth FROM users WHERE username = \$1";
	$p[] = $username;
	$res = pg_query_params( connect(), $q, $p );

	$r = array();
	if (pg_num_rows($res) > 0 ) {
		while ( $z = pg_fetch_assoc($res) ) {
			$r[] = $z;
		} // end while
	}

	if ( count($r) > 0 ) {
		// We found a user with this username, a good start.

			$id = $r[0]["id"];
			$auth = $r[0]["auth"];

			$qc = "SELECT * FROM credentials WHERE id = \$1";
			$pc[] = $id;
			$rc = pg_query_params( connect(), $qc, $pc );

			$r2 = array();
			if (pg_num_rows($rc) > 0 ) {
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
					echo "Authorization Data: $auth\n";
					echo "<br>\n";
				}

				if ($FLAGS["DISABLED"] & $auth) {
					echo "<b>This account has been disabled.</b><br>\n";
				} else {
					// "Simple" case: they have a new-user token
					$authdata = json_decode($data, true);

					echo "<pre>\n";
					print_r($authdata);
					echo "</pre>\n";

					if ( time() > $authdata["expire"] ) {
						echo "Expired!<br>\n";
					} else {
						$bootstrap = $_POST["bootstrap"];
							$salt = $authdata["salt"];
						if ( sha1($salt . $bootstrap) === $authdata["sha1"] ) {
							echo "Success!";

							if (empty($_SESSION['token'])) {
								$token = bin2hex(random_bytes(32));
								session_name($token);
								session_start();
							}

							$_SESSION["token"] = session_id();
							$_SESSION["username"] = $_POST["username"];
							$_SESSION["symbol"] = $_POST["symbol"];
							$_SESSION["tactical"] = $_POST["tactical"];
							$_SESSION["auth"] = $auth;

							$q3 = "INSERT INTO sessions VALUES ( \$1, \$2, \$3, \$4, \$5 );";

							$p4[] = session_id();		// int
							$p4[] = $id;				// int
							$p4[] = time();				// bigint
							$p4[] = $_POST["symbol"];	// int
							$p4[] = $_POST["tactical"];	// text

							$rc = pg_query_params( connect(), $q3, $p4 );
							print_r($rc);

							$q4 = "INSERT INTO messages VALUES ( \$1, \$2, \$3, \$4, 'logged on', 'Sysop' );";

							$p2[] = time();				// 3
							$p2[] = $_POST["username"];	// 6
							$p2[] = $_POST["symbol"];	// 4
							$p2[] = $_POST["tactical"];	// 5

							$rc = pg_query_params( connect(), $q4, $p2 );
							print_r($rc);

							header("Location", "index.html");
						} else {
							echo "Fail!";
						}
					}
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

<?php
ob_end_flush();
?>