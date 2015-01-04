#!/bin/sh
echo "Removing old docs."
rm -rf ./docs/*
echo "Creating class documentation."
phpdoc run \
 --filename dbdefs.inc.php,mysqlidb_class.php \
 -t ./docs \
 --title  "MySQL Class utilizing the MySQLi PHP extension" \
 --defaultpackagename "db_MySQLi"

