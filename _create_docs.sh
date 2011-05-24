#!/bin/sh
# -o PDF:default
phpdoc \
       -d /html/private/PHP-Classes/MySQLi_class/ \
       -i *.png,*.gif,*.jpg,*.sh,*.zip,*.pak,*.html,*.css,*.ico,*.gz,*.js,*.txt,*.sql,*.csv,tests/ \
       -t /html/private/PHP-Classes/MySQLi_class/docs \
       -ti "MySQL improved PHP Class for PHP4 / PHP5" \
       -dn db_MySQLi 

