#!/usr/local/bin/php
<?php
/**
 * Examples how to handle connections to MySQL.
 * First connect try is without automatic error handling, second try is with automatic handling on.
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQL
 * @subpackage Examples
 * @version 0.36 (21-Sep-2008)
 * $Id: test_connect.php 26 2014-08-24 22:44:53Z siegel $
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 * @filesource
 */
/**
 */
require('functions.inc.php');
$db = new db_MySQLi('../dbdefs.inc.php');

$d = WhichBR();

/*
 * Turn off automatic error handling:
 */
$db->SetErrorHandling(db_MySQLi::DBOF_RETURN_ALL_ERRORS);
$db->SetDebug(db_MySQLi::DBOF_DEBUG_SCREEN);
echo($d['LF']."Testing connectivity to MySQL (U=".MYSQLDB_USER."|P=".MYSQLDB_PASS."|H=".MYSQLDB_HOST."|DB=".MYSQLDB_DATABASE.')'.$d['LF'].$d['LF']);

$sock = $db->Connect();
if(!$sock)
  {
  echo("Unable to connect to database(".$db->GetErrno()."): ".$db->GetErrorText().$d['LF']);
  }
else
  {
  echo('Connected'.$d['LF']);
  $db->Disconnect();
  }

echo($d['HR']);
echo($d['LF'].'Now trying to connect with automatic error handling:'.$d['LF'].$d['LF']);

/*
 * Now turn on automatic error handling:
 */

$db->setErrorHandling(db_MySQLi::DBOF_SHOW_ALL_ERRORS);

$sock = $db->Connect();
echo('Connected'.$d['LF'].$d['LF']);
$db->Disconnect();
echo($d['HR']);

/*
 * Test persistant connection
 */
$db->SetPConnect(TRUE);
$db->Connect();
echo('Connected (persistant).'.$d['LF'].$d['LF']);
$db->Disconnect();


DBFooter($d['LF'].$d['LF'],$db);
?>
