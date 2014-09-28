#!/usr/local/bin/php
<?php
/**
 * Examples how to use the CreateNewInsert() method.
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQLi
 * @subpackage Examples
 * @version 0.11 (24-Aug-2014)
 * $Id: test_new_insert.php 26 2014-08-24 22:44:53Z siegel $
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

/*
 * Table for this test (must exist on the target db!!!!)
 */

$table=<<<EOM
CREATE TABLE IF NOT EXISTS db_MySQLi_new_insert_test
  (
  id      INTEGER NOT NULL AUTO_INCREMENT,
  field1  VARCHAR(50),
  field2  VARCHAR(50),
  PRIMARY KEY(id)
  )
EOM;

$testdata = array();

$testdata[1]['field1']  = 'wert1/1';
$testdata[1]['field2']  = 'wert1/2';

$testdata[3]['field1']  = 'wert3/1';
$testdata[3]['field2']  = 'wert3/2';

$testdata[5]['field1']  = 'wert5/1';
$testdata[5]['field2']  = 'wert5/2';

$testdata[6]['field1']  = 'wert6/1';
$testdata[6]['field2']  = 'wert6/2';


echo($d['LF']."PerformNewInsert() test".$d['LF']);
echo($d['HR']);

// First connect to the database:

$db->Connect();

// Check if we need to create the table:
printf("Creating test table.%s%s",$d['LF'],$d['LF']);
$db->Query($table);

printf("Dump of testdata used to insert into \"db_MySQLi_new_insert_test\"%s%s",$d['LF'],$d['LF']);

print_r($testdata);
printf("Now calling PerformNewInsert() on these data...%s%s",$d['LF'],$d['LF']);

$retcode = $db->PerformNewInsert('db_MySQLi_new_insert_test',$testdata);
if(!is_array($retcode))
  {
  $errmsg = $db->GetErrorText();
  $db->Disconnect();
  printf("DB-ERROR DURING INSERT: %s%s%s",$errmsg,$d['LF'],$d['LF']);
  exit;
  }
printf("Used %d INSERT statement(s) with a total size of %u bytes for %d entries.%s",$retcode[0],$retcode[1],count($testdata),$d['LF']);
echo($d['HR']);

// Now read out the data to validate input:

printf("Reading table data:%s%s",$d['LF'],$d['LF']);
printf("field1     | field2%s",$d['LF']);
printf("-----------+------------------%s",$d['LF']);
$query = 'SELECT field1, field2 FROM db_MySQLi_new_insert_test';
$stmt = $db->QueryResult($query);
while($data = $db->FetchResult($stmt))
  {
  printf("%10s | %s%s",$data['field1'],$data['field2'],$d['LF']);
  }
$db->FreeResult($stmt);
printf("%sFinally dropping test table \"db_MySQLi_new_insert_test\"%s%s",$d['LF'],$d['LF'],$d['LF']);
$db->Query('DROP TABLE db_MySQLi_new_insert_test');

// Print out footer and disconnect from database.
DBFooter($d['LF'],$db);

$db->Disconnect();
if($d['SAPI'] != 'cli')
  {
  echo("</pre>\n");
  }
?>
