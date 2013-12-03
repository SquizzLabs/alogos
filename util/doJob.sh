#!/bin/bash

cd /var/www/skillq.net/util/

mkdir /tmp/locks/ 2>/dev/null

lockFile=/tmp/locks/skq_$1.lock

flock -w 63 $lockFile php5 doJob.php $1 #>/dev/null 2>/dev/null
