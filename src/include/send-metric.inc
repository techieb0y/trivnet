<?php

function sendMetric( $name, $value ) {
	$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	socket_connect($socket, "localhost", 2023) or die("Could not open socket to carbon.");
	
	$now = time();
	$data = "$name $value $now\n" ;
	$bytes = socket_write($socket, $data);
	socket_close($socket);
	return 0;
}

function sendFullMetric( $name, $value ) {
	$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	socket_connect($socket, "localhost", 2003) or die("Could not open socket to carbon.");
	
	$now = time();
	$data = "$name $value $now\n" ;
	$bytes = socket_write($socket, $data);
	socket_close($socket);
	return 0;
}

?>
