<?php
/**
 * Set of functions used in the MySQLi examples.
 * @package db_MySQLi
 * @subpackage Examples
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @version 0.10 (14-Mar-2008)
 * $Id: functions.inc.php,v 1.1 2008/03/16 09:20:54 siegel Exp $
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */

error_reporting(E_ALL);   // Activate E_NOTICE to see coding warnings

/**
 * Load in the class
 */
require_once('../mysqlidb_class.php');

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
 * Checks if given Object name exists inside the database.
 * If checked object does not exist function can auto create the object if required DML is supplied
 * @param mixed &$dbh The database object.
 * @param string $objectname Name of object to check.
 * @param string $dml_sql Required SQL to create the object if it does not exist.
 * @return bool TRUE if Object exists else false.
 */
function CheckForDBobject(&$dbh, $objectname, $dml_sql = '')
  {
  $result = $dbh->Query("SELECT COUNT(*) AS CNT FROM USER_OBJECTS WHERE OBJECT_NAME = :obj", OCI_ASSOC, 0, $objectname);
  if(intval($result['CNT']) > 0)
    {
    return(true);
    }
  /* If no sql to create object is supplied we return false as object does not exist. */
  if($dml_sql == '')
    {
    return(false);
    }
  /* If $dml_sql != '' we try to create the object in question, and if this does not work we return false. */
  $result = $dbh->Query($dml_sql,OCI_ASSOC, 1);
  if($result)
    {
    $d = WhichBR();
    $error = $dbh->GetErrorText();
    printf("OCI ERROR: %s-%s%s",$result,$error,$d['LF']);
    return(false);
    }
  /* All is okay return true now. */
  return(true);
  }
?>
