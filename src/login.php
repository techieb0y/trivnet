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
	function userSearch() {
		// get_rn_by_cs.php?cs=KD8GBL
		username = document.getElementById("username");
		authrow = document.getElementById("authrow");

		loginbtn = document.getElementById("loginbtn");
		loginpage = document.getElementById("loginpage");

		tactical = document.getElementById("tactical");

		// Reset the auth chalange row
		authrow.innerHTML = "";

		req=new XMLHttpRequest();
		url = 'agents/validate-username.php?username=' + username.value;
		req.open("GET", url, false);
		req.send();
		rslt = JSON.parse( req.responseText );
		console.log(rslt);

		if ( rslt.id > 0) {
			// We got an ID for the username, so they exist.

			// Next, collect the info depending on credential style
			if ( rslt.type == "totp" ) {
				var textTd = document.createElement("td");
				textTd.innerHTML = "TOTP Code:";

				var inputTd = document.createElement("td");
				var inputBox = document.createElement("input");

				inputBox.setAttribute("size", "6");
				inputBox.setAttribute("name", "totp");
				inputBox.setAttribute("id", "totp");
				inputTd.appendChild(inputBox);

				authrow.appendChild(textTd);
				authrow.appendChild(inputTd);
			} else if ( rslt.type == "bootstrap" ) {
				var textTd = document.createElement("td");
				textTd.innerHTML = "Registration Code:";

				var inputTd = document.createElement("td");
				var inputBox = document.createElement("input");

				inputBox.setAttribute("size", "32");
				inputBox.setAttribute("name", "bootstrap");
				inputBox.setAttribute("id", "bootstrap");
				inputTd.appendChild(inputBox);

				authrow.appendChild(textTd);
				authrow.appendChild(inputTd);
			} else if ( rslt.type == "passkey" ) {
				var passkey = document.createElement("button");
				passkey.setAttribute("name", "passkeyLogin");
				passkey.setAttribute("value", "🔑 Sign In With Passkey");
				authrow.appendChild(passkey);
			} else {
				authtext.innerHTML = "Error!";
				authdata.size=1;
			}

			setSymbol(rslt.symbol);
			tactical.value=rslt.tactical;

			loginbtn.value = "🔑 Sign In";
			loginbtn.hidden = false;
			console.log("User Exists");
			loginpage.action = "authenticate.php";
		} else {
			// We didn't get an ID for the username, offer to register
			loginbtn.value = "🪪 Register";
			console.log("New User");
			loginpage.action = "register.php";
			loginbtn.hidden = false;
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

<form id="loginpage" action="index.php" method=POST>

	<table>
	<tr><td>Username:</td><td> <input type=text name="username" id="username" maxlength=16 size=16 onBlur="javascript:userSearch()"></td></tr>
	<tr id="authrow"></tr>

	<tr><td colspan=2><hr></td></tr>
	<tr><td>Tactical: (<em>optional</em>)</td><td><input type=text maxlength=20 name="tactical" id="tactical"></td></tr>
	<tr><td>Symbol: </td><td><img class="symbol" id="symb" src="symbol/0" onClick="toggleSymbols()">
		<input type="hidden" id="symbol" value="0" name="symbol">
		<?php
			genTablePicker();
		?>
		(<em>click to change</em>)
	</td></tr>
	<tr><td colspan=2><input id="loginbtn" hidden type=submit value=""></td></tr>
	</table>

</form>

</div>

</body>
</html>
