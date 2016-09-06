<?php
header("Content-type: image/png");

passthru("rrdtool graph - -a PNG DEF:count=medtent.rrd:inmedtent:AVERAGE LINE:count#FFFFFF");

?>
