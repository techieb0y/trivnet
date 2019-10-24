<?php
	require_once("include/head.inc");
	require_once("include/config.inc");
	require_once("include/db_ops.inc");
?>

<script type="text/javascript" src="common/domTraverse.js"></script>
<script type="text/javascript" src="common/batchmode.js"></script>

<form name=batchupdates id=output method=post action="" enctype="multipart/form-data">

<input type=hidden id="counter" value=0>
<input type=hidden id="defaulttype" value="<?php echo $config["multidefault"]; ?>">

<?php
	$q = "SELECT label FROM datatypes WHERE typeid=" . $config["multidefault"];
	$r = query($q);
	$searchtype = $r[0]["label"];
	echo "Search on <b>$searchtype</b> only.\n";
?>
<br>

<div>
<table style="display: none;">
<tr id="template">
<td id="icon_td">
<img width=32 height=32 src="images/blank.png" id="statusIcon">
</td>
<td id="search_td">
<input name="search[]" id="search" size=6 onBlur="javascript:personSearch(this)">
</td>
<td id="display_td">
<input name="diplayName[]" id="displayName" size=50 disabled="disabled">
<input name="personID[]" type=hidden id="personID">
</td>
<td id="status_td">
<input name="status[]" id="status" size=50 onBlur="javascript:personSearch(this)">
<?php
	// <select size=5 class="magicBox" name="presetStatus0" id="presetStatus0">
	// $r = query("SELECT text from quickmesg");
	// foreach ($r as $row) {
	//	$text = $row["text"];
	//	echo "<option value=\"$text\">$text</option>\n";
	// } // end foreach
?>
</select>
</td>
<td id="go_td">
<img type="image" src="images/save_button.png" style="display: none" name="infoIcon" id="infoIcon" onClick="javascript:postStatus(this)">
</td>
</tr>
</table>
</div>

<table>
<div id="outParent">
</div>
</table>
</form>

Directions:
<form name=demo id=demo method=post action="" enctype="multipart/form-data">
<table>
<tr id="demo">
<td id="demo_icon_td">
<img width=32 height=32 src="images/blank.png" id="demostatusIcon">
</td>
<td id="demosearch_td">
<input name="demosearch[]" id="demosearch" disabled="disabled" size=6 value="Box 1">
</td>
<td id="demodisplay_td">
<input name="demodiplayName[]" id="demodisplayName" size=50 disabled="disabled" value="Box 2: Check Name Here">
</td>
<td id="status_td">
<input name="demostatus[]" id="demostatus" size=50 disabled="disabled" value="Box 3: Status Update Goes Here">
</td>
<td id="go_td">
<img type="image" src="images/save_button.png" name="demoinfoIcon" id="demoinfoIcon">
</td>
</tr>
</table>
<ol>
<li>Type search critera in box 1. Press tab.
<li>Verify name against box 2.
<li>Type status message into box 3. Press tab.
<li>Verify status message is correct.
<li>Click "Save"
<li>Enter new search criter for next update in newly-created entry box in the next row.
</ol>
</form>

<hr>

<script>
makeClone();
fixFocus();
</script>

<?php require_once("include/foot.inc"); ?>
</body></html>
