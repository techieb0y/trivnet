<?php 

$raceid=279;

// require_once("include/head.inc");

echo "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
                      \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">

<head>
        <meta charset=\"utf-8\">
        <title>TrivnetDB - Amateur Radio Information Network</title>
        <link rel=\"stylesheet\" href=\"common/trivnet.css\" />

        <script src=\"js/jquery.js\"></script>
        <script src=\"js/handlebars-v1.3.0.js\"></script>
        <script src=\"js/jquery-ui-1.8.24.custom.min.js\"></script>
        <!--script src=\"js/jquery.mobile-1.2.0.min.js\"--><!--/script-->
</head>
";


echo "
<script language=JavaScript>
{
upds = '
	    \"updateSequence\": [
        {
            \"timestamp\": \"1399758867\",
            \"source\": \"KD8GBL\",
            \"datatype\": \"0\",
            \"value\": \"Fjnord\"
        },
        {
            \"timestamp\": \"1399758864\",
            \"source\": \"KD8GBL\",
            \"datatype\": \"0\",
            \"value\": \"Fell off a cliff\"
        },
        {
            \"timestamp\": \"1399758862\",
            \"source\": \"KD8GBL\",
            \"datatype\": \"0\",
            \"value\": \"Dunked in freezing liquid\"
        }
    ];';
}
</script>
";

$id=0;

if ( isset($_GET["id"]) ) {
	$id = $_GET["id"];
} // end if


// ------- Re-dispplay the person's info, for confirmation that we're looking at
// the right person

echo "<table width=\"100%\">\n";

echo "<tr><th>Timestamp</th><th>Message Source</th><th>Message</th></tr>\n";
echo "<div id=\"stuffGoesHere\"></div>\n";

echo "<script id=\"updates-tmpl\" type=\"text/x-handlebars-template\">\n";
echo "{{#updateSequence}}\n";
echo "<tr><td>{{timestamp}}</td><td>{{source}}</td><td>{{value}}</td></tr>\n";
echo "{{/updateSequence}}\n";
echo "</script>\n";

echo "<script>\n";
echo "\tvar data;\n";
echo "\t$().ready(function() {\n";
//echo "\t\t\$.ajax( {url:\"/trivnet/agents/detail.php?id=$id\",success:function(result){\n";
//echo "\t\t\tdata = result;\n";
//echo "\t}})});\n";
echo "\tvar source   = \$(\"#updates-tmpl\").html();\n";
echo "\tvar template = Handlebars.compile(source);\n";
echo "\tstuffGoesHere.innerHTML = template(upds));\n";
echo "\t}})});\n";
// echo "\tstuffGoesHere.innerHTML = \"This is a test\";\n";
// echo "\t$('body').append(template(data));\n";
echo "</script>";

echo "</table>\n";

echo "</body></html>";
?>
