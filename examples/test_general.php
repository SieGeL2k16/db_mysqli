<?php
/**
 * Tests general class functions.
 * This script is used during development of the class itself.
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_mysqli\Examples
 * @version 1.0.0 (31-May-2018)
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */
/**
 * Load in the general functions for all tests.
 */
require_once('functions.inc.php');

// Determine SAPI type
$d = WhichBR();

// Create new instance of class
$db = new spfalz\db_mysqli();

// Before doing anything connect first!
$db->Connect();

if($d['SAPI'] != 'cli')
  {
  echo('<pre>');
  }

echo($d['LF'].'General Test for db_mysqli class'.$d['LF'].$d['LF']);

$u = $db->Query('SELECT USER()',MYSQLI_NUM);

printf("PHP Version / SAPI type......: %s / %s%s",phpversion(),$d['SAPI'],$d['LF']);
printf("db_mysqli class Version......: %s%s",$db->GetClassVersion(),$d['LF']);
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

$class_methods = get_class_methods('spfalz\db_mysqli');
natcasesort ($class_methods);

printf("%sList of defined functions (%s) in spfalz\db_mysqli class:%s%s",$d['LF'],count($class_methods),$d['LF'],$d['LF']);
$cnt = 1;
foreach ($class_methods as $method_name)
  {
  printf("%02d. %s%s",$cnt,$method_name,$d['LF']);
  $cnt++;
  }

DBFooter($d['LF'],$db);

echo($d['LF']);
exit;
