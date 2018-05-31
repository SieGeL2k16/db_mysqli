#!/usr/local/bin/php
<?php
/**
 * Examples how to use bind variables with the Query method "QueryResultHash()".
 * Compares variants with fixed SQL (Test 1), Bind vars via QueryResultHash() (Test 2) and native code via mysqli class (Test 3)
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_mysqli\Examples
 * @version 1.0.0 (31-May-2018)
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */
/**
 */
require('functions.inc.php');

define('ROWS_TO_CREATE', 100);

$db = new spfalz\db_mysqli('../dbdefs.inc.php');
$d = WhichBR();
if($d['SAPI'] != 'cli')
  {
  echo("<pre>\n");
  }
echo($d['LF']);

$db->setErrorHandling(spfalz\db_mysqli::DBOF_SHOW_ALL_ERRORS);
$sock = $db->Connect();

/****************************************************************************
 * Create first the required test table and populate it with data:
 ****************************************************************************/
$ddlquery = array('','');
$ddlquery[0]=<<<EOM
CREATE TABLE IF NOT EXISTS MYSQLI_DB_TEST_QUERIES
  (
  ID      INTEGER NOT NULL,
  NAME    VARCHAR(100),
  PRIMARY KEY(ID)
  )
 CHARSET='utf8'
EOM;
$ddlquery[1]="DROP TABLE MYSQLI_DB_TEST_QUERIES";

PrintCon(60,"Creating test table MYSQLI_DB_TEST_QUERIES");
$rc = $db->Query($ddlquery[0],MYSQLI_ASSOC,1);
if(is_bool($rc) == FALSE)
  {
  $error = $db->GetErrorText();
  $db->Disconnect();
  die(sprintf("FAILED!\n%s\n\n",$error));
  }
echo("OK!\n\n");
PrintCon(60,sprintf("Adding %d rows with prepare()/execute()",ROWS_TO_CREATE));
$start = microtime(true);
$SQL  = "INSERT INTO MYSQLI_DB_TEST_QUERIES VALUES(?,?)";
$stmt = $db->Prepare($SQL);
$data = array();
for($i = 0; $i < ROWS_TO_CREATE; $i++)
  {
  $data = array([$i,spfalz\db_mysqli::DBOF_TYPE_INT], ['NAME',spfalz\db_mysqli::DBOF_TYPE_STRING]);
  $rc = $db->Execute($stmt,0,$data);
  }
$db->FreeResult($stmt);
printf("finished in %5.3fs\n\n",(microtime(true) - $start));

/****************************************************************************
 * First test with fixed SQL (not recommended anymore)
 ****************************************************************************/

echo(str_repeat("-",70));
echo("\nTesting QueryResult() with fixed SQL\n");
echo(str_repeat("-",70)."\n");

$stmt = $db->QueryResult("SELECT ID,NAME FROM MYSQLI_DB_TEST_QUERIES WHERE NAME='NAME' LIMIT 3");
while($d = $db->FetchResult($stmt))
  {
  printf("ID=%u | NAME=%s\n",$d['ID'],$d['NAME']);
  }
$db->FreeResult($stmt);

/****************************************************************************
 * Now test with QueryResultHash() using bind vars
 ****************************************************************************/

echo(str_repeat("-",70));
echo("\nTesting QueryResultHash() with bind vars\n");
echo(str_repeat("-",70)."\n");

$SQL  = "SELECT ID,NAME FROM MYSQLI_DB_TEST_QUERIES WHERE NAME=? LIMIT 3";
$sp   = array(['NAME','s']);
$stmt = $db->QueryResultHash($SQL,0,$sp);
while($d = $db->FetchResult($stmt))
  {
  printf("ID=%u | NAME=%s\n",$d['ID'],$d['NAME']);
  }
$db->FreeResult($stmt);
$db->Disconnect();

/****************************************************************************
 * Finally bind vars with mysqli class from PHP
 ****************************************************************************/

echo(str_repeat("-",70));
echo("\nNow testing the same with native code\n");
echo(str_repeat("-",70)."\n");

$mysqli = new mysqli(MYSQLDB_HOST, MYSQLDB_USER, MYSQLDB_PASS, MYSQLDB_DATABASE);
/* check connection */
if (mysqli_connect_errno())
  {
  printf("Connect failed: %s\n", mysqli_connect_error());
  exit();
  }
$stmt = $mysqli->prepare($SQL);
$bvar = 'NAME';
$stmt->bind_param('s',$bvar);
$stmt->execute();
$c1 = $c2 = null;
$stmt->bind_result($c1,$c2);
while($stmt->fetch())
  {
  printf("[BIND] ID=%s | NAME=%s\n",$c1,$c2);
  }
$stmt->close();
$mysqli->close();

/******************************************************************************
 * Finally drop the table
 ******************************************************************************/
$db->Connect();
$db->Query($ddlquery[1]);
$db->Disconnect();
