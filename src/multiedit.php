<?php
	require_once("include/head.inc");
	require_once("include/config.inc");
	require_once("include/db_ops.inc");
?>

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
<span id="demostatusIcon"><span>
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
<span name="demoinfoIcon" id="demoinfoIcon">▶️</span>
</td>
</tr>
</table>
<ol>
<li>Type search critera in box 1. Press tab.
<li>Verify name against box 2.
<li>Type status message into box 3. Press tab.
<li>Verify status message is correct.
<li>Click "Save"
<li>Enter new search critera for next update in newly-created entry box in the next row.
</ol>
</form>

<hr>

<script>
makeClone();
fixFocus();
</script>

<?php require_once("include/foot.inc"); ?>
</body></html>
