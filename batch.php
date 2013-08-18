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
</td> <td>
Update status to: 
<select name="status">
<option value="">---
<?php
	$r = query("SELECT id, status from statustypes");
	foreach ($r as $row) {
		$id = $row["id"];
		$text = $row["status"];
		echo "<option value=\"$id\">$text</option>\n";
	} // end foreach
?>
</select><br><br>
and message to: 
<select name="quickmesg">
<?php
	$r = query("SELECT text from quickmesg");
	foreach ($r as $row) {
		$text = $row["text"];
		echo "<option value=\"$text\">$text</option>\n";
	} // end foreach
?>
</select>
<br>...or use custom: <input name="custom" size=40><br>
<i>(overrides selected message)</i>

<br><br>
Mile marker: <input type=text size=4 name="mile">
<br>

<div id="base" style="display: none;">
<input type=text id="base" name="searchkey[]" disabled size=6>
</div>

<input type=submit value="Perform Updates">
</td></tr></table>

</form>

</body></html>
