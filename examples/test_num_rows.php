#!/usr/local/bin/php
<?php
/**
 * Examples how to use the NumRows() and AffectedRows().
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_mysqli\Examples
 * @version 1.0.0 (31-May-2018)
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */
/**
 */
require('functions.inc.php');
$db = new spfalz\db_mysqli('../dbdefs.inc.php');
$d = WhichBR();
if($d['SAPI'] != 'cli')
  {
  echo("<pre>\n");
  }
$db->Connect();
$db->Query('SHOW TABLES');
printf("There are %d tables stored in database %s\n",$db->NumRows(),MYSQLDB_DATABASE);

// Now do the same with QueryResult, just to make sure everything works:

$stmt = $db->QueryResult('SHOW TABLES');
printf("There are %d tables stored in database %s\n",$db->NumRows(),MYSQLDB_DATABASE);
$db->FreeResult($stmt);

$db->Disconnect();
exit;

