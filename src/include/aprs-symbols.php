<?php

$symbols["--"]="0";
$symbols["/!"]="1";
$symbols["/\""]="2";
$symbols["/#"]="3";
$symbols["/$"]="4";
$symbols["/%"]="5";
$symbols["/&"]="6";
$symbols["/'"]="7";
$symbols["/("]="8";
$symbols["/)"]="9";
$symbols["/*"]="10";
$symbols["/+"]="11";
$symbols["/,"]="12";
$symbols["/-"]="13";
$symbols["/."]="14";
$symbols["//"]="15";
$symbols["/0"]="16";
$symbols["/1"]="17";
$symbols["/2"]="18";
$symbols["/3"]="19";
$symbols["/4"]="20";
$symbols["/5"]="21";
$symbols["/6"]="22";
$symbols["/7"]="23";
$symbols["/8"]="24";
$symbols["/9"]="25";
$symbols["/:"]="26";
$symbols["/;"]="27";
$symbols["/<"]="28";
$symbols["/="]="29";
$symbols["/>"]="30";
$symbols["/?"]="31";
$symbols["/@"]="32";
$symbols["/A"]="33";
$symbols["/B"]="34";
$symbols["/C"]="35";
$symbols["/D"]="36";
$symbols["/E"]="37";
$symbols["/F"]="38";
$symbols["/G"]="39";
$symbols["/H"]="40";
$symbols["/I"]="41";
$symbols["/J"]="42";
$symbols["/K"]="43";
$symbols["/L"]="44";
$symbols["/M"]="45";
$symbols["/N"]="46";
$symbols["/O"]="47";
$symbols["/P"]="48";
$symbols["/Q"]="49";
$symbols["/R"]="50";
$symbols["/S"]="51";
$symbols["/T"]="52";
$symbols["/U"]="53";
$symbols["/V"]="54";
$symbols["/W"]="55";
$symbols["/X"]="56";
$symbols["/Y"]="57";
$symbols["/Z"]="58";
$symbols["/["]="59";
$symbols["/\\"]="60";
$symbols["/]"]="61";
$symbols["/^"]="62";
$symbols["/_"]="63";
$symbols["/`"]="64";
$symbols["/a"]="65";
$symbols["/b"]="66";
$symbols["/c"]="67";
$symbols["/d"]="68";
$symbols["/e"]="69";
$symbols["/f"]="70";
$symbols["/g"]="71";
$symbols["/h"]="72";
$symbols["/i"]="73";
$symbols["/j"]="74";
$symbols["/k"]="75";
$symbols["/l"]="76";
$symbols["/m"]="77";
$symbols["/n"]="78";
$symbols["/o"]="79";
$symbols["/p"]="80";
$symbols["/q"]="81";
$symbols["/r"]="82";
$symbols["/s"]="83";
$symbols["/t"]="84";
$symbols["/u"]="85";
$symbols["/v"]="86";
$symbols["/w"]="87";
$symbols["/x"]="88";
$symbols["/y"]="89";
$symbols["/z"]="90";
$symbols["/{"]="91";
$symbols["/|"]="92";
$symbols["/}"]="93";
$symbols["/~"]="94";
$symbols["/|"]="95";

function getPos($sym) {
	global $symbols;
	$loc = $symbols[$sym];
	$col = floor($loc / 16);
	$row = $loc % 16;
	return array( $row, $col );
//	echo "(" . 16 * $col . "," . 16 * $row . ")";
} // end getPos

function getCoords($sym) {
	global $symbols;
	$loc = 0;
	if ( "/" == substr($sym, 0, 1) ) {
		// We got the APRS-encoded form ("/q")
		$loc = $symbols[$sym];
	} else if ( preg_match("/^[0-9]+$/",$sym) ) {
		// We got the image number, counting down then right from 0;
		$loc = $sym;
	} // end if

	$col = floor($loc / 16);
	$row = $loc % 16;

	$left = 16 * $col;
	$top = 16 * $row;
	return array( $left, $top );
} // end getCoords

function genMap() {
	echo "<map name=\"symbolpicker\">\n";
	global $symbols;
	foreach( $symbols as $ky => $sym ) {
		$loc = getPos($ky);
		// print_r($loc);
		$left = 16 * $loc[1];
		$top = 16 * $loc[0];
		$right = ( 16 * (1 + $loc[0] ) ) - 1;
		$bot = ( 16 * (1 + $loc[1] ) ) - 1;
		$coords = $left . "," . $top . "," . $right . "," . $bot; 
		// echo "<area shape=poly coords=" . $coords . " href=\"" . urlencode($ky) . "\">\n";
		echo "<area shape=poly coords=" . $coords . " onClick=\"setSymbol($sym)\">\n";
	} // end foreach
	echo "</map>\n";
} // end genMap

function genStyle() {
	global $symbols;
	foreach( $symbols as $ky => $sym ) {
		$loc = getPos($ky);
		// print_r($loc);
		$left = -16 * $loc[1];
		$top = -16 * $loc[0];
		$coords = $left . "," . $top . "," . $right . "," . $bot; 
		echo ".symbol" . $sym . " { background: url(\"images/allicons.png\") $top $left;}\n";
	} // end foreach
} // end genMap

function genTablePicker() {
	global $symbols;

	echo "<div id=\"symbolpicker\" class=\"symbolpicker\"><table>";
	echo "<!-- symbols has " . count($symbols) . " items -->\n";
	for ( $i=0; $i <= count($symbols); $i++ ) {
		if ( 0 == ($i % 6) ) { echo "<tr>\n"; }
		echo "\t<td><a onClick=\"setSymbol($i)\"><img src=\"/symbol/$i\"></a></td>\n";
		if ( 5 == ($i % 6) ) { echo "</tr>\n"; }
	} // end for
	echo "</table></div>\n";
} // end genTablePicker

?>
