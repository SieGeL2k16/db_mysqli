#!/usr/local/bin/php
<?php
/**
 * Examples how to Fetch data from a table.
 * First a single-row function is called (SELECT VERSION()).
 * Second a multi-row function is called (SHOW VARIABLES).
 * Also GetQueryCount() and GetQueryTime() usage is shown.
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQLi
 * @subpackage Examples
 * @version 0.11 (24-Aug-2014)
 * $Id$
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */
/**
 */
require('functions.inc.php');
$db = new db_MySQLi('../dbdefs.inc.php');
$d = WhichBR();
if($d['SAPI'] != 'cli')
  {
  echo("<pre>\n");
  }
echo($d['LF']);

$db->setErrorHandling(db_MySQLi::DBOF_SHOW_ALL_ERRORS);
$sock = $db->Connect();

/*
 * First single-row query. The result of the query "SELECT VERSION()" returns a single
 * string with the current MySQL server version. As the flag "MYSQLI_NUM" is given to the
 * class, the result is returned as an numbered array which has only the element 0 containing
 * the Server version.
 */
$version = $db->Query("SELECT VERSION()",MYSQLI_NUM);
echo($d['LF'].'MySQL Database Version: '.$version[0].$d['LF']);
echo($d['HR'].$d['LF']);

// Now Multi-Row query:

$query=<<<SQL
SHOW VARIABLES
SQL;

$stmt = $db->QueryResult($query);

/*
 * Dump out the data from the stored resultset $stmt.
 * The SQL statement 'SHOW VARIABLES' returns two values which are then accessable as $t[0] and $t[1],
 * because the flag "MYSQLI_NUM" is set, which returns one row of data as numbered array.
 */
while($t = $db->FetchResult($stmt,MYSQLI_NUM))
  {
  printf("%30s: %s%s",$t[0],$t[1],$d['LF']);
  }
$db->FreeResult($stmt);

// Print out some stats:

echo($d['LF'].$db->GetQueryCount()." queries took ".round($db->GetQueryTime(),3)." seconds.".$d['LF'].$d['LF']);
$db->Disconnect();
if($d['SAPI'] != 'cli')
  {
  echo("</pre>\n");
  }
?>
