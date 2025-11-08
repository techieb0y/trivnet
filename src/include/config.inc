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
$DB_HOST = "database";

// Network-topolgy-aware page display parameters
$config["localnets"] = array( "192.168.54.0/24", "172.16.104.0/24" );
$config["slownets"] = array("10.0.1.0/24");
$config["default"] = "fancy";

// ID numbers for statuses that get special handling within the UI
$config["multidefault"] = 2;
$config["status"] = 0;
$config["message"] = 1;

$config["medtentstatus"] = 3;
$config["lefttentstatus"] = 4;
$config["finishstatus"] = 2;

$config["finisherlatchid"] = 1;
$config["medtendlatchid"] = 2;

// Idle session auto-disconnect timeout (in seconds)
$config["idle_timeout"] = 300;

// Number of async (batch) jobs to run in parallel
$config["async_count"] = 1;

// printf-style format string for generating the Results out-link
// Arg 1 = race ID
// Arg 2 = bib number
$config["timinglink"] = "https://www.mtecresults.com/runner/show?rid=%1$d&race=%2$d";

global $RACEID;
$RACEID[1] = "2569";
$RACENAME[1] = "Marathon";
$RACELENGTH[1] = "26.2";

$RACEID[3] = "2571";
$RACENAME[3] = "10-mile";
$RACELENGTH[3] = "10";

$RACEID[4] = "2570";
$RACENAME[4] = "Wheelchair";
$RACELENGTH[4] = "26.2";

$config["use_graphite"] = false;
?>
