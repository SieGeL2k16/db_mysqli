#!/usr/local/bin/php
<?php
/**
 * Example shows how to do:
 * - CREATE TABLE
 * - INSERT
 * - SELECT
 * - UPDATE
 * - DROP TABLE
 *
 * Please note that this example requires a valid login to your own
 * database which allows to create/drop tables and INSERT/UPDATE data!
 * The example won't run until you setup a valid login for your database!
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQLi
 * @subpackage Examples
 * @version 0.11 (24-Aug-2014)
 * $Id: test_ddl_dml.php 26 2014-08-24 22:44:53Z siegel $
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */
/**
 */
require('functions.inc.php');
$d = WhichBR();
if($d['SAPI'] != 'cli')
  {
  echo("<pre>\n");
  }
echo($d['LF']);

/**
 * Enter here your OWN (!) login data for your MySQL database. As long as
 * you do not configure these defines this example WON'T RUN!
 */

/** Database user name */
define('DB_USER', 'siegel');

/** Database user password */
define('DB_PASS', 'siegel');

/** Database hostname (can be empty, localhost is then assumed) */
define('DB_HOST', '');

/** Database TCP port number to use, defaults to 3306 */
define('DB_PORT', 3306);

/** Database schema name */
define('DB_NAME', 'siegel');

if(DB_USER == '' || DB_PASS == '' || DB_NAME == '')
  {
  die('ERROR: Example is not configured - Please modify the defines to let this example work!'.$d['LF'].$d['LF']);
  }

/*
 * Create database class object and try to connect.
 * Auto-error is on so if the logindata is wrong connect() won't return.
 */

$db = new db_MySQLi;
$db->Connect(DB_USER,DB_PASS,DB_HOST,DB_NAME,DB_PORT);

/*
 * The DDL query to create a simple table which we will fill up with random data afterwards.
 */

$ddl_query[0] = 'DROP TABLE IF EXISTS MYSQLi_DB_TEST';
$ddl_query[1]=<<<EOM
CREATE TABLE MYSQLi_DB_TEST
  (
  ID        INTEGER NOT NULL AUTO_INCREMENT,
  TESTFIELD VARCHAR(200),
  PRIMARY KEY(ID)
  )
 COMMENT='db_MySQLi class testing table'
EOM;

echo('Creating test table "MYSQLi_DB_TEST" in database '.DB_NAME.'.'.$d['LF'].$d['LF']);

/*
 * Now first let us create the table.
 * To be on the safe side we first check if we need to drop an existing table, then we create it again.
 * NOTE: We do not check anything here for errors, this is all done automatically be the class!
 */
for($i = 0; $i < count($ddl_query); $i++)
  {
  $db->Query($ddl_query[$i]);
  }

echo('Table created. Now INSERTing 10 rows with random data.'.$d['LF'].$d['LF']);

/*
 * Table is now created (else class would have exited in previous loop!),
 * so start filling it with 10 rows of randomized data:
 */

for($i = 0; $i < 10; $i++)
  {
  $db->Query(sprintf("INSERT INTO MYSQLi_DB_TEST(TESTFIELD) VALUES('%s')",mt_rand(0,99999999)));
  }

/*
 * Just for safety a COMMIT is performed, not necessary for MyISAM tables of course.
 */

$db->Commit();

echo($i.' rows inserted to table, now fetching them.'.$d['LF'].$d['LF']);
echo($d['HR'].$d['LF']);

/*
 * Now we fetch here the data and display it to the user. We are using here the MYSQL_ASSOC result set to have
 * associative arrays available. This is better readable than using numbered arrays.
 */

$stmt = $db->QueryResult('SELECT ID,TESTFIELD FROM MYSQLi_DB_TEST ORDER BY ID');
while($data = $db->FetchResult($stmt))
  {
  printf("ID: %2d | TESTFIELD: %-30s%s",$data['ID'],$data['TESTFIELD'],$d['LF']);
  }
$db->FreeResult($stmt);

/*
 * Now we update all data to have the same value (no WHERE clause).
 * We will modify the TESTFIELD entries to have all the value '007' stored :)
 */

echo($d['LF'].'Updating field "TESTFIELD" on all rows with new value "007"'.$d['LF'].$d['LF']);

$db->Query("UPDATE MYSQLi_DB_TEST SET TESTFIELD='007'");
$db->Commit();

/*
 * Finally select again all rows and display them to validate that the update was successful.
 */

echo('Now fetching again all rows to validate the update'.$d['LF']);
echo($d['HR'].$d['LF']);

$stmt = $db->QueryResult('SELECT ID,TESTFIELD FROM MYSQLi_DB_TEST ORDER BY ID');
while($data = $db->FetchResult($stmt))
  {
  printf("ID: %2d | TESTFIELD: %-30s%s",$data['ID'],$data['TESTFIELD'],$d['LF']);
  }
$db->FreeResult($stmt);

/*
 * Okay before ending this example we will remove our table.
 * The query is still available in the variable $ddl_query[0].
 */

echo($d['LF'].'Dropping test table "MYSQLi_DB_TEST"'.$d['LF'].$d['LF']);
$db->Query($ddl_query[0]);

/* Finally print out some stats */
echo($d['LF'].$db->GetQueryCount()." queries took ".round($db->GetQueryTime(),3)." seconds.".$d['LF']);
$db->Disconnect();
if($d['SAPI'] != 'cli')
  {
  echo("</pre>\n");
  }
?>
