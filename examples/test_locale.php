#!/usr/local/bin/php
<?php
/**
 * Examples how change the locale of MySQL messages and how to change the character set.
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

echo($d['LF']."Locale & character set tests".$d['LF']);
echo($d['HR']);

// First connect to the database:

$sock = $db->Connect();

// Retrieve the current lc_time_names variable:

$current_locale = $db->get_TimeNames();
printf("Current Locale of MySQL connection is..........: '%s'%s",$current_locale, $d['LF']);

// Now set it to english, no matter what is was previously, and check if right afterwards.

$db->set_TimeNames('en_US');
printf("New locale after changing to english...........: '%s'%s",$db->get_TimeNames(),$d['LF']);

// Read the names of some dates in english:

$test_date = '2018-05-31';

$eng_dates = $db->Query("SELECT DATE_FORMAT('".$test_date."','%W %a %M %b') AS DF");

printf("Date '%s' formatted by MySQL in english: '%s'%s",$test_date,$eng_dates['DF'],$d['LF']);

// Now we switch to german and perform the same query again:

$db->set_TimeNames('de_DE');
printf("New locale after changing to german............: '%s'%s",$db->get_TimeNames(),$d['LF']);
$eng_dates = $db->Query("SELECT DATE_FORMAT('".$test_date."','%W %a %M %b') AS DF");

printf("Date '%s' formatted by MySQL in german.: '%s'%s%s",$test_date,$eng_dates['DF'],$d['LF'],$d['LF']);

// Now we dump out the current settings for character set support:

echo("List of currently active character set settings for this MySQL connection.".$d['LF'].$d['LF']);

$current_charsets = $db->get_CharSet();
for($i = 0; $i < count($current_charsets); $i++)
  {
  printf("%s%s: %s%s",$current_charsets[$i][0],str_repeat('.',(47-strlen($current_charsets[$i][0]))),$current_charsets[$i][1],$d['LF']);
  }
echo($d['LF'].'Now changing character set to utf8:'.$d['LF'].$d['LF']);
$db->set_CharSet('utf8');
$current_charsets = $db->get_CharSet();
for($i = 0; $i < count($current_charsets); $i++)
  {
  printf("%s%s: %s%s",$current_charsets[$i][0],str_repeat('.',(47-strlen($current_charsets[$i][0]))),$current_charsets[$i][1],$d['LF']);
  }

echo($d['LF'].$d['HR']);
DBFooter($d['LF'],$db);

$db->Disconnect();
if($d['SAPI'] != 'cli')
  {
  echo("</pre>\n");
  }

