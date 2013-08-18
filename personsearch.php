<?php

require_once("include/config.inc");
require_once("include/db_ops.inc");

$q = $_GET["q"];

header("Content-type: text/xml");
// header("Content-type: text/plain");

echo "<?xml version=\"1.0\"?>\n";
echo "<!DOCTYPE person SYSTEM \"/dtd/person.dtd\">\n";

echo "<person_list>\n";
if ( isset($q) && ( strlen($q) > 0 ) ) {

	$query = "SELECT personid,name,value from persondata,datatypes WHERE persondata.personid IN ( SELECT personid FROM persondata WHERE value ILIKE '%" . $q . "%') AND datatypes.typeid=persondata.datatype";
	$result = query($query);

	foreach ( $result as $r ) { 
		$id = $r["personid"];
		$dt = $r["name"];
		$vl = $r["value"];

		$people[$id]["id"] = $id;
		$people[$id][$dt] = $vl;
	}

	sort($people);

	foreach ( $people as $person ) {
		$resp = "<person personID=\"" . $person["id"] . "\" displayName=\"" . $person["firstname"] . " " . $person["lastname"] . "\"";
		foreach ( $person as $key => $val ) {
			$resp .= " $key=\"$val\"";
		} // end foreach
		$resp .= " />\n";
		echo $resp;
	} // end foreach
	
} // end if
echo "</person_list>\n";
?>
