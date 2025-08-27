<?php 

ini_set("auto_detect_line_endings", true);

// Test page for handling CSV uploads

require_once("../../include/db_ops.inc");
require_once("../../include/config.inc");

header("Content-Type: text/plain");

if ( isset( $_SERVER["CONTENT_TYPE"] ) ) {
    if ( "text/csv" == $_SERVER["CONTENT_TYPE"] ) {
        echo "OK\n";

        $file = fopen("php://input", "r");
        while ( $row = fgetcsv($file) ) {
            $data[] = $row;
        }

        print_r($data);
    } else {
        http_response_code(415);
        echo "Unexpected conent type\n";
    }
} else {
    http_response_code(400);
    echo "Missig content type\n";
}

?>