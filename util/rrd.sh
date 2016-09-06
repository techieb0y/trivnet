#!/bin/bash

rrdtool create --step 60 medtent.rrd DS:inmedtent:GAUGE:120:0:64 RRA:AVERAGE:0.5:1:1440
