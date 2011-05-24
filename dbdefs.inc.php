<?php
/**
 * All definitions for the mysqlidb_class.
 * Configure the database access here together with some other class options.
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQLi
 * @version 0.10 (13-Mar-2008)
 * $Id: dbdefs.inc.php,v 1.2 2008/03/16 09:20:52 siegel Exp $
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Name of application using this class (used in error.log etc.).
 */
define('MYSQLiAPPNAME' , 'MySQLi_class');

/**
 * Hostname/IP address of database.
 */
define('MYSQLiDB_HOST' , 'localhost');

/**
 * Port number of database.
 * Defaults to 3306 and should be given only if port differ.
 */
//define('MYSQLiDB_PORT' , 3306);

/**
 * Username to access database.
 */
define('MYSQLiDB_USER' , 'siegel');

/**
 * Password to use.
 */
define('MYSQLiDB_PASS' , 'siegel');

/**
 * Database schema to use.
 */
define('MYSQLiDB_DATABASE' , 'siegel');

/**
 * Modify default error behavour of class.
 * Default is DBOF_SHOW_NO_ERRORS if you omit this parameter.
 */
define('DB_ERRORMODE', DBOF_SHOW_ALL_ERRORS);

/**
 * Set address to be shown in case of an error.
 * If this is not set the default address of $_SERVER['SERVER_ADMIN'] is used.
 */
define('MYSQLiDB_ADMINEMAIL' , 'php@saschapfalz.de');

/**
 * Set this define to 1 if you want auto-emails to be sent whenever an
 * error occures. Default is 0 (disabled).
 */
define('MYSQLiDB_SENTMAILONERROR', 0);

/**
 * You can define here additional parameters to be passed to the mail() call.
 * Some servers may need the "-f enter@your.mail" parameter for example.
 * Default is unset.
 */
//define('MYSQLiDB_MAIL_EXTRAARGS' , '-fwebmaster@yourdomain.com');
?>
