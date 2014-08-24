<?php
/**
 * All definitions for the mysqlidb_class.
 * Configure the database access here together with some other class options.
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQLi
 * @version 0.11 (24-Aug-2014)
 * $Id$
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Name of application using this class (used in error.log etc.).
 */
define('MYSQLAPPNAME' , 'MySQLi_class');

/**
 * Hostname/IP address of database.
 */
define('MYSQLDB_HOST' , 'localhost');

/**
 * Port number of database.
 * Defaults to 3306 and should be given only if port differ.
 */
//define('MYSQLDB_PORT' , 3306);

/**
 * Username to access database.
 */
define('MYSQLDB_USER' , 'siegel');

/**
 * Password to use.
 */
define('MYSQLDB_PASS' , 'siegel');

/**
 * Database schema to use.
 */
define('MYSQLDB_DATABASE' , 'siegel');

/**
 * Modify default error behavour of class.
 * Default is db_MySQLi::DBOF_SHOW_NO_ERRORS if you omit this parameter.
 */
define('DB_ERRORMODE', db_MySQLi::DBOF_SHOW_ALL_ERRORS);

/**
 * Set address to be shown in case of an error.
 * If this is not set the default address of $_SERVER['SERVER_ADMIN'] is used.
 */
define('MYSQLDB_ADMINEMAIL' , 'php@saschapfalz.de');

/**
 * Set this define to 1 if you want auto-emails to be sent whenever an
 * error occures. Default is 0 (disabled).
 */
define('MYSQLDB_SENTMAILONERROR', 0);

/**
 * You can define here additional parameters to be passed to the mail() call.
 * Some servers may need the "-f enter@your.mail" parameter for example.
 * Default is unset.
 */
//define('MYSQLDB_MAIL_EXTRAARGS' , '-fwebmaster@yourdomain.com');

/**
 * Set this define to 1 if you want to use persistant connections as default connection.
 * Default is 0, which means that mysql_connect is used instead. (new connections).
 * @since 0.11
 */
define('MYSQLDB_USE_PCONNECT'   , 1);

/**
 * You can set here the character set used when connecting.
 * This is only performed if this define is set, else no specific character set is set.
 * To get a list of all supported character sets connect to MySQL and issue the following:
 * SHOW CHARACTER SET;
 * The class performs a "SET NAMES 'utf8';" query if you define i.e. 'utf8' as characterset.
 * @since 0.11
 */
define('MYSQLDB_CHARACTERSET'   , 'utf8');

/**
 * To have Day and month names returned in a specific locale, define here a valid countrycode.
 * Note that this is supported since MySQL 5.0.25 (see http://dev.mysql.com/doc/refman/5.0/en/locale-support.html)
 * If you do not define this value, the class does not set any lc_time_names at all.
 * If a define is given the class performs a "SET lc_time_names = 'de_DE';" query to enable german locale.
 * @since 0.11
 */
define('MYSQLDB_TIME_NAMES'     , 'de_DE');

/**
 * If you use this class as replacement for my old class "db_MySQL" set this parameter below to TRUE.
 * In this case the old defines are defined to match the new class values.
 */
define('MYSQLDB_COMPATIBLE_MODE'       , FALSE);
?>
