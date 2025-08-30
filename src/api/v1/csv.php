<?php 

// Test page for handling CSV uploads

require_once("../../include/db_ops.inc");
require_once("../../include/config.inc");

header("Content-Type: text/plain");

if ( isset( $_SERVER["CONTENT_TYPE"] ) ) {
    // Save CSV input into a file
    if ( "text/csv" == $_SERVER["CONTENT_TYPE"] ) {
        $tmp = tmpfile() or die("Unable to open tempfile.");

        $file = fopen("php://input", "r");
        while ( $row = trim(fgets($file)) ) {
            // echo "Got row: {$row}\n";
            fwrite($tmp, $row . "\n");
        }
        fflush($tmp);

        // Set up the async file in the right place
        $q_id = "SELECT nextval('async_jobid_seq') AS jobid";
        $r_id = query($q_id);
        $jobid = $r_id[0]["jobid"];

        $jobfile = "../../jobs/API-" . $jobid;
        $fh = fopen($jobfile, "w+");
        fseek($tmp, 0);
        stream_copy_to_stream($tmp, $fh);
        fclose($fh);

        // CREATE TABLE async ( jobid serial, filename varchar(64) not null, callsign varchar(8) not null, searchtype int REFERENCES datatypes(typeid), updatetype int, data varchar(255) not null, state int DEFAULT 0, progress int DEFAULT 0, timestamp int NOT NULL);
        $q_submit = "INSERT INTO async VALUES ( $jobid, '$jobfile', 'API', '" . $config["multidefault"] . "', '" . $config["message"] . "', 'Runner crossed finish line', 1, 0, '" . time() . "');";
	    $r_submit = query($q_submit);

        $q_submit = "INSERT INTO async VALUES ( $jobid, '$jobfile', 'API', '" . $config["multidefault"] . "', '999', '1', 1, 0, '" . time() . "');";
	    $r_submit = query($q_submit);

    } else {
        http_response_code(415);
        echo "Unexpected conent type\n";
    }
} else {
    http_response_code(400);
    echo "Missig content type\n";
}

?>