<?php
/**
 * Tests general class functions.
 * This script is used during development of the class itself.
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQLi\Examples
 * @version 0.2.0 (07-Dec-2015)
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */
/**
 * Load in the general functions for all tests.
 */
require_once('functions.inc.php');

// Determine SAPI type
$d = WhichBR();

// Create new instance of class
$db = new db_MySQLi();

// Before doing anything connect first!
$db->Connect();

if($d['SAPI'] != 'cli')
  {
  echo('<pre>');
  }

echo($d['LF'].'General Test for db_MySQLi class'.$d['LF'].$d['LF']);

$u = $db->Query('SELECT USER()',MYSQLI_NUM);

printf("PHP Version / SAPI type......: %s / %s%s",phpversion(),$d['SAPI'],$d['LF']);
printf("db_MySQLi class Version......: %s%s",$db->GetClassVersion(),$d['LF']);
printf("Class connection type........: %s%s",mysqli_get_host_info($db->GetConnectionHandle()),$d['LF']);
printf("MySQL Server Version.........: %s%s",$db->Version(),$d['LF']);
printf("MySQL Client Version.........: %s%s",mysqli_get_client_info(),$d['LF']);
printf("Username from MySQL Server...: %s%s",$u[0],$d['LF']);
printf("AutoCommit state is..........: %s%s",$db->GetAutoCommit(),$d['LF']);
printf("AffectedRows() call..........: %s%s",$db->AffectedRows(),$d['LF']);
printf("lc_time_names setting........: %s%s",$db->get_TimeNames(),$d['LF']);

// Always disconnect when you don't need the database anymore
$db->Disconnect();

// Dump out all defined methods in the class:

$class_methods = get_class_methods('db_MySQLi');
natcasesort ($class_methods);

printf("%sList of defined functions (%s) in db_MySQLi class:%s%s",$d['LF'],count($class_methods),$d['LF'],$d['LF']);
$cnt = 1;
foreach ($class_methods as $method_name)
  {
  printf("%02d. %s%s",$cnt,$method_name,$d['LF']);
  $cnt++;
  }

DBFooter($d['LF'],$db);

echo($d['LF']);
exit;
?>
