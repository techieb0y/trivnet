<?php
	global $begin;
	$begin = microtime(true);
	require_once("include/config.inc");
	require_once("include/constants.inc");
	require_once("include/db_ops.inc");
	require_once("include/sessions.inc");

	date_default_timezone_set("America/Chicago");

	if ( !isset($_SESSION["symbol"]) ) {
		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$extra = 'login.php';
		header( "Location: https://{$host}{$uri}/{$extra}" );
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta charset="utf-8">
	<title>TrivnetDB - Amateur Radio Information Network</title>
	<link rel="stylesheet" href="common/trivnet.css" />
	<!--link rel="stylesheet" href="css/jquery.mobile-1.2.0.min.css" /-->

	<link rel="stylesheet" href="css/jquery-ui.css" />
	<script src="js/jquery.js"></script>
	<script src="js/jquery-ui.js"></script>
	<script src="common/jquery_msg.js"></script>
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

<form action="message_send.php" method="POST">
	<table><tr>
	<td>From:</td><td><?php echo $user; ?></td></tr>
	<tr><td>To:</td><td><input name="sendto" id="sendto" size=12> ("all" or "*" for broadcast message)</td></tr>
	<tr><td>Message:</td><td><input name="text" size=96 maxlength=255></td></tr>
	<tr><td>Send: </td><td><input type="submit" value="send"></td></tr>
	</table>
</form>

<?php
	// Time and messages window
	echo "<div class=\"messages messagesBig\">\n";
	echo "<a id=\"mesgLoad\" href=\"#\">📡</a>\n";
	echo "<a id=\"olderMsgs\" href=\"#\">⏪️</a>\n";
	echo "<table id=\"messages\">";
	echo "</table>";
	echo "</div>\n";
?>

<b>Users:</b><br>
<table>
	<?php
		$q = "SELECT users.id, users.username, users.display, sessions.symbol, sessions.tactical FROM users,sessions WHERE sessions.userid=users.id ORDER BY users.username ASC";
		$who = query($q);

		foreach ($who as $w) {
			$user = trim($w["username"]);
			$disp = trim($w["display"]);
			$sym = trim($w["symbol"]);
			$tac = trim($w["tactical"]);
			echo "<tr><td><img src=\"symbol/$sym\"></td>";
			echo "<td><a onClick=\"document.getElementById('sendto').value='$user'\">$disp / $tac</a></td>";
			echo "</tr>\n";
		} // end foreach
	?>
</table>
<hr>

<?php require_once("include/foot.inc"); ?>
</body>
</html>
