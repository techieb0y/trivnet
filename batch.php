<?php
	require_once("include/head.inc");
	require_once("include/config.inc");
	require_once("include/db_ops.inc");
?>

<script type="text/javascript" src="common/batchmode.js"></script>

<form name=batchupdates id=output method=post action="do_batch.php" enctype="multipart/form-data">

<table>
<tr>
<th style="border-right: 1px dashed black">1.</td>
<th>2.</td>
</tr>
<tr>
<td style="border-right: 1px dashed black">
<?php
$q = "SELECT * FROM datatypes WHERE typeid > 0";
$r = query($q);
foreach($r as $row) {
	$typeid = $row["typeid"];
	$name = $row["label"];
	$dt[$typeid] = $name;
}

	echo "Enter a <select name=\"datatype\">\n";

		foreach($dt as $i => $j) {
			if ($i == $config["multidefault"] ) {
				echo "<option value=\"$i\" selected>$j</option>\n";
			} else {
				echo "<option value=\"$i\">$j</option>\n";
			} // end if
		} // end foreach

	echo "</select>, <b>newline</b>-separated.<br>\n";
?>

<textarea name="searchkeys" id="searchkeys" columns=12 rows=20></textarea>
<br>(or from a text file: <input type=file name="searchkey_file">)
<br>Repeated file behavior:
<input type=radio name="autodiff" value="on" checked id="autodiffon"><label for="autodiffon">Incremental</label>
<input type=radio name="autodiff" value="off" id="autodiffoff"><label for="autodiffoff">Authoratative</label>
</td>
<td>
Update Data values: 
<table>
<?php
$_dts = query("SELECT * from datatypes order by typeid");

foreach( $_dts as $_dt ) {
	$typeid = $_dt["typeid"];
	$label = $_dt["label"];
	$isEnum = $_dt["enum"];
	echo "<tr>";
	if ( $typeid == $config["message"] || $typeid == $config["status"] ) {
		echo "<td><input type=\"checkbox\" checked id=\"updateType[$typeid]\" name=\"updateType[$typeid]\" value=\"true\"></td>\n";
	} else {
		echo "<td><input type=\"checkbox\" id=\"updateType[$typeid]\" name=\"updateType[$typeid]\" value=\"true\"></td>\n";
	} // end if
	echo "<td><label for=\"updateType[$typeid]\">$label</label></td>\n<td>";


	echo "<td><label for=\"updateType[$typeid]\">";
	// Messages can be enum or custom, so they're a bit of a special case.
	if ( $typeid == $config["message"] ) {
		echo "<select name=\"quickmesg\">";
		$r = query("SELECT text from quickmesg");
		foreach ($r as $row) {
			$text = $row["text"];
			// FIXME: finish refactoring quick-messages into the message datatype
			if ( preg_match("/crossed/i", $text) ) {
				echo "<option selected value=\"$text\">$text</option>\n";
			} else {
				echo "<option value=\"$text\">$text</option>\n";
			} // end if
		} // end foreach
		echo "</select>";
		echo "<br>...or use custom: <input name=\"custom\" size=40><br><i>(overrides selected message)</i>\n";
		echo "<br>Mile marker: <input type=text size=4 name=\"mile\">\n";
	} else {
		// General case of not the status text
		// Can be enum or not.
		if ( 't' == $isEnum ) {
			echo "<select name=\"value[$typeid]\">\n";
			echo "<option selected disabled>Select One</option>\n";
			$r = query("SELECT * from enumtypes WHERE datatype=$typeid");
			foreach ( $r as $row ) {
				$val = $row["id"];
				$text = $row["value"];
				if ( ( $typeid == $config["status"] && $val == $config["finishstatus"] ) ) {
					echo "<option name=\"value[$typeid]\" selected value=\"$val\">$text</option>\n";
				} else {
					echo "<option name=\"value[$typeid]\" value=\"$val\">$text</option>\n";
				} // end if
			} // end foreach
			echo "</select>\n";
		} else {
			echo "<input size=32 name=\"value[$typeid]\">\n";
		} // end if
	} // end if

	echo "</label></td></tr>\n";
} // end foreach
echo "</table>";
?>

<div id="base" style="display: none;">
<input type=text id="base" name="searchkey[]" disabled size=6>
</div>

<br>

<input type=submit value="Perform Updates">
</td></tr></table>
</form>

<?php require_once("include/foot.inc"); ?>
</body></html>
