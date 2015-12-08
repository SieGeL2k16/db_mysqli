#!/usr/local/bin/php
<?php
/**
 * Examples how to use bind variables with the Query methods "QueryHash()" and "QueryResultHash()".
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQLi\Examples
 * @version 0.2.0 (07-Dec-2015)
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
$SQL  = "INSERT INTO t1 VALUES(?,?)";
$data = array();
for($i = 0; $i < 10; $i++)
  {
  $data = array(['VAL' => $i, 'TYPE' => db_MySQLi::DBOF_TYPE_INT], ['VAL' => 'MANUAL_'.$i, 'TYPE' => db_MySQLi::DBOF_TYPE_STRING]);
  $rc = $db->QueryHash($SQL,MYSQLI_ASSOC,1,$data);
  }
*/


$SQL  = "SELECT ID,NAME FROM t1 WHERE ID=?";
$sp   = array(['VAL' => 11, 'TYPE' => db_MySQLi::DBOF_TYPE_INT]);
$rc = $db->QueryHash($SQL,MYSQL_ASSOC,0,$sp);

var_dump($rc);

echo("-----\n");

$rc = $db->Query("SELECT ID,NAME FROM t1 WHERE ID=110");

var_dump($rc);


$db->Disconnect();
