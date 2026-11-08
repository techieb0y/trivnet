<?php
	$authdata = apache_request_headers();
	$display = $authdata["OIDC_CLAIM_display_name"];
	$username = $authdata["OIDC_CLAIM_preferred_username"];

	require_once("include/constants.inc");
	require_once("include/db_ops.inc");
	require_once("include/config.inc");

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

		if ($FLAGS["DISABLED"] & $auth) {
			header("HTTP/401 Unauthorized");
			echo "<b>This account has been disabled.</b><br>\n";
		} else {
			if (empty($_SESSION['token'])) {
				$token = bin2hex(random_bytes(32));
				session_name($token);
				session_start();
			}

			$_SESSION["token"] = session_id();
			$_SESSION["username"] = $username;
			$_SESSION["realname"] = $display;
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
			// print_r($rc);

			$q4 = "INSERT INTO messages VALUES ( \$1, \$2, \$3, \$4, 'logged on', 'Sysop' );";

			$p2[] = time();				// 3
			$p2[] = $username;	// 6
			$p2[] = $_POST["symbol"];	// 4
			$p2[] = $_POST["tactical"];	// 5

			$rc = pg_query_params( connect(), $q4, $p2 );
			// print_r($rc);

			$host  = $_SERVER['HTTP_HOST'];
			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = 'index.php';
			header( "Location: https://{$host}{$uri}/{$extra}" );
		}
	} else {
		// No ID found for this username; auto-create one that's disabled
		$q = "INSERT INTO users VALUES ( (SELECT MAX(id)+1 FROM users), \$1, \$2, \$3 )";

		unset($p);
		
		$p[] = $username;
		$p[] = $display;
		$p[] = $FLAGS["DISABLED"];

		$r = pg_query_params( connect(), $q, $p );
		// print_r($r);

		header("HTTP/401 Unauthorized");
		echo "<br>A Trivnet user has been auto-created; contact an admin to have it enabled.\n";
	}
?>