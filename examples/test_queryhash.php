<?php
/**
 * Tests QueryHash class method.
 * This script is used during development of the class itself.
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQLi
 * @subpackage Examples
 * @version 0.10 (01-Jan-2009)
 * $Id: test_queryhash.php,v 1.1 2009/01/01 11:47:50 siegel Exp $
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 * @filesource
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

$params['s']


DBFooter($d['LF'],$db);

echo($d['LF']);
exit;
?>
