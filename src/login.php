<?php
require_once("include/aprs-symbols.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta charset="utf-8">

<style>
.loginWindow { width: 512px; padding: 15px; border: 1px solid black; border-radius: 15px; -moz-border-radius: 15px; }
.symbolpicker { width: 122px; height: 384x; position: absolute; z-index: 5; border: 1px black solid; }
div.symbolpicker { display: none; background: white; }
div.symbolpickershow { display: inline; }
</style>

<script>
function hamSearch() {
	// get_rn_by_cs.php?cs=KD8GBL
	callsign = document.getElementById("callsign");
	realname = document.getElementById("realname");
	realname.innerHTML = '<img src=images/spinner-small.gif>';
	req=new XMLHttpRequest();
	url = 'agents/get_rn_by_cs.php?cs=' + callsign.value;
	req.open("GET", url, false);
	req.send();
	rslt = JSON.parse( req.responseText );
	if ( rslt.name ) {
		realname.innerHTML = rslt.name;
		setSymbol(rslt.symbol);
	} else {
		realname.innerHTML = '⚠️ Error fetching name';
	} // end if
} // end doAjax

	function toggleSymbols() {
		document.getElementById("symbolpicker").classList.toggle("symbolpickershow");
	}

	function setSymbol(which) {
		document.getElementById("symb").src= 'symbol/' + which;
		document.getElementById("symbol").value = which;
	}
</script>
</head>

<body>

<div class="loginWindow">

<form action="index.php" method=POST>

	<table>
	<tr><td>Callsign:</td><td><input type=text name="callsign" id="callsign" maxlength=10 size=10 onBlur="javascript:hamSearch()"> (<i>or enter 'guest')</i></td></tr>
	<tr><td>Name:</td>    <td><div name="realname" id="realname"></td></tr>
	<tr><td>Tactical: (<em>optional</em>)</td><td><input type=text maxlength=20 name="tactical" id="tactical"></td></tr>
	<tr><td>Symbol: </td><td><img class="symbol" id="symb" src="symbol/0" onClick="toggleSymbols()">
		<input type="hidden" id="symbol" value="0" name="symbol">
		<?php
			genTablePicker();
		?>
		(<em>click to change</em>)
	</td></tr>
	<tr><td colspan=2><input type=submit value="Login"></td></tr>
	</table>

</form>

</div>

</body>
</html>
