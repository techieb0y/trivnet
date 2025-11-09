<?php 

require_once("include/head.inc");
require_once("include/config.inc");
require_once("include/db_ops.inc");

$id=0;

if ( isset($_GET["id"]) ) {
	$id = $_GET["id"];
} // end if

echo "<script language=\"JavaScript\">\n";
echo "	function doChange(id) {\n";
echo "		document.getElementById(id).style.backgroundColor = '#32CD32';\n";
echo "	}\n";
echo "	function doUnChange() {\n";
echo "		var boxes = document.forms['personinfo'].getElementsByTagName(\"input\");\n";
echo "		Array.from(boxes).forEach(function(box) {\n";
echo "			box.style.backgroundColor = '';\n";
echo "		});\n";
echo "	}\n";
echo "	function doUpdate() {\n";
echo "	  var boxes = document.forms['personinfo'].getElementsByTagName(\"input\");\n";
echo "	  var sboxes = document.forms['personinfo'].getElementsByTagName(\"select\");\n";
echo "	  var update = '';\n";
echo "	  Array.from(boxes).forEach(function(box) {\n";
echo "	    if ( '' != box.style.backgroundColor) {;\n";
echo "		  update += box.id + ':' + box.value + ',';\n";
echo "		  }\n";
echo "	  });\n";
echo "	  Array.from(sboxes).forEach(function(box) {\n";
echo "	    if ( '' != box.style.backgroundColor) {;\n";
echo "		  update += box.id + ':' + box.value + ',';\n";
echo "		}\n";
echo "	  });\n";
echo "    console.log(update);\n";
echo "	  document.getElementById(\"updateString\").value = update;\n";
echo "	  var xhr = new XMLHttpRequest();\n";
echo "	  xhr.onload = function(){ console.log(xhr.responseText); }\n";
echo "	  xhr.open( document.forms['updateinfo'].method, 'personedit.php' );\n";
echo "    console.log(xhr);\n";
echo "	  xhr.send(new FormData( document.forms['updateinfo']) );\n";
echo "	}\n";
echo "	function doUpdateBoth() {\n";
echo "    doUpdate();\n";
echo "	  var xhr2 = new XMLHttpRequest();\n";
echo "	  xhr2.onload = function(){ console.log(xhr.responseText); }\n";
echo "	  xhr2.open( document.forms['quickmesg'].method, 'update.php?id=$id' );\n";
echo "    console.log(xhr2);\n";
echo "	  xhr2.send(new FormData( document.forms['quickmesg']) );\n";
echo "	}\n";
echo "</script>\n";

// Record an 'audit log' event
global $config;
$who = $_SESSION["tactical"];
$cs = $_SESSION["callsign"];
$now = time();
$dt = $config["message"];
$q = "INSERT INTO updatesequence VALUES ( $id, $now, '$who', $dt, '<span class=\"audit\">Search performed by $cs</span>' )";
$r = query($q);


// ------- Re-display the person's info, for confirmation that we're looking at
// the right person

// Quick sanity check: does this personId exist in the people table?
$sc = query("SELECT id from PEOPLE where id=$id");
$snck = $sc[0]["id"];
if ( $snck != $id ) { echo "<em>The specified person ID does not exist.</em><br>\n"; }

// Pre-load the enumerated data value sets for display
$q_enum = "SELECT * FROM enumtypes";
$r_enum = query($q_enum);

foreach( $r_enum as $k => $v ) {
	$dt = $v["datatype"];
	$val = $v["value"];
	$eid = $v["id"];
	$enums[$dt][$eid] = $val;
} // end foreach

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

// echo "<pre> $theQuery </pre>\n";

// Get results
$r = query( $theQuery );

echo "<form name=\"updateinfo\" id=\"updateinfo\" action=\"personedit.php\" method=GET>\n";
echo "<input id=\"personID\" name=\"personID\" value=\"$id\" type=hidden>\n";
echo "<input id=\"updateString\" name=\"updateString\" type=hidden>\n";
echo "</form>\n";

echo "<form name=\"personinfo\" id=\"personinfo\" action=\"javascript:;\" method=GET>\n";

// Fetch the enum-ness of all the datatypes
$r_dts = query("SELECT typeid, name, enum FROM datatypes");
foreach($r_dts as $dt) {
	$_name = $dt["name"];
	$_id = $dt["typeid"];
	$_en = $dt["enum"];
	$dts[$_name]["id"] = $_id;
	$dts[$_name]["enum"] = $_en;
} // end foreach

echo "<table class=\"persondata\">\n";
echo "<tr>";
echo "<th>PersonID</th>";
foreach ( $theBigArray as $fieldName ) {
	if ( $fieldName != "message" ) {
		echo "<th>$fieldName</th>";
	} // end if
} // end foreach
echo "</tr>\n";

// Do we know we exist in the DB (from sanity check above) but this monster query returned no rows?
// This happens why you've just used the 'manually create person' link -- all that does is
// add to the people table, but not to persondata at all.
if ( ( $id == $snck ) && ( count($r) == 0 ) ) {
        $bogoArray = array();
        foreach ($r_dts as $dt) {
                $dtn = $dt["name"];
                $bogoArray[$dtn] = '';
        }
        // There isn't a way to insert into the begining of an array, so swap/push/swap to get the same effect.	
        $r[0] = array_reverse($bogoArray);
        $r[0]["personid"] = $id;
        $r[0] = array_reverse($r[0]);
}


$l = 0;
foreach ( $r as $key => $row ) {
	// This should only loop once, as we only query one personID.
	echo "<tr>";
	foreach ( $r[0] as $k => $f ) {
		// This is for each datatype column
		$val = $row[$k];

			if ( $k != "message" ) {
			echo "<td>\n";
			if ( $k == "personid" ) {
				// Don't display edit box for PersonID
				echo "\t$row[$k]\n";
			} else if ( isset($dts[$k]["enum"]) && ( "t" == $dts[$k]["enum"] ) ) {
				// This is an enumerated data type; reference the table and show it as a dropdown
				echo "<select name=\"$k\" id=\"$k\" onChange=\"doChange(this.id)\" >\n";
				$_id = $dts[$k]["id"];
				if ( !isset($val) ) { echo "<option selected disabled>--</option>\n"; }
				foreach ( $enums[$_id] as $en_k => $en_v ) {
					if ( $en_k == $val ) {
						echo "<option selected value=\"$en_k\">$en_v</option>\n";
					} else {
						echo "<option value=\"$en_k\">$en_v</option>\n";
					} // end if
				} // end foreach
				echo "</select>\n";
			} else {
					// Plain entry field
					$mylen = 2 + strlen($val);
					$l += $mylen;
					echo "\t<input type=text name=\"$k\" id=\"$k\" size=$mylen value=\"" . $val . "\" onChange=\"doChange(this.id)\">\n";
			} // end if
			// Marthon-specific: save the bib number for the results link
			if ( $k == "bibnum" ) { $bibNum = $val; }
			if ( $k == "race" ) { $rid = $val; }
			echo "</td>\n";
			} // end if
	} // end foreach
	// echo "<td><input type=button value=\"Save\" onClick=\"doUpdate()\"></td>";
	echo "<td><input type=reset  value=\"Reset\" onClick=\"doUnChange()\"></td>";
	echo "</tr>\n";
} // end foreach

echo "</table>\n";

echo "</form>\n";

echo "<table class=\"persondata\">\n";

echo "<tr><td>\n";

	// 11 = Red Cross
	// 27 = Tent
	// 65 = Ambulance
	// 72 = Hospital
	// 31 = SysOp

	$mt = array( "11", "27", "65", "72", "31" );
	echo "<table><tr>\n";
	echo "<th colspan=3>Med-tent specific options</th>\n";
	echo "</tr><tr>\n";
	if ( in_array( $_SESSION["symbol"], $mt ) ) {
		echo "<td><a href=\"mtquick.php?personid=$id&direction=" . $config["medtentstatus"] . "\">➡️🏥<br>Entered Med Tent</a></td>\n";
		echo "<td>&nbsp;</td>";
		echo "<td><a href=\"mtquick.php?personid=$id&direction=" . $config["lefttentstatus"] . "\">⬅️🏥<br>Left Med Tent</a></td>\n";
	} else {
		echo "<td colspan=3><i>n/a</i></td>\n";		
	}
	echo "</tr></table>\n";

echo "</td><td>\n";
echo "<table><tr><th>\n";
echo "Results\n";
echo "</th></tr>\n<tr><td>\n";

if ( isset( $RACEID[$rid]) ) { 
	$raceid = $RACEID[ $rid ];
	echo "<a target=\"_new\" href=\"" . sprintf( $config["timinglink"], $rid, $bibNum ) . "\">for bib $bibNum</a>\n";
}
echo "</td></tr></table>\n";

echo "</td><td>\n";

echo "<table>\n";
echo "<tr><th>\n";
echo "Latching statuses\n";
echo "</th></tr>\n<tr><td>\n";
$q_latch = 'select label from latchlog,latchtypes where latchtypes.id=latchlog.latchid and latchlog.personid = $1;';
$p_latch[0] = $id;

$res = pg_query_params( connect(), $q_latch, $p_latch );
$r = array();
while ( $z = pg_fetch_assoc($res) ) {
	echo $z["label"] . "<br>\n";
} // end while

echo "</td></tr></table>\n";

echo "</td>\n";
echo "</tr></table>\n";

echo "<br>\n";

// Canned message selection and free-form entry goes here
echo "<form method=\"POST\" name=\"quickmesg\" id=\"quickmesg\" action=\"\" onsubmit=\"return doUpdateBoth();\">\n";
$q = "SELECT text FROM quickmesg ORDER BY text ASC";
$r = query($q);
echo "<select name=\"quickmesg\">\n";
echo "<option value=\"null\">Choose one:</option>\n";
foreach($r as $row) {
	echo "<option value=\"" . $row["text"] . "\">" . $row["text"] . "</option>\n";
} // end foreach
echo "</select>\n";

echo " or custom message: <input name=\"customtext\" length=255 size=128><i>(overrides preset text)</i><br>\n";
echo "<td><input type=button value=\"Save\" onClick=\"doUpdateBoth()\"></td>";
// echo "<input type=\"submit\" value=\"Save\"></form>\n";
echo "</form>";

echo "<hr>\n";

$mdt = $config["message"];
$q = "SELECT * from updatesequence WHERE personid=$id AND datatype=$mdt ORDER BY timestamp desc";
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

require_once("include/foot.inc");
?>
