<?php 

// This is the basic query page, where you search for people, to get more information or to update their info.
// To make the process simple, we show all the fields and let you search by entering criteria into any of them.

require_once("include/head.inc");
require_once("include/config.inc");
require_once("include/db_ops.inc");

function showFields() {
	// Display the search field stuff
	$db = connect();
	$q = "SELECT * FROM datatypes WHERE typeid > 0 ORDER BY typeid";
	$r = query($q);
	echo "<form action=\"query.php\" method=POST>\n";
	echo "<table>\n";
	if ( count($r) > 0 ) {
		foreach ( $r as $row ) {
			$label = $row["label"];
			$name = $row["typeid"];
			echo "<tr><td>$label:</td><td><input name=\"$name\"></td></tr>\n";
		} // end foreach
	} // end if
	echo "<tr><td><input checked type=\"radio\" name=\"type\" value=\"AND\"> Match all (AND search)</td>";
	echo "<td><input type=\"radio\" name=\"type\" value=\"OR\"> Match any (OR search)</td></tr>\n";
	echo "<tr><td>or by status message:</td><td><input name=\"0\"></td></tr>\n";
	echo "<tr><td><input type=\"reset\"></td><td><input type=\"submit\"></td></tr>\n";
	echo "</table></form>\n";
} // end showFields


showFields();

echo "<hr>\n";

if ( isset($_SESSION["criteria"] ) || ( isset($_POST) && ( count($_POST) > 1 ) ) ) {
	if ( isset( $_GET["offset"] ) ) { $offset = $_GET["offset"]; } else { $offset = 0; }

	// echo count($_POST);

	if ( strlen($_POST[0]) > 0 ) { 
		$q_base = "SELECT DISTINCT personid FROM updatesequence WHERE ";
	} else {
		$q_base = "SELECT DISTINCT personid FROM persondata WHERE ";
	} // end special handling for status messages

	$q2 = "SELECT * from persondata WHERE personid IN (";
	$prefix = "";

	$q_ex = "SELECT typeid,exact FROM datatypes";
	$r_ex = query($q_ex);

	$exactness = array();
	foreach ( $r_ex as $rk => $rv ) {
		$typeid = $rv["typeid"];
		$exactness[$typeid] = $rv["exact"];
	} // end foreach

	// echo "<pre>"; print_r($exactness); echo "</pre>";
	// echo "<pre>"; print_r($_POST); echo "</pre>";

	echo "<table width=\"100%\">\n";

	$q = $q_base;

	if ( strlen($_POST[0]) > 0 ) {
		$q = $q_base . "(datatype=0 AND value ILIKE ''%" . $_POST[0] . "%'')";
	} else {
	// echo "<pre>" . print_r($_POST) . "</pre>";
	foreach ( $_POST as $key => $param ) {

		if ( ($key != "type" ) && isset($param) && ( strlen($param) > 0 ) ) {

			if ( "t" == $exactness[$key] ) {
				$q .= $prefix . "( datatype=''$key'' AND value = ''$param'' ) ";
			} else {				
				$q .= $prefix . "( datatype=''$key'' AND value ILIKE ''%$param%'' ) ";
			} // end if

			if ( "AND" == $_POST["type"] ) {
				$prefix = "INTERSECT " . $q_base;
			} else {
				$prefix = "OR ";
			} // end if
		} // end if
	} // end foreach

	} // end special-case handling for status messages

	$qq = $q2 . $q . ")";

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

	$theQuery = "SELECT * FROM crosstab('" . $qq . " ORDER BY personid', 'SELECT typeid FROM datatypes ORDER BY typeid') as ( personid int";
	foreach ( $theBigArray as $fieldName ) {
		$theQuery .= ", " . $fieldName . " varchar ";
	} // end foreach
	$theQuery .= ")";

	// Get results
	// echo "<pre>" . $theQuery . "</pre>";

	$r = query( $theQuery );
	
	// echo "<pre>" . print_r($r) . "</pre>";

	echo "<table>\n";
	echo "<tr>";
	foreach ( $theBigArray as $fieldName ) {
		if ( $fieldName != "status" ) {
			echo "<th>$fieldName</th>";
		} // end if
	} // end foreach
	echo "</tr>\n";

	foreach ( $r as $key => $row ) {
		$pers = $row["personid"];
		echo "<tr>";
		foreach ( $r[0] as $k => $f ) {
			if ( ($k != "personid") && ($k != "status") ) {
				echo "<td><a href=\"detail.php?id=$pers\">";
				echo $row[$k];
				echo "</a></td>";
			} // end if
		} // end foreach
		echo "</tr>\n";
	} // end foreach

} // end if

echo "</table>\n";
if ( isset($r) ) { $numRows = count($r); } else { $numRows = 0; }
echo "<b>" . $numRows . " rows</b><br>\n";

echo "...or <a href=\"addPerson.php\">Add New Person Manually</a><br>";

echo "<hr>\n";
showFields();

echo "</body></html>";
?>
