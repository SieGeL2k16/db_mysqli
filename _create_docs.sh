#!/bin/sh
echo "Removing old docs."
rm -rf ./docs/*
echo "Creating class documentation."
/usr/src/phpDocumentor-2.9.0/bin/phpdoc run \
 --filename dbdefs.inc.php,db_mysqli.class.php \
 -t ./docs \
 --title  "MySQL Class utilizing the MySQLi PHP extension" \
 --defaultpackagename "db_MySQLi"

