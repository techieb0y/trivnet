<?php 

// Test page for handling CSV uploads

require_once("include/db_ops.inc");
require_once("include/config.inc");

header("Content-Type: text/plain");

if ( isset( $_SERVER["CONTENT_TYPE"] ) ) {
    if ( "text/csv" == $_SERVER["CONTENT_TYPE"] ) {
        echo "OK";

        $postbody = stream_get_contents(STDIN);
        $data = fgetcsv($postbody);

        print_r($data);

    } else {
        header("HTTP/415 Unsupported Media Type");
        echo "Unexpected conent type";
    }
} else {
    header("HTTP/400 Bad Request");
    echo "Missig content type";
}

?>