#!/usr/local/bin/php
<?php
/**
 * Examples how to use bind variables with the Query methods "QueryHash()" and "QueryResultHash()".
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_mysqli\Examples
 * @version 1.0.0 (31-May-2018)
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */
/**
 */
require('functions.inc.php');

define('ROWS_TO_CREATE', 100000);
define('MAX_ITERATIONS', 10000);

$db = new spfalz\db_mysqli('../dbdefs.inc.php');
$d = WhichBR();
if($d['SAPI'] != 'cli')
  {
  echo("<pre>\n");
  }
echo($d['LF']);

$db->setErrorHandling(spfalz\db_mysqli::DBOF_SHOW_ALL_ERRORS);
$sock = $db->Connect();

/* Define some queries to create and drop our test table MYSQLI_DB_TEST_BINDVARS */
$ddlquery = array('','');
$ddlquery[0]=<<<EOM
CREATE TABLE MYSQLI_DB_TEST_BINDVARS
  (
  ID      INTEGER NOT NULL,
  NAME    VARCHAR(50),
  PRIMARY KEY(ID)
  )
EOM;
$ddlquery[1]="DROP TABLE MYSQLI_DB_TEST_BINDVARS";

/******************************************************************************
 * Now create first the table - This will fail if given table already exists
 ******************************************************************************/
PrintCon(60,"Creating test table MYSQLI_DB_TEST_BINDVARS");
$rc = $db->Query($ddlquery[0],MYSQLI_ASSOC,1);
if(is_bool($rc) == FALSE)
  {
  $error = $db->GetErrorText();
  $db->Disconnect();
  die(sprintf("FAILED!\n%s\n\n",$error));
  }
echo("OK!\n\n");

/******************************************************************************
 * Fill in random rows for testing by using static query
 ******************************************************************************/
PrintCon(60,sprintf("Adding %d rows with \"Query()\" method",ROWS_TO_CREATE));
$start = microtime(true);
for($i = 0; $i < ROWS_TO_CREATE; $i++)
  {
  $SQL  = sprintf("INSERT INTO MYSQLI_DB_TEST_BINDVARS VALUES(%d,'%s')",$i,$db->EscapeString('MANUAL_'.$i));
  $rc = $db->Query($SQL,MYSQLI_ASSOC,1);
  }
printf("finished in %5.3fs\n\n",(microtime(true) - $start));

$db->Query("TRUNCATE TABLE MYSQLI_DB_TEST_BINDVARS");

/******************************************************************************
 * Fill in random rows for testing by using prepare()/execute() and bind variables
 ******************************************************************************/
PrintCon(60,sprintf("Adding %d rows with prepare()/execute()",ROWS_TO_CREATE));
$start = microtime(true);
$SQL  = "INSERT INTO MYSQLI_DB_TEST_BINDVARS VALUES(?,?)";
$stmt = $db->Prepare($SQL);
$data = array();
for($i = 0; $i < ROWS_TO_CREATE; $i++)
  {
  $data = array([$i,spfalz\db_mysqli::DBOF_TYPE_INT], ['MANUAL_'.$i,spfalz\db_mysqli::DBOF_TYPE_STRING]);
  $rc = $db->Execute($stmt,0,$data);
  }
$db->FreeResult($stmt);
printf("finished in %5.3fs\n\n",(microtime(true) - $start));

/******************************************************************************
 * Now call first QueryHash() and measure time required
 ******************************************************************************/
PrintCon(60,sprintf("Calling %d times method \"QueryHash()\"",MAX_ITERATIONS));
$SQL    = "SELECT ID,NAME FROM MYSQLI_DB_TEST_BINDVARS WHERE ID=?";
$start  = microtime(true);
for($i = 0; $i < MAX_ITERATIONS; $i++)
  {
  $ID = mt_rand(0,(ROWS_TO_CREATE) -1);

  // Now fetch first a row with bind variables
  $sp   = array([$ID,spfalz\db_mysqli::DBOF_TYPE_INT]);
  $rc = $db->QueryHash($SQL,MYSQLI_ASSOC,0,$sp);
  }
printf("finished in %5.3fs (%5d = %s)\n\n",(microtime(true)-$start),$rc['ID'],$rc['NAME']);flush();


/******************************************************************************
 * Now call prepare() and execute() to fetch data and measure time required
 ******************************************************************************/
PrintCon(60,sprintf("Calling %d times method \"Prepare()/Execute()\"",MAX_ITERATIONS));
$SQL    = "SELECT ID,NAME FROM MYSQLI_DB_TEST_BINDVARS WHERE ID=?";
$start  = microtime(true);
$stmt   = $db->Prepare($SQL);
for($i = 0; $i < MAX_ITERATIONS; $i++)
  {
  $ID = mt_rand(0,(ROWS_TO_CREATE) -1);
  $sp   = array([$ID,spfalz\db_mysqli::DBOF_TYPE_INT]);
  $db->Execute($stmt,0,$sp);
  $row = $db->FetchResult($stmt);
  }
$db->FreeResult($stmt);
printf("finished in %5.3fs (%5d = %s)\n\n",(microtime(true)-$start),$row['ID'],$row['NAME']);flush();

/******************************************************************************
 * Now call Query() multiple times and measure time
 ******************************************************************************/
PrintCon(60,sprintf("Calling %d times method \"Query()\"",MAX_ITERATIONS));
$start = microtime(true);
for($i = 0; $i < MAX_ITERATIONS; $i++)
  {
  $ID = mt_rand(0,(ROWS_TO_CREATE - 1));
  $rc = $db->Query(sprintf("SELECT ID,NAME FROM MYSQLI_DB_TEST_BINDVARS WHERE ID=%u",$ID));
  }
printf("finished in %5.3fs (%5d = %s)\n\n",(microtime(true)-$start),$rc['ID'],$rc['NAME']);

/******************************************************************************
 * Read out all rows using QueryResult()
 ******************************************************************************/
PrintCon(60,sprintf("Reading %d rows with \"QueryResult()\"",MAX_ITERATIONS));
$start  = microtime(true);
$res    = $db->QueryResult("SELECT ID,NAME FROM MYSQLI_DB_TEST_BINDVARS");
$cnt    = 0;
while($d = $db->FetchResult($res))
  {
  $cnt++;
  }
$db->FreeResult($res);
printf("finished in %5.3fs (%d rows)\n\n",(microtime(true)-$start),$cnt);

/******************************************************************************
 * Read out all rows using Prepare/Execute()
 ******************************************************************************/
PrintCon(60,sprintf("Reading all rows with \"Prepare()\"/\"Execute()\"",MAX_ITERATIONS));
$start  = microtime(true);
$stmt   = $db->Prepare("SELECT ID,NAME FROM MYSQLI_DB_TEST_BINDVARS");
$rc     = $db->Execute($stmt);
$cnt    = 0;
while($d = $db->FetchResult($stmt))
  {
  mysqli_stmt_bind_result($stmt,$dataid,$data);
  $cnt++;
  }
$db->FreeResult($stmt);
printf("finished in %5.3fs (%d rows)\n\n",(microtime(true)-$start),$cnt);

/******************************************************************************
 * Finally drop the table
 ******************************************************************************/
$db->Query($ddlquery[1]);
$db->Disconnect();
