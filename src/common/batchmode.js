function makeClone() {
	counter = document.getElementById("counter").value;

	var newRow = document.createElement("tr");

	// Spinner space
	var newIconTd = document.createElement("td");
	newIconTd.innerHTML = "";
	newIconTd.setAttribute("id", "statusIcon" + counter);

	// Enter a probably-a-bib-number
	var newSearchTd = document.createElement("td");
	var newSearchInput = document.createElement("input");
	newSearchInput.setAttribute("name", "search[]");
	newSearchInput.setAttribute("size", "6");
	newSearchInput.setAttribute("onBlur", "javascript:personSearch(this)");
	newSearchInput.setAttribute("id", "search" + counter);
	newSearchTd.appendChild(newSearchInput);

	// Show the display name
	var newDisplayTd = document.createElement("td");
	var newDisplayInput = document.createElement("input");
	newDisplayInput.setAttribute("name", "displayName[]");
	newDisplayInput.setAttribute("size", "50");
	newDisplayInput.setAttribute("disabled", "disabled");
	newDisplayInput.setAttribute("id", "displayName" + counter);

	var newPidInput = document.createElement("input");
	newPidInput.setAttribute("name", "personId[]");
	newPidInput.setAttribute("type", "hidden");
	newPidInput.setAttribute("id", "personID" + counter);

	newSearchTd.appendChild(newDisplayInput);
	newSearchTd.appendChild(newPidInput);

	// This is where you type the message
	var newStatusTd = document.createElement("td");
	var newStatusInput = document.createElement("input");
	newStatusInput.setAttribute("name", "status[]");
	newStatusInput.setAttribute("size", "50");
	newStatusInput.setAttribute("onBlur", "javascript:personSearch(this)");
	newStatusInput.setAttribute("id", "status" + counter);
	newStatusTd.appendChild(newStatusInput);

	// Save button
	var newSaveButton = document.createElement("button");
	newSaveButton.setAttribute("id", "saveButton" + counter);
	newSaveButton.innerHTML = "▶️";
	newSaveButton.setAttribute("onClick", "javascript:postStatus(this)");

	var newSaveTd = document.createElement("td");
	newSaveTd.appendChild(newSaveButton);

	newRow.appendChild(newIconTd);
	newRow.appendChild(newSearchTd);
	newRow.appendChild(newDisplayTd);
	newRow.appendChild(newStatusTd);
	newRow.appendChild(newSaveTd);

	var parent = document.getElementById("outParent");
	parent.appendChild(newRow);

	counter.value++;

	newSearchInput.focus();
} // end makeClone

function fixFocus() {
	counter = document.getElementById("counter").value;
	oneLess = counter-1;
	document.getElementById("search" + oneLess).focus();
}

function personSearch(where) {
	// Basic stuff 
	counter = document.getElementById("counter").value;
	boxNum = where.id.replace("search", "").replace("status", "");

	// Validate
	searchM = document.getElementById("search" + boxNum);
	statusM = document.getElementById("status" + boxNum);
	icon = document.getElementById("statusIcon" + boxNum);
	if ( searchM.value.length > 0 ) {
		// Do magic
		icon.innerHTML = "&nbsp;";
		doAjax(boxNum);
	} else {
		icon.innerHTML = '⚠️';
	} // end validate

	// Add more rows
	oneLess = counter-1;
	searchN = document.getElementById("search" + oneLess);
	statusN = document.getElementById("status" + oneLess);
	if ( icon.innerHTML != '⚠️') {
		if ( ( statusN.value.length > 0 ) && ( searchN.value.length > 0 )) {
			goBtn = document.getElementById("infoIcon" + boxNum);
			goBtn.style.display = "inherit";
			makeClone();
		} // end if
	} // enf if
} // end pretendDoStuff

function doAjax(rowNum) {
	// get_dn_by_x.php?x=5&q=F1000
	window.rowNum = rowNum;
	typeid = document.getElementById("defaulttype").value;
	searchN = document.getElementById("search" + rowNum);
	resultN = document.getElementById("displayName" + rowNum);
	pidN = document.getElementById("personID" + rowNum);
	icon = document.getElementById("statusIcon" + boxNum);
	var rowNum;
	req=new XMLHttpRequest();
	url = 'agents/get_dn_by_x.php?q=' + searchN.value + '&x=' + typeid;
	req.open("GET", url, false);
	req.send();
	rslt = JSON.parse( req.responseText );
	if ( 1 == rslt.result ) {
		resultN.value = rslt.displayname;
		pidN.value = rslt.personid;
		icon.innerHTML = "&nbsp;";
	} else {
		goBtn = document.getElementById("infoIcon" + boxNum);
		goBtn.style.display = "none";
		icon.innerHTML = '⚠️';
	} // end if
} // end doAjax


function postStatus(where) {
	boxNum = where.id.replace("saveButton", "");
	
	idN = document.getElementById("personID" + boxNum);
	searchN = document.getElementById("search" + boxNum);
	statusN = document.getElementById("status" + boxNum);
	url = 'agents/set_status.php?personid=' + idN.value + '&status=' + statusN.value;
	req=new XMLHttpRequest();
	req.open("GET", url, false);
	req.send();
	rslt = JSON.parse( req.responseText );
	if ( 1 == rslt.result ) {
		icon = document.getElementById("statusIcon" + boxNum);
		icon.innerHTML = '';
		searchN.disabled = true;
		statusN.disabled = true;
	} else {
		icon = document.getElementById("statusIcon" + boxNum);
		icon.innerHTML = '⚠️';
	} // end if
} // end postStatus	
