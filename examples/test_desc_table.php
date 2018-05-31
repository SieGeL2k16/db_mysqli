#!/usr/local/bin/php
<?php
/**
 * Examples how to show the fields of a given table.
 * This example simply reads out all table names available for your schema,
 * take the first shown table and calls the method "DescTable()" with the found
 * tablename. Afterwards this table structure is dumped on screen.
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

// Retrieve the first available table:

$stmt = $db->QueryResult('SHOW TABLES');
while($table = $db->FetchResult($stmt,MYSQLI_NUM))
  {
  break;
  }
$db->FreeResult($stmt);
if($table[0] == '')
  {
  $db->Disconnect();
  die('ERROR: No table found?? Please create at least one table to describe.'.$d['LF']);
  }
$tfields = $db->DescTable($table[0]);
printf('%sTable %s has %d fields:%s%s',$d['LF'],$table[0],count($tfields),$d['LF'],$d['LF']);
print('Fieldname                        | Type/Size          | Flags'.$d['LF']);
print('---------------------------------+--------------------+----------------------'.$d['LF']);
for($i = 0; $i < count($tfields); $i++)
  {
  $type = sprintf("%s(%d)",$tfields[$i][spfalz\db_mysqli::DBOF_MYSQL_COLTYPE],$tfields[$i][spfalz\db_mysqli::DBOF_MYSQL_COLSIZE]);

  printf('%-32s | %-18s | %s%s',$tfields[$i][spfalz\db_mysqli::DBOF_MYSQL_COLNAME],
                                $type,
                                $tfields[$i][spfalz\db_mysqli::DBOF_MYSQL_COLFLAGS],
                                $d['LF']);
  }
echo($d['HR']);
DBFooter($d['LF'],$db);
$db->Disconnect();
echo($d['LF']);
