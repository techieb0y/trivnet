<?php 

$raceid=279;

require_once("include/head.inc");
require_once("include/config.inc");
require_once("include/db_ops.inc");

echo "<script language=\"JavaScript\">\n";
echo "	function doChange(id) {\n";
echo "		document.getElementById(id).style.backgroundColor = '#32CD32';\n";
echo "	}\n";
echo "	function doUnChange() {\n";
echo "		var boxes = document.forms['personinfo'].getElementsByTagName(\"input\");\n";
echo "		Array.forEach(boxes, function(box) {\n";
echo "			box.style.backgroundColor = '';\n";
echo "		});\n";
echo "	}\n";
echo "	function doUpdate() {\n";
echo "		var boxes = document.forms['personinfo'].getElementsByTagName(\"input\");\n";
echo "		var update = '';\n";
echo "		Array.forEach(boxes, function(box) {\n";
echo "			if ( '' != box.style.backgroundColor) {;\n";
echo "				update += box.id + ':' + box.value + ',';\n";
echo "			}\n";
echo "		});\n";
echo "		document.getElementById(\"updateString\").value = update;\n";
echo "		document.forms['updateinfo'].submit();\n";
echo "	}\n";

echo "</script>\n";

$id=0;

if ( isset($_GET["id"]) ) {
	$id = $_GET["id"];
} // end if


// ------- Re-dispplay the person's info, for confirmation that we're looking at
// the right person

$theBigArray = array();

// Header
$qh = "SELECT typeid,name,label FROM datatypes ORDER BY typeid";
$rh = query($qh);
foreach ( $rh as $row ) {
	$typeid = $row["typeid"];
	$name = $row["name"];
	$label = $row["label"];
	$theBigArray[$typeid] = $name;
} // end foreach

$theQuery = "SELECT * FROM crosstab('SELECT * from persondata WHERE personid=$id', 'SELECT typeid FROM datatypes ORDER BY typeid') as ( personid int";
foreach ( $theBigArray as $fieldName ) {
	$theQuery .= ", " . $fieldName . " varchar ";
} // end foreach
$theQuery .= ")";

// Get results
$r = query( $theQuery );

echo "<form name=\"updateinfo\" id=\"updateinfo\" action=\"personedit.php\" method=GET>\n";
echo "<input id=\"personID\" name=\"personID\" value=\"$id\" type=hidden>\n";
echo "<input id=\"updateString\" name=\"updateString\" type=hidden>\n";
echo "</form>\n";

$q_cur = "SELECT status FROM people WHERE id=$id";
$r_curr = query($q_cur);
$curr_stat = $r_curr[0]["status"];

echo "<form method=\"POST\" action=\"setStatusQuick.php?personID=$id\">\n";

echo "Person's status is: <select name=\"setStatus\">\n";

$q_st = "SELECT id, status from statustypes ORDER BY status";
$r_st = query($q_st);
foreach( $r_st as $rr ) {
	$status = $rr["status"];
	$code = $rr["id"];
	if ( $code == $curr_stat ) {
		echo "<option selected value=\"$code\">$status\n";
	} else {
		echo "<option value=\"$code\">$status\n";
	} // end if
} // end foreach
echo "</select>\n";

echo "<input type=\"submit\" value=\"Save\">";

echo "</form>";

echo "<form name=\"personinfo\" id=\"personinfo\" action=\"javascript:;\" method=GET>\n";

echo "<table>\n";
echo "<tr>";
echo "<th>PersonID</th>";
foreach ( $theBigArray as $fieldName ) {
	if ( $fieldName != "status" ) {
		echo "<th>$fieldName</th>";
	} // end if
} // end foreach
echo "</tr>\n";

$l = 0;
foreach ( $r as $key => $row ) {
	// This should only loop once, as we only query one personID.
	echo "<tr>";
	foreach ( $r[0] as $k => $f ) {
		// This is for each datatype column
		$val = $row[$k];

		if ( $k != "status" ) {
			echo "<td>\n";
			// Don't display edit box for PersonID
			if ( $id == $val ) {
				// is there a better way to check for this?
				echo "\t$row[$k]\n";
			} else {
					$mylen = 2 + strlen($val);
					$l += $mylen;
					echo "\t<input type=text name=\"$k\" id=\"$k\" size=$mylen value=\"" . $val . "\" onChange=\"doChange(this.id)\">\n";
			} // end if
			// Marthon-specific: save the bib number for the results link
			if ( $k == "bibnum" ) { $bibNum = $val; }
			echo "</td>\n";
		} // end if
	} // end foreach
	echo "<td><input type=button value=\"Save\" onClick=\"doUpdate()\"></td>";
	echo "<td><input type=reset  value=\"Reset\" onClick=\"doUnChange()\"></td>";
	echo "</tr>\n";
} // end foreach


echo "</table>\n";

// echo "<br><a target=\"_new\" href=\"http://www.mtecresults.com/runner/show?rid=$bibNum&race=$raceid\">MTEC Results for bib $bibNum</a>\n";

echo "</form>";

$q = "SELECT * from updatesequence WHERE personid=$id AND datatype=0 ORDER BY timestamp desc";
$r = query($q);

echo "<table width=\"100%\">\n";

echo "<tr><th>Timestamp</th><th>Message Source</th><th>Message</th></tr>\n";

if ( count( $r ) > 0 ) {
	foreach ( $r as $row) {
		$who = $row["source"];
		$when = date( "r", $row["timestamp"] );
		$mesg = $row["value"];
		echo "<tr><td>$when</td><td>$who</td><td>$mesg</td></tr>\n";
	} // end foreach
} // end if

echo "</table>\n";
echo "<b>" . count($r) . " updates</b><br>\n";

// Canned message selection and free-form entry goes here
echo "<form method=\"POST\" action=\"update.php?id=$id\">\n";
$q = "SELECT text FROM quickmesg ORDER BY text ASC";
$r = query($q);
echo "<select name=\"quickmesg\">\n";
echo "<option value=\"null\">Choose one:</option>\n";
foreach($r as $row) {
	echo "<option value=\"" . $row["text"] . "\">" . $row["text"] . "</option>\n";
} // end foreach
echo "</select>\n";

echo " or custom message: <input name=\"customtext\" length=255 size=128><i>(overrides preset text)</i><br>";
echo "<input type=\"submit\"></form>";

echo "</body></html>";
?>
