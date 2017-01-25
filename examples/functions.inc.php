<?php
/**
 * Set of functions used in the MySQLi examples.
 * @package db_MySQLi
 * @subpackage Examples
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @version 0.11 (24-Aug-2014)
 * $Id: functions.inc.php 26 2014-08-24 22:44:53Z siegel $
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */
/**
 * Make sure that we get noticed about EVERYTHING problematic in our code:
 */
ini_set('error_reporting' , E_ALL|E_NOTICE|E_STRICT);
/**
 * Load in the class
 */
require_once('../db_mysqli.class.php');

/**
 * Returns an associative array with sapi-type name and required line break char.
 * Use this function to retrieve the required line-break character for both the
 * browser output and shell output. Currently only two keys are included:
 * - "SAPI" => The sapi type of PHP (i.e. "cli")
 * - "LF"   => The line-break character to use (i.e. "<br>")
 * @return array The associative array as described.
 */
function WhichBR()
  {
  $data = array();
  $data['SAPI'] = php_sapi_name();
  switch($data['SAPI'])
    {
    case  'cli':
          $data['LF'] = "\n";
          $data['HR'] = "------------------------------------------------------------------------------\n";
          break;
    default:
          $data['LF'] = "<br>";
          $data['HR'] = "<hr>";
          break;
    }
  return($data);
  }

/**
 * Prints out the amount of queries and the time required to process them.
 * @param string $lf The linefeed character to use.
 * @param mixed &$dbh The database object.
 */
function DBFooter($lf, &$dbh)
  {
  printf("%sQueries: %d | Time required: %5.3fs%s",$lf,$dbh->GetQueryCount(),$dbh->GetQueryTime(),$lf);
  }

/**
 * Prints out aligned text
 */
function PrintCon($maxlen,$fstr)
  {
  printf("%s%s: ",$fstr,str_repeat(".",$maxlen-strlen($fstr)-2));
  flush();
  }
