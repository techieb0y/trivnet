<?php
require_once("include/aprs-symbols.php");
$sym = urldecode($_GET["sym"]);
header("Content-type: image/png");
$when = date("D, j M Y G:i:s T", time() + 604800);
header("Expires: " . $when);

$whc = getCoords($sym);
$left = $whc[0];
$top = $whc[1];

$dstimage=imagecreate(16,16);
$srcimage=imagecreatefrompng("images/allicons.png");
imagecopy($dstimage,$srcimage, 0,0, $left, $top, 16,16);
imagepng($dstimage);

?>
