<?php
	global $begin;
	$begin = microtime(true);
	require_once("config.inc");
	require_once("constants.inc");
	require_once("db_ops.inc");
	require_once("sessions.inc");

date_default_timezone_set("America/Chicago");

// Trivnetdb 2.x was designed and built by KD8GBL

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta charset="utf-8">
	<title>TrivnetDB - Amateur Radio Information Network</title>
	<link rel="stylesheet" href="common/trivnet.css" />
	<!--link rel="stylesheet" href="css/jquery.mobile-1.2.0.min.css" /-->

<?php
	if ( "true" != getenv("slownet") ) {
		echo "	<link rel=\"stylesheet\" href=\"css/jquery-ui.css\" />\n";
		echo "	<script src=\"js/jquery.js\"></script>\n";
		echo "	<script src=\"js/jquery-ui.js\"></script>\n";
		echo "	<script src=\"js/jquery.js\"></script>\n";
	} // end if
?>
</head>

<body>
	<!-- Page Header -->
	<!-- Menu -->
	<div class="whoami">
	<?php
		$name = $_SESSION["realname"];
		$tac  = $_SESSION["tactical"];
		$user = $_SESSION["username"];
		$auth = $_SESSION["auth"];
		if ( isset($_SESSION["symbol"]) ) { $sym = $_SESSION["symbol"]; } else { $sym = 0; }
		echo "<img src=\"symbol/$sym\">&nbsp;";
		echo "$name/<b>$tac</b> <i>$user</i> <a href=\"logout.php\">[logout]</a>\n";
	?>
	</div>
	<script>var callsign = <?php echo "'$user'" ?></script>

	<table class="menu">
	<tr class="menu">
	<?php
		$tabs["index.php"] = "Home";
		$tabs["query.php"] = "Search";
		$tabs["multiedit.php"] = "Multi-Edit";
		// $tabs["turboedit.php"] = "Turbo Edit";

		if ( $FLAGS["BATCH"] & $auth ) {
			$tabs["batch.php"] = "Batch Edit";
		}

		$tabs["messaging.php"] = "Messaging";

		if ( $FLAGS["ADMIN"] & $auth ) {
			$tabs["admin.php"] = "Admin";
		}

		foreach ( $tabs as $taburl => $tabname ) {
			if ( strpos( $_SERVER["PHP_SELF"], $taburl ) ) { 
				echo "<td> <a class=\"menu activetab\" href=\"$taburl\">$tabname</a></td>";
			} else {
				echo "<td> <a class=\"menu\" href=\"$taburl\">$tabname</a></td>";
			}
		}
	?>
	</tr>
	</table>

<?php
	// Time and messages window
	echo "<div class=\"messages\">\n";
	echo "<table id=\"messages\">\n";
	echo "<a href=\"#\" id=\"mesgLoad\">📡</a>\n";
	echo "</table>\n";
	echo "</div>\n";
	
	// echo "<pre>"; print_r($_SESSION); echo "</pre>\n";

?>
	<hr>
	<!-- end head.inc -->
