<?php 

// Test page for handling CSV uploads

require_once("../../include/db_ops.inc");
require_once("../../include/config.inc");

if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
    header("Content-Type: application/json");
    if ( isset($_GET["bibnum"] ) ) {
        // GET with options
        $bib = $_GET["bibnum"];
        $q_id = "SELECT personid FROM persondata WHERE datatype = 2 AND value = $1";
        $p_id[0] = $bib;
        $res = pg_query_params( connect(), $q_id, $p_id );

        $r_id = array();
        while ( $z = pg_fetch_assoc($res) ) {
            $r_id[] = $z;
        } // end while

        // Bail if we can't match the bibnumber to a personid
        if ( ! isset($r_id[0]) ) {
            $output["finished"] = false;
             echo json_encode($output, JSON_PRETTY_PRINT) . "\n";
             exit(0);
        }

        $x = $r_id[0]["personid"];

        $q_count = "SELECT count(personid) AS count FROM latchlog WHERE latchid = 1 AND personid = $x";
        $r_count = query($q_count);

        // No rows in the latchlog means they haven't finished yet
        if ( ! isset($r_count[0]) ) {
            $output["finished"] = false;
             echo json_encode($output, JSON_PRETTY_PRINT) . "\n";
             exit(0);
        }

        $count = $r_count[0]["count"];

        // If there is exactly 1 row for the person/latch type tuple, then they've finished.
        if ( $count == 1 ) {
            $output["finished"] = true;
            echo json_encode($output, JSON_PRETTY_PRINT) . "\n";
             exit(0);
        }
    } else {
        // GET without options
        $q_count = "SELECT count('personid') AS count FROM latchlog WHERE latchid = 1";
        $r_count = query($q_count);
        $count = $r_count[0]["count"];

        $output["count"] = $count;
        echo json_encode($output, JSON_PRETTY_PRINT) . "\n";
    }
} elseif ( ($_SERVER['REQUEST_METHOD'] === 'POST') && isset( $_SERVER["CONTENT_TYPE"] ) ) {
    header("Content-Type: text/plain");
    // Save CSV input into a file
    if ( "text/csv" == $_SERVER["CONTENT_TYPE"] ) {
        $tmp = tmpfile() or die("Unable to open tempfile.");

        $file = fopen("php://input", "r");
        while ( $row = trim(fgets($file)) ) {
            // echo "Got row: {$row}\n";
            fwrite($tmp, $row . "\n");
        }
        fflush($tmp);

        $q_id = "SELECT nextval('async_jobid_seq') AS jobid";
        $r_id = query($q_id);
        $jobid = $r_id[0]["jobid"];

        $jobfile = "/var/www/html/jobs/API-" . $jobid;
        $fh = fopen($jobfile, "w+");
        fseek($tmp, 0);
        stream_copy_to_stream($tmp, $fh);
        fclose($fh);

        // CREATE TABLE async ( jobid serial, filename varchar(64) not null, callsign varchar(8) not null, searchtype int REFERENCES datatypes(typeid), updatetype int, data varchar(255) not null, state int DEFAULT 0, progress int DEFAULT 0, timestamp int NOT NULL);
        // $q_submit = "INSERT INTO async VALUES ( $jobid, '$jobfile', 'API', '" . $config["multidefault"] . "', '" . $config["message"] . "', 'Runner crossed finish line', 1, 0, '" . time() . "');";
	    // $r_submit = query($q_submit);

        // $q_id = "SELECT nextval('async_jobid_seq') AS jobid";
        // $r_id = query($q_id);
        // $jobid = $r_id[0]["jobid"];

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