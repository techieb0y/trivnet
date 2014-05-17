<?php
	require_once("config.inc");
	require_once("constants.inc");
	require_once("db_ops.inc");
	require_once("sessions.inc");

// Trivnetdb 2.0 was designed and built by KD8GBL 

//	The original trivnetdb author used to have web page with more information at:
//	http://msuarc.egr.msu.edu/kb8zqz/packet/

// Make sure we know who we're talking to
session_start();
// session_register("lastmsg");
// session_register("mycall");

if ( isset($_POST["callsign"]) ) {
	// Process login
	$mycall = strtoupper($_POST["callsign"]);
	if ( strlen($mycall) < 1 ) { header("Location: login.php"); }
	$qr = "SELECT name FROM part97 WHERE callsign='$mycall'";
	$r = query($qr);
	if ( count($r) == 1 ) {
		$name = $r[0]["name"];
		$_SESSION["callsign"] = $mycall;
		$_SESSION["realname"] = $name;
		if ( isset($_POST["tactical"]) ) {
			$_SESSION["tactical"] = $_POST["tactical"];
		} // end if
		if ( isset($_POST["symbol"]) ) {
			$_SESSION["symbol"] = $_POST["symbol"];
		} // end if
		start_session();
	} // end if
	if ( "GUEST" == $mycall ) {
		// Guest user
		$name = "Guest user";
		$_SESSION["callsign"] = "Guest";
		$_SESSION["realname"] = "Guest User";
		
		if ( isset($_POST["tactical"]) ) {
			$_SESSION["tactical"] = $_POST["tactical"];
		} // end if
		if ( isset($_POST["symbol"]) ) {
			$_SESSION["symbol"] = $_POST["symbol"];
		} // end if
		start_session();
	} // end if
} // end if 

if ( isset($_SESSION["callsign"]) ) { $mycall = $_SESSION["callsign"]; } else { $mycall = "N0CALL"; }
if ( !has_session() ) {
	header("HTTP/403 Forbidden");
	// header("WWW-Authenticate: Basic");
	$loc_parts = explode($_SERVER["REQUEST_URI"], "/");
	$loc_parts = array_slice($loc_parts, 0, -1);
	$loc_parts[] = "login.php";
	$loc = implode("/", $loc_parts);
	header("Location: " . $loc);
} // end if
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta charset="utf-8">
	<title>TrivnetDB - Amateur Radio Information Network</title>
	<link rel="stylesheet" href="common/trivnet.css" />

	<!--link rel="stylesheet" href="css/jquery.mobile-1.2.0.min.css" /-->
	<link rel="stylesheet" href="css/flick/jquery-ui-1.8.24.custom.css" />

	<script src="js/jquery.js"></script>
	<script src="js/jquery-ui-1.8.24.custom.min.js"></script>
	<!--script src="js/jquery.mobile-1.2.0.min.js"--><!--/script-->
	
	<script src="common/jquery.js"></script>
</head>

<body>
	<!-- Page Header -->
	<!-- Menu -->
	<div class="whoami">
	<?php
		$name = $_SESSION["realname"];
		$tac = $_SESSION["tactical"];
		$call = $_SESSION["callsign"];
		if ( isset($_SESSION["symbol"]) ) { $sym = $_SESSION["symbol"]; } else { $sym = 0; }
		echo "<img src=\"getAPRS.php?sym=$sym\">&nbsp;";
		echo "$name/<b>$tac</b> <i>$call</i> <a href=\"logout.php\">[logout]</a>\n";
	?>
	</div>
	<script>var callsign = <?php echo "'$call'" ?></script>
	<table class="menu">
	<tr class="menu">
		<td> <a class="menu" href="index.php">Home</a> </td>
		<td> <a class="menu" href="query.php">Search</a> </td>
		<td> <a class="menu" href="multiedit.php">Multi-Edit</a> </td>
		<td> <a class="menu" href="turboedit.php">Turbo Edit</a> </td>
		<td> <a class="menu" href="batch.php">Batch Edit</a> </td>
		<td> <a class="menu" href="messaging.php">Messaging</a> </td>
		<td> <a class="menu" href="admin.php">Admin</a> </td>
	</tr>
	</table>

<?php
	// Time and messages window
	echo "<div id=\"messages\" class=\"messages\">\n";
	echo "<table>\n";
	echo "<a href=\"#\"><img class=\"topRight\" id=\"mesgLoad\" src=\"images/refresh.png\"></a>\n";
	echo "</table>\n";
	echo "</div>\n";

	// echo "<pre>"; print_r($_SESSION); echo "</pre>\n";

?>
	<hr>
	<!-- end head.inc -->
