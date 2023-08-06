<?php 

// This is the admin page

require_once("include/db_ops.inc");
require_once("include/config.inc");

if ( isset($_GET["mode"]) ) {
	$mode = $_GET["mode"];
	require_once("include/admin_process.inc");
} else {
    // No mode, show the form
    require_once("include/head.inc");

    echo "<link rel=\"stylesheet\" href=\"/css/jquery-ui.css\">\n";
    echo "<script src=\"/js/jquery.js\"></script>\n";
    echo "<script src=\"/js/jquery-ui.js\"></script>\n";

    echo "
        <script>
        $(function() {
                $( \"#tabs\" ).tabs();
                });
    </script>
        ";

    echo "<div id=\"tabs\">\n";
    echo "<ul>\n";
    echo "<li><a href=\"#tabs1\">Data Types</a></li>\n";
    echo "<li><a href=\"#tabs5\">Quick Status Messages</a></li>\n";
    echo "<li><a href=\"#tabs2\">Bulk Import</a></li>\n";
    echo "<li><a href=\"#tabs3\">Async Jobs</a></li>\n";
    echo "<li><a href=\"#tabs4\">Race Head/Tail</a></li>\n";
    echo "<li><a href=\"#tabs9\">Debug Info</a></li>\n";
    echo "</ul>\n";

	require_once("include/admin_datatype.inc");
	require_once("include/admin_quickmesg.inc");
	require_once("include/admin_headtail.inc");
	require_once("include/admin_bulkimport.inc");
	require_once("include/admin_async.inc");
	require_once("include/admin_debug.inc");
    require_once("include/foot.inc");

    echo "</body></html>";
} // end if

?>
