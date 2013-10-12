<?php
	require_once("include/config.inc");
	require_once("include/db_ops.inc");

	require_once("include/head.inc");
?>

<script type="text/javascript" src="js/jquery.js"></script>

<script type="text/javascript">
	var c = 0;
	var cloned;
	var tmpl;

	function makeMoreRows(event) {
		tmpl = $( "#template" );
		cloned = tmpl.clone(true,true);
		cloned.attr("id", "id"+(++c) );
		cloned.show();
		cloned.insertBefore("#terminator");
		cloned.find(".bib").focus();

		whoami = $( event.target );
		myRow = whoami.parent().parent();
		myRow.find("img").attr("src", "images/spinner-small.gif");

		postData(myRow);
	}

	function postData(_which) {
		myRowId = _which[0].id;
		bibNum = _which.find("input.bib")[0].value;
		statusMsg = _which.find("input.status")[0].value;
		data = '{ "id": "' + myRowId + '", "searchKey" : "' + bibNum + '", "data" : "' + statusMsg  + '" }';
		console.out(data);
		$.ajax({
			type: "POST",
			dataType: "json",
			url: "agents/set_status_json.php",
			success: processResult,
			data: data
		});
	}

	function fixFocus() {
		$( "in.bib:last" ).focus();
	}

	function processResult (_data, _status, _jqXHR) {
		id = _data.id;
		if ( _data.result == 'OK' ) {
			$( "#" + id ).remove();
		} else {
			$( "#" + id ).find("img").attr("src", "images/warning.png");
		}
	}

	$(function() {
		$( "input.status" ).blur( makeMoreRows );
		$( "#fix" ).click( fixFocus );
	});

</script>
</head>
<body>

<div>
<table>
<!-- instructions go here -->
	<tr id="header">
		<th id="icon_td">
			&nbsp;
		</th>
		<th id="search_td">
			<input class="bib" name="search[]" id="search" size=6 disabled value="Bib Number">
		</th>
		<th id="status_td">
			<input class="status" name="status[]" id="status" size=50 disabled value="Status">
		</th>
	</tr>

<!-- this is the fist one the user sees -->

	<tr id="id0">
		<td id="icon_td">
			<img width=32 height=32 src="images/blank.png" id="statusIcon">
		</td>
		<td id="search_td">
			<input class="bib" name="search[]" id="search" size=6>
		</td>
		<td id="status_td">
			<input class="status" name="status[]" id="status" size=50>
		</td>
	</tr>

<!-- magic parts go here -->

	<tr id="template" style="display: none">
		<td id="icon_td">
			<img width=32 height=32 src="images/blank.png" id="statusIcon">
		</td>
		<td id="search_td">
			<input class="bib" name="search[]" id="search" size=6>
		</td>
		<td id="status_td">
			<input class="status" name="status[]" id="status" size=50>
		</td>
	</tr>
	<tr id="terminator">
	</tr>
</table>
</div>

<input id="fix" type=button value="Fix">

</body></html>
