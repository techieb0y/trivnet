<?php

global $DB_TYPE;
global $DB_NAME;
global $DB_HOST;
global $DB_USER;
global $DB_PASS;

// Choices are "mysql" or "pgsql"

//$DB_TYPE = "mysql";
//$DB_NAME = "trivnetdb";
//$DB_USER = "root";
//$DB_HOST = "localhost";

$DB_TYPE = "pgsql";
$DB_NAME = "trivnet";
$DB_USER = "trivnet";
$DB_PASS = "trivnet";
$DB_HOST = "127.0.0.1";

// Network-topolgy-aware page display parameters
$config["localnets"] = array( "10.0.10.0/24", "172.16.206.0/24" );
$config["slownets"] = array("10.0.1.0/24");
$config["default"] = "fancy";

$config["multidefault"] = 1;

// Idle session auto-disconnect timeout (in seconds)
$config["idle_timeout"] = 300;

// Number of async (batch) jobs to run in parallel
$config["async_count"] = 1;

global $RACEID;
$RACEID[1] = "2244"; // Full
$RACEID[2] = "2245"; // Half
$RACEID[3] = "2246"; // Half

?>
