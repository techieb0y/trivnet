<?php

header("Content-Type: image/png");

ini_set( "include_path", "./include/" );

require_once("config.inc");
require_once("constants.inc");
require_once("db_ops.inc");

$races = query("select * from race");
// echo "<pre>";
// print_r($races);
// echo "</pre>";

$increment = 32;

$height = $increment * count($races);
$width = 512;

$img = imagecreatetruecolor( $width, $height );

$font = "/usr/share/fonts/dejavu/DejaVuSansMono.ttf";
$black = imagecolorexact($img, 0,     0,   0);
$white = imagecolorexact($img, 255, 255, 255);

$grey =  imagecolorexact($img, 100, 100, 100);
$blue =  imagecolorexact($img, 0,     0, 100);

// White background
imagefilledrectangle($img, 0, 0, $width, $height, $white);

foreach($races as $k => $r) {
        $rn = $r["raceid"];
        $left  =       $r["tail"] / $RACELENGTH[$rn];
        $right = 1 - ( $r["head"] / $RACELENGTH[$rn] );

	syslog(LOG_DEBUG, "Race $rn: left is " . floor($width*$left) . ", right is " . floor($width*$right) );

	$y = (1+$k) * $increment;
	$y2 = floor(0.90 * $y );

        if ( floor($width*$left) > 48 ) {
                imagefilledrectangle($img, 48,                      $y, floor($width*$left), $y+$increment, $grey);
                syslog(LOG_DEBUG, "1: 48, $y, floor($width*$left), $y+$increment");
        }

	imagefilledrectangle($img, 48+floor($width*$right), $y, $width,              $y+$increment, $blue);
	imageline( $img, 0, $y, $width, $y, $black );
	
	$text = $rn;
	            //      sz, ang, x,   y, color, font, string
	imagefttext ( $img, 12, 0,   8, $y2, $black, $font, $text );
}

// Vertical left seperator
imageline( $img, 24, 0, 24, $height, $black );

imagepng( $img );
?>
