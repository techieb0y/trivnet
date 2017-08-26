function makeClone() {
	counter = document.getElementById("counter").value;
	source = document.getElementById("template");
	var parent = document.getElementById("outParent");
	var clone = document.getElementById("template").cloneNode(true);
	counter++;
	parseDOMat( clone );
	document.getElementById("counter").value = counter;
	clone.style.display = "inherit";
	parent.appendChild(clone);
	oneLess = counter-1;
	document.getElementById("search" + oneLess).focus();
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
		icon.setAttribute('src', 'images/throbber.gif');
		doAjax(boxNum);
	} else {
		icon.setAttribute('src', 'images/warning.png');
	} // end validate

	// Add more rows
	oneLess = counter-1;
	searchN = document.getElementById("search" + oneLess);
	statusN = document.getElementById("status" + oneLess);
	if ( icon.getAttribute('src') != 'images/warning.png') {
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
		icon.setAttribute('src', 'images/blank.png');
	} else {
		goBtn = document.getElementById("infoIcon" + boxNum);
		goBtn.style.display = "none";
		icon.setAttribute('src', 'images/warning.png');
	} // end if
} // end doAjax


function postStatus(where) {
	boxNum = where.id.replace("infoIcon", "");
	
	idN = document.getElementById("personID" + boxNum);
	searchN = document.getElementById("search" + boxNum);
	statusN = document.getElementById("status" + boxNum);
	url = 'agents/set_status.php?personid=' + idN.value + '&status=' + statusN.value;
	req=new XMLHttpRequest();
	req.open("GET", url, false);
	req.send();
	rslt = JSON.parse( req.responseText );
	if ( 1 == rslt.result ) {
		icon = document.getElementById("infoIcon" + boxNum);
		icon.setAttribute('src', 'images/blank.png');
		searchN.disabled = true;
		statusN.disabled = true;
	} else {
		icon = document.getElementById("infoIcon" + boxNum);
		icon.setAttribute('src', 'images/warning.png');
	} // end if
} // end postStatus	
