<?php
	require("include/config.inc");
	require("include/constants.inc");
	require("include/db_ops.inc");
	require("include/sessions.inc");

// Trivnetdb 2.0 was designed and built by KD8GBL 

//	The original trivnetdb author has a web page with more inforamtion at:
//	http://msuarc.egr.msu.edu/kb8zqz/packet/

// Make sure we know who we're talking to
session_start();
// session_register("lastmsg");
// session_register("mycall");

if ( isset($_POST["callsign"]) ) {
	// Process login
	$mycall = $_POST["callsign"];
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
} // end if 

if ( isset($_SESSION["callsign"]) ) { $mycall = $_SESSION["callsign"]; } else { $mycall = "N0CALL"; }
if ( !has_session() ) {
	header("HTTP/401 Unauthorized");
	header("Location: login.php");
} // end if

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title>TrivnetDB - Amateur Radio Information Network</title>
	<link rel="stylesheet" href="common/trivnet.css" />
	<link rel="stylesheet" href="css/flick/jquery-ui-1.8.24.custom.css" />
	<script src="js/jquery.js"></script>
	<script src="js/jquery-ui-1.8.24.custom.min.js"></script>
	<script src="/common/jquery_msg.js"></script>
</head>

<body>
	<div class="whoami">
	<?php
		$name = $_SESSION["realname"];
		$tac = $_SESSION["tactical"];
		$call = $_SESSION["callsign"];
		if ( isset($_SESSION["symbol"]) ) { $sym = $_SESSION["symbol"]; } else { $sym = 0; }
		echo "<img src=\"symbol/$sym\">&nbsp;";
		echo "$name/<b>$tac</b> <i>$call</i> <a href=\"logout.php\">[logout]</a>\n";
	?>
	<script>var callsign = <?php echo "'$call'" ?></script>
	</div>

	<!-- Menu -->
	<table class="menu">
	<tr class="menu">
		<td> <a class="menu" href="index.php">Home</a> </td>
		<td> <a class="menu" href="query.php">Search</a> </td>
		<td> <a class="menu" href="multiedit.php">Multi-Edit</a> </td>
   		<td> <a class="menu" href="batch.php">Batch Edit</a> </td>
		<td> <a class="menu activetab" href="messaging.php">Messaging</a> </td>
		<td> <a class="menu" href="admin.php">Admin</a> </td>
	</tr>
	</table>

<form action="message_send.php" method="POST">
	<table><tr>
	<td>From:</td><td><?php echo $mycall; ?></td></tr>
	<tr><td>To:</td><td><input name="sendto" id="sendto" size=12> ("all" or "*" for broadcast message)</td></tr>
	<tr><td>Message:</td><td><input name="text" size=96 maxlength=255></td></tr>
	<tr><td>Send: </td><td><input type="submit" value="send"></td></tr>
	</table>
</form>

<?php
	// Time and messages window
	echo "<div class=\"messages messagesBig\">\n";
	echo "<a id=\"mesgLoad\" href=\"#\">🔄</a>\n";
	echo "<table id=\"messages\">";
	echo "</table>";
	echo "</div>\n";
?>

<b>Users Online:</b><br>
<table>
	<?php
		$who = list_sessions();

		if ( count($who) > 0 ) {
		foreach ($who as $w) {
			if ( $w["age"] < $config["idle_timeout"] ) {
				$call = trim($w["callsign"]);
				$sym = trim($w["symbol"]);
				$tac = trim($w["tactical"]);
				echo "<tr><td><img src=\"symbol/$sym\"></td>";
				echo "<td><a onClick=\"document.getElementById('sendto').value='$call'\">$call / $tac</a></td>";
				echo "</tr>\n";
			} else {
				$call = trim($w["callsign"]);
				echo "<tr><td>&nbsp;</td><td><font color=\"grey\">$call</font></td></tr>\n";
			} // end if
		} // end foreach
		} // end if
	?>
</table>

<hr>

<?php require_once("include/foot.inc"); ?>
</body>
</html>
