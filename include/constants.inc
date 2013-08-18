<?php

$prompt = ">";

$help_text["help"][] = "Get help about available commands";

$help_text["search"][] = "Allows you to search for data";
$help_text["search"][] = "Usage: search <field> <string>";

//$help_text["add"][] = "Adds data to the system";
//$help_text["add"][] = "Usage: add <column1> <column2> ... <columnN>";

$help_text["enable"][] = "Become sysop";
$help_text["disable"][] = "Drop sysop priviliges";

$help_text["quit"][] = "Exit from the Trivnet system";
$help_text["exit"] = $help_text["quit"];
$help_text["logout"] = $help_text["quit"];

$help_text["tell"][] = "Send message";
$help_text["tell"][] = "Usage: tell ( all | <callsign> ) Message text ...";

$help_text["listfields"][] = "Show the available data types";

$help_text["addfield"][] = "Add a data field.";
$help_text["addfield"][] = "Usage: addfield <shortname> <full label>";

$help_text["delfield"][] = "Removes a data field.";
$help_text["delfield"][] = "Usage: delfield <shortname>";

$help_text["mycall"][] = "Set your station's callsign or tactical call";

$help_text["date"][] = "Show the server's current date and time";
$help_text["who"][] = "Show all users currently on the system";

$help_text["info"][] = "View details about a person";
$help_text["info"][] = "Usage: info <record id>";
$help_text["details"] = $help_text["info"];
$help_text["show"] = $help_text["info"];

$help_text["update"][] = "Add updates for a person";
$help_text["update"][] = "Usage: update <record id> <message text>";

$help_text["batch"][] = "Batch update mode";
$help_text["batch"][] = "batch [search type] <update type>";

$jobstate[0] = "Completed";
$jobstate[1] = "Pending";
$jobstate[2] = "Aborted";
$jobstate[3] = "Running";
$jobstate[4] = "On Hold";
$jobstate[5] = "Ran With Errors";


?>
