<?php
/**
 * Database Class provides access to MySQL with the new MySQLi (improved) extension.
 * All functions are nearly the same as on the MySQL class, except that additional methods
 * are included to support bind variables.
 * See docs/ for a complete overview of all methods.
 * Requires dbdefs.inc.php for global access data (user,pw,host,port,dbname,appname).
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @package db_MySQLi
 * @version 0.10 (24-Aug-2008)
 * $Id: mysqlidb_class.php,v 1.6 2009/01/01 11:47:48 siegel Exp $
 * @see dbdefs.inc.php
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 * @filesource
 */
/**
 * DEBUG: No Debug Info
 */
define('DBOF_DEBUGOFF'    , (1 << 0));
/**
 * DEBUG: Debug on-screen
 */
define('DBOF_DEBUGSCREEN' , (1 << 1));
/**
 * DEBUG: Debug to error_log()
 */
define('DBOF_DEBUGFILE'   , (1 << 2));

/**#@+
 * Connect and error handling.
 * If DBOF_SHOW_NO_ERRORS is set and an error occures,
 * the class still reports an an error of course but the error
 * shown is reduced in informational details to avoid showing
 * sensible informations in a productive environment.
 * If DBOF_SHOW_ALL_ERRORS is set the maximum possible details are shown.
 * Set RETURN_ALL_ERRORS if you want to handle errors yourself, in
 * this case the class returns error codes if something goes wrong.
 */
define('DBOF_SHOW_NO_ERRORS'    , 0);
define('DBOF_SHOW_ALL_ERRORS'   , 1);
define('DBOF_RETURN_ALL_ERRORS' , 2);
/**#@-*/

/**
 * Main class definition.
 * @package db_MySQLi
 */
class db_MySQLi
  {
  /** @var mixed $sock Internal connection handle */
  var $sock;
  /** @var string $host Name of database to connect to */
  var $host;
  /** @var string $user Name of database user used for connection */
  var $user;
  /** @var string $password Password of database user used for connection */
  var $password;
  /** @var string $database Name of schema to be used */
  var $database;
  /** @var integer $port Portnumber to use when connecting to MySQL */
  var $port;
  /** @var integer $querycounter Counts the processed queries against the database */
  var $querycounter;
  /** @var float $querytime Contains the total SQL execution time in microseconds. */
  var $querytime;
  /** @var mixed $stmt Stores active statement handle. */
  var $stmt;
  /** @var string $appname Name of Application that uses this class */
  var $appname;
  /** @var string $classversion Version of this class in format VER.REV */
  var $classversion;
  /** @var string $currentQuery Contains the actual query to be processed. */
  var $currentQuery;
  /** @var integer $showError Flag indicates how the class should interact with errors */
  var $showError;
  /** @var integer $debug Flag indicates debug mode of class */
  var $debug;
  /** @var string The SAPI type of php (used to detect CLI sapi) */
  var $SAPI_type;
  /** @var string Email Address for the administrator of this project */
  var $AdminEmail;
  /** @var integer myErrno Error code of last mysql operation (set in Print_Error()) */
  var $myErrno;
  /** @var string myErrStr Error string of last mysql operation (set in Print_Error()) */
  var $myErrStr;

  /**
   * Constructor of class.
   * The constructor takes default values from dbdefs.inc.php.
   * Please see this file for further informations about the setable options.
   * @param string $extconfig Optional other filename for dbdefs.inc.php, defaults to "dbdefs.inc.php".
   * @see dbdefs.inc.php
   */
  function db_MySQLi($extconfig='')
    {
    if($extconfig == '')
      {
      require_once('dbdefs.inc.php');
      }
    else
      {
      require_once($extconfig);
      }
    $this->classversion = '0.10';
    $this->host         = '';
    $this->user         = '';
    $this->pass         = '';
    $this->database     = '';
    $this->port         = 3306;
    $this->appname      = MYSQLiAPPNAME;
    $this->sock         = 0;
    $this->querycounter = 0;
    $this->querytime    = 0.000;
    $this->debug        = 0;
    $this->myErrno      = 0;
    $this->MyErrStr     = '';
    $this->AdminEmail   = (isset($_SERVER['SERVER_ADMIN'])) ? $_SERVER['SERVER_ADMIN'] : ''; // Defaults to Webadministrator of Server
    $this->SAPI_type    = @php_sapi_name();         // May contain 'cli', in this case disable HTML errors!
    $this->stmt         = NULL;                     // Internal statement handle

    // Check if the mysqli_* functions exists:

    if(function_exists('mysqli_connect')==false)
      {
      die('ERROR: mysqli_connect() function does not exist in your PHP installation - class is not useable !');
      }

    // Now check if important defines are correctly set in dbdefs.inc.php

    if(!defined('MYSQLiAPPNAME'))
      {
      $this->setErrorHandling(DBOF_SHOW_ALL_ERRORS);
      $this->Print_Error('dbdefs.inc.php misses "MYSQLiAPPNAME" define! Please check configuration!');
      }
    if(defined('DB_ERRORMODE'))                       // You can set a default behavour for error handling in dbdefs.inc.php
      {
      $this->setErrorHandling(DB_ERRORMODE);
      }
    else
      {
      $this->setErrorHandling(DBOF_SHOW_NO_ERRORS);   // Default is not to show too much informations
      }

    if(defined('MYSQLiDB_ADMINEMAIL'))
      {
      $this->AdminEmail = MYSQLiDB_ADMINEMAIL;         // If set use this address instead of default webmaster
      }
    }

  /**
   * Performs the connection to MySQL.
   * If anything goes wrong calls Print_Error().
   * You should set the defaults for your connection by setting user,pass,host,port and database in dbdefs.inc.php
   * and leave connect() parameters empty.
   * If there is an active connection already stored internally this value is returned and no new connection is made.
   * @param string $user Username used to connect to DB.
   * @param string $pass Password to use for given username.
   * @param string $host Hostname of database to connect to.
   * @param string $db Schema to use on MySQL DB Server.
   * @param integer $port TCP port to use for connection. Defaults to 3306.
   * @return mixed Either the DB connection handle or NULL in case of an error.
   * @see dbdefs.inc.php
   * @see mysqli_connect
   */
  function Connect($user='',$pass='',$host='',$db='',$port = 0)
    {
    if($this->sock)
      {
      return($this->sock);
      }
    if($user!='')
      {
      $this->user = $user;
      }
    else
      {
      $this->user = MYSQLiDB_USER;
      }
    if($pass!='')
      {
      $this->pass = $pass;
      }
    else
      {
      $this->pass = MYSQLiDB_PASS;
      }
    if($host!='')
      {
      $this->host = $host;
      }
    else
      {
      $this->host = MYSQLiDB_HOST;
      }
    if($db!='')
      {
      $this->database = $db;
      }
    else
      {
      $this->database = MYSQLiDB_DATABASE;
      }
    if(!$port)
      {
      if(defined('MYSQLiDB_PORT'))
        {
        $this->port = MYSQLiDB_PORT;
        }
      else
        {
        $this->port = 3306;
        }
      }
    else
      {
      $this->port = $port;
      }
    $start = $this->getmicrotime();
    $this->printDebug('mysqli_connect('.sprintf("%s/%s@%s",$this->user,$this->pass,$this->host).')');
    $this->sock = @mysqli_connect($this->host,$this->user,$this->pass,$this->database,$this->port);
    if(!$this->sock)
      {
      $this->Print_Error('Connect(): Connection to '.$this->host.':'.$this->port.' failed!');
      return(0);
      }
    $this->querytime+= ($this->getmicrotime() - $start);
    return($this->sock);
    }

  /**
   * Disconnects from MySQL.
   * You may optionally pass an external link identifier.
   * @param mixed $other_sock Optionally your own connection handle to close, else internal will be used.
   * @see mysqli_close
   */
  function Disconnect($other_sock=-1)
    {
    if($other_sock!=-1)
      {
      @mysqli_close($other_sock);
      }
    else
      {
      if($this->sock)
        {
        @mysqli_close($this->sock);
        $this->sock = 0;
        }
      }
    $this->currentQuery = '';
    }

  /**
   * Prints out MySQL Error in own <div> container and exits.
   * Please note that this function does not return as long as you have not set DBOF_RETURN_ALL_ERRORS!
   * @param string $ustr User-defined Error string to show
   * @param mixed $var2dump Optionally a variable to print out with print_r()
   * @see print_r
   * @see mysqli_errno
   * @see mysqli_error
   */
  function Print_Error($ustr="",$var2dump="")
    {
    if(!$this->sock)
      {
      $errnum   = @mysqli_connect_errno();
      $errstr   = @mysqli_connect_error();
      }
    else
      {
      $errnum   = @mysqli_errno($this->sock);
      $errstr   = @mysqli_error($this->sock);
      }
    $filename = basename($_SERVER['SCRIPT_FILENAME']);
    $this->myErrno = $errnum;
    $this->myErrStr= $errstr;
    if($errstr=='')
      {
      $errstr = 'N/A';
      }
    if($errnum=='')
      {
      $errnum = -1;
      }
    @error_log($this->appname.': Error in '.$filename.': '.$ustr.' ('.chop(strip_tags($errstr)).')',0);
    if($this->showError == DBOF_RETURN_ALL_ERRORS)
      {
      return($errnum);      // Return the error number
      }
    $this->SendMailOnError($errnum,$errstr,$ustr);
    $crlf = "\n";
    $space= ' ';
    if($this->SAPI_type != 'cli')
      {
      $crlf = "<br>\n";
      $space= '&nbsp;';
      echo("<br>\n<div align=\"left\" style=\"background-color: #EEEEEE; color:#000000;border: 1px solid #000000;\">\n");
      echo("<font color=\"red\" face=\"Arial,Sans-Serif\"><b>".$this->appname.": Database Error occured!</b></font><br>\n<br>\n<code>\n");
      }
    else
      {
      echo("\n!!! ".$this->appname.": Database Error occured !!!\n\n");
      }
    echo($space."CODE: ".$errnum.$crlf);
    echo($space."DESC: ".$errstr.$crlf);
    echo($space."FILE: ".$filename.$crlf);
    if($this->showError == DBOF_SHOW_ALL_ERRORS)
      {
      if($this->currentQuery!="")
        {
        echo("QUERY: ".$this->currentQuery.$crlf);
        }
      echo($space."QCNT: ".$this->querycounter.$crlf);
      if($ustr!='')
        {
        echo($space."INFO: ".$ustr.$crlf);
        }
      if($var2dump!='')
        {
        echo($space.'DUMP: ');
        if(is_array($var2dump))
          {
          if($this->SAPI_type != 'cli')
            {
            echo('<pre>');
            print_r($var2dump);
            echo("</pre>\n");
            }
          else
            {
            print_r($var2dump);
            }
          }
        else
          {
          echo($var2dump.$crlf);
          }
        }
      }
    if($this->SAPI_type != 'cli')
      {
      echo("<br>\nPlease inform <a href=\"mailto:".$this->AdminEmail."\">".$this->AdminEmail."</a> about this problem.");
      echo("</code>\n");
      echo("</div>\n");
      echo("<div align=\"right\"><small>PHP V".phpversion()." / MySQLi Class v".$this->classversion."</small></div>\n");
      }
    else
      {
      echo("\nPlease inform ".$this->AdminEmail." about this problem.\n\nRunning on PHP V".phpversion()." / MySQLi Class v".$this->classversion."\n");
      }
    $this->Disconnect();
    exit;
    }

  /**
   * Performs a single-row query and returns result (if one exists).
   * Resflag can be MYSQL_NUM or MYSQL_ASSOC
   * depending on what kind of array you want to be returned.
   * @param string $querystring The SQL query to send to database.
   * @param integer $resflag Decides how the result should be returned:
   *  - MYSQLI_ASSOC = Data is returned as assoziative array
   *  - MYSQLI_NUM   = Data is returned as numbered array
   *  - MYSQLI_BOTH  = Data is returned as both numbered and associative array.
   * @param integer $no_exit Decides how the class should react on errors.
   *                         If you set this to 1 the class won't automatically exit
   *                         on an error but instead return the mysqli_errno value.
   *                         Default of 0 means that the class calls Print_Error()
   *                         and exists.
   * @return mixed Either an array as result of the query or an error code or TRUE.
   * @see Print_Error
   * @see mysqli_query
   */
  function Query($querystring, $resflag = MYSQLI_ASSOC, $no_exit = 0)
    {
    if(!$this->sock)
      {
      return($this->Print_Error('Query(): No active Connection!',$querystring));
      }
    if($querystring == '')
      {
      return($this->Print_Error('Query(): No querystring was supplied!'));
      }
    $this->PrintDebug($querystring);
    $this->currentQuery = $querystring;
    if($this->showError == DBOF_RETURN_ALL_ERRORS)
      {
      $no_exit = 1;  // Override if user has set master define
      }
    $start = $this->getmicrotime();
    $rc = mysqli_query($this->sock, $querystring);
    $this->querycounter++;
    // Now check if an result set is returned or a boolean, in this case we can safely return here.
    if(is_bool($rc) == TRUE)
      {
      $this->querytime+= ($this->getmicrotime() - $start);
      if($rc == FALSE)
        {
        if($no_exit)
          {
          $reterror = @mysqli_errno($this->sock);
          return($reterror);
          }
        else
          {
          return($this->Print_Error("Query('".$querystring."') failed!"));
          }
        }
      return($rc);
      }
    // Result set is returned, fetch 1st row (!) and return as arraytype specified.
    $row = mysqli_fetch_array($rc,$resflag);
    mysqli_free_result($rc);
    $this->querytime+= ($this->getmicrotime() - $start);
    return($row);
    }

  /**
   * Performs a multi-row query and returns result identifier.
   * @param string $querystring The Query to be executed
   * @param integer $no_exit The error indicator flag, can be one of:
   *  - 0 = (Default), In case of an error Print_Error is called and script terminates
   *  - 1 = In case of an error this function returns the error from mysql_errno()
   * @return mixed A resource identifier or an errorcode (if $no_exit = 1)
   * @see mysql_query
   */
  function QueryResult($querystring, $no_exit = 0)
    {
    if(!$this->sock)
      {
      return($this->Print_Error('QueryResult(): No active Connection!',$querystring));
      }
    if($this->debug)
      {
      $this->PrintDebug($querystring);
      }
    $this->currentQuery = $querystring;
    $start = $this->getmicrotime();
    $rc = mysqli_query($this->sock, $querystring);
    $this->querycounter++;
    // Now check if an result set is returned or a boolean, in this case we can safely return here.
    if(is_bool($rc) == TRUE)
      {
      $this->querytime+= ($this->getmicrotime() - $start);
      if($rc == FALSE)
        {
        if($no_exit)
          {
          $reterror = @mysqli_errno($this->sock);
          return($reterror);
          }
        else
          {
          return($this->Print_Error("Query('".$querystring."') failed!"));
          }
        }
      return($rc);
      }
    // Result set is returned, so we return it to the caller and also store it into internal class variable if there is no statement already stored.
    if(is_null($this->stmt))
      {
      $this->stmt = $rc;
      }
    return($rc);
    }

    /**
   * Frees result returned by QueryResult().
   * It is a good programming practise to give back what you have taken, so after processing
   * your Multi-Row query with FetchResult() finally call this function to free the allocated
   * memory.
   *
   * @param mixed $result The resource identifier you want to be freed. If not given the internal statement handle is used.
   * @return mixed The resulting code of mysql_free_result (can be ignored).
   * @see mysql_free_result
   * @see QueryResult
   * @see FetchResult
   */
  function FreeResult($result=NULL)
    {
    $this->currentQuery = '';
    $start = $this->getmicrotime();
    if(is_null($result))
      {
      if(is_null($this->stmt)==false)
        {
        $myres = @mysqli_free_result($this->$stmt);
        }
      $this->stmt = NULL;
      }
    else
      {
      $myres = @mysqli_free_result($result);
      }
    $this->querytime+= ($this->getmicrotime() - $start);
    return($myres);
    }

  /**
   * Sets debug level for debugging of SQL Queries.
   * $state can have these values:
   * - DBOF_DEBUGOFF    = Turn off debugging
   * - DBOF_DEBUGSCREEN = Turn on debugging on screen (every Query will be dumped on screen)
   * - DBOF_DEBUFILE    = Turn on debugging on PHP errorlog
   * You can mix the debug levels by adding the according defines!
   * @param integer $state The DEBUG Level you want to be set
   */
  function SetDebug($state)
    {
    $this->debug = $state;
    }

  /**
   * Returns the current debug setting.
   * @return integer The debug setting (bitmask)
   * @see SetDebug()
   */
  function GetDebug()
    {
    return($this->debug);
    }

  /**
   * Handles output according to internal debug flag.
   * @param string $msg The Text to be included in the debug message.
   * @see error_log
   */
  function PrintDebug($msg)
    {
    if(!$this->debug)
      {
      return;
      }
    if($this->SAPI_type != 'cli')
      {
      $formatstr = "<div align=\"left\" style=\"background-color:#ffffff; color:#000000;\"><pre>DEBUG: %s</pre></div>\n";
      }
    else
      {
      $formatstr =  "DEBUG: %s\n";
      }
    if($this->debug & DBOF_DEBUGSCREEN)
      {
      @printf($formatstr,$msg);
      }
    if($this->debug & DBOF_DEBUGFILE)
      {
      @error_log('DEBUG: '.$msg,0);
      }
    }

  /**
   * Returns version of this class.
   * @return string The version of this class.
   */
  function GetClassVersion()
    {
    return($this->classversion);
    }

  /**
   * Returns MySQL Server Version.
   * Opens an own connection if no active one exists.
   * @return string MySQL Server Version
   */
  function Version()
    {
    $weopen = 0;
    if(!$this->sock)
      {
      $this->Connect();
      $weopen = 1;
      }
    $ver = @mysqli_get_server_info($this->sock);
    if($weopen)
      {
      $this->Disconnect();
      }
    return($ver);
    }

  /**
   * Returns amount of queries executed by this class.
   * @return integer Query counter
   */
  function GetQueryCount()
    {
    return($this->querycounter);
    }

  /**
   * Returns amount of time spent on queries executed by this class.
   * @return float Time in seconds.msecs spent in executin MySQL code.
   */
  function GetQueryTime()
    {
    return($this->querytime);
    }

  /**
   * Returns microtime in format s.mmmmm.
   * Used to measure SQL execution time.
   * @return float the current time in microseconds.
   */
  function getmicrotime()
    {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
    }

  /**
   * Allows to set the class behavour in case of an error.
   *
   * - DBOF_SHOW_NO_ERRORS    => Show no security-relevant informations
   * - DBOF_SHOW_ALL_ERRORS   => Show all errors (useful for develop)
   * - DBOF_RETURN_ALL_ERRORS => No error/autoexit, just return the mysql error code.
   * @param integer $val The Error Handling mode you wish to use.
   */
  function setErrorHandling($val)
    {
    $this->showError = $val;
    }

  /**
   * Returns current connection handle.
   * Returns either the internal connection socket or -1 if no active handle exists.
   * Useful if you want to work with mysqli* functions in parallel to this class.
   * @return mixed Internal socket value
   */
  function GetConnectionHandle()
    {
    return($this->sock);
    }

  /**
   * Allows to set internal socket to external value.
   * @param mixed New socket handle to set (as returned from mysqli_connect())
   * @see mysqli_connect
   */
  function SetConnectionHandle($extsock)
    {
    $this->sock = $extsock;
    }

  /**
   * Send error email if programmer has defined a valid email address and
   * enabled it with the define MYSQLDB_SENTMAILONERROR.
   * @param integer $merrno MySQL errno number
   * @param string $merrstr MySQL error description
   * @param string $uerrstr User-supplied error description
   * @see dbdefs.inc.php
   * @see mail
   */
  function SendMailOnError($merrno,$merrstr,$uerrstr)
    {
    if(!defined('MYSQLiDB_SENTMAILONERROR') || MYSQLiDB_SENTMAILONERROR == 0 || $this->AdminEmail == '')
      {
      return;
      }
    $server  = $_SERVER['SERVER_NAME']." (".$_SERVER['SERVER_ADDR'].")";
    if($server == ' ()' || $server == '')
      {
      $server = 'n/a';
      }
    $uagent  = (isset($_SERVER['HTTP_USER_AGENT'])) ? strip_tags($_SERVER['HTTP_USER_AGENT']) : '';
    if($uagent == '')
      {
      $uagent = 'n/a';
      }
    $clientip = $_SERVER['REMOTE_ADDR']." (".@gethostbyaddr($_SERVER['REMOTE_ADDR']).")";
    if($clientip == ' ()' || $clientip == '')
      {
      $clientip = 'n/a';
      }
    $message = "MySQLiDB Class v".$this->classversion.": Error occured on ".date('r')." !!!\n\n";
    $message.= "      APPLICATION: ".$this->appname."\n";
    $message.= "  AFFECTED SERVER: ".$server."\n";
    $message.= "       USER AGENT: ".$uagent."\n";
    $message.= "       PHP SCRIPT: ".$_SERVER['SCRIPT_FILENAME']."\n";
    $message.= "   REMOTE IP ADDR: ".$clientip."\n";
    $message.= "    DATABASE DATA: ".$this->user." @ ".$this->host."\n";
    $message.= "SQL ERROR MESSAGE: ".$merrstr."\n";
    $message.= "   SQL ERROR CODE: ".$merrno."\n";
    $message.= "    QUERY COUNTER: ".$this->querycounter."\n";
    $message.= "         INFOTEXT: ".$uerrstr."\n";
    if($this->currentQuery != '')
      {
      $message.= "        SQL QUERY:\n";
      $message.= "------------------------------------------------------------------------------------\n";
      $message.= $this->currentQuery."\n";
      }
    $message.= "------------------------------------------------------------------------------------\n";
    if(defined('MYSQLiDB_MAIL_EXTRAARGS') && MYSQLiDB_MAIL_EXTRAARGS != '')
      {
      @mail($this->AdminEmail,'MySQLiDB Class v'.$this->classversion.' ERROR #'.$merrno.' OCCURED!',$message,MYSQLiDB_MAIL_EXTRAARGS);
      }
    else
      {
      @mail($this->AdminEmail,'MySQLiDB Class v'.$this->classversion.' ERROR #'.$merrno.' OCCURED!',$message);
      }
    }

  /**
   * Sets autocommit flag.
   * @param bool $state TRUE = Autocommit enabled, FALSE = autocommit disabled.
   * @param resource $extsock Optional an external mysqli connect resource, default is internally stored resource.
   * @return bool The returnvalue of mysqli_autocommit. FALSE in any case if no open link is found.
   * @see mysqli_autocommit
   */
  function SetAutoCommit($state,$extsock = -1)
    {
    if($extsock == -1)
      {
      $s = $this->sock;
      }
    else
      {
      $s = $extsock;
      }
    if(!$s)
      {
      return(FALSE);
      }
    return(@mysqli_autocommit($s,$state));
    }

  /**
   * Retrieves autocommit flag.
   * Note that this only works for connected (!) databases, as we have to read the state from the server!
   * @param resource $extsock Optional an external mysqli connect resource. Default is internally stored resource.
   * @return bool TRUE = autocommit is enabled, FALSE = autocommit is disabled.
   * @see mysqli_autocommit
   */
  function GetAutoCommit($extsock = -1)
    {
    $oldsock = -1;
    if($extsock != -1)
      {
      $oldsock = $this->sock;   // Safe current value
      $this->sock = $extsock;
      }
    $data = $this->Query('SELECT @@autocommit',MYSQLI_NUM);
    if($oldsock != -1)
      {
      $this->sock = $oldsock;   // Restore old value
      }
    return($data[0]);
    }

  /**
   * Commits the current transaction.
   * @param resource $extsock Optional an external mysqli connect resource. Default is internally stored resource.
   * @return bool The return value from mysqli_commit()
   * @see mysqli_commit
   */
  function Commit($extsock = -1)
    {
    if($extsock == -1)
      {
      $s = $this->sock;
      }
    else
      {
      $s = $extsock;
      }
    if(!$s)
      {
      return(FALSE);
      }
    return(@mysqli_commit($s));
    }

  /**
   * Rollback current transaction.
   * @param resource $extsock Optional an external mysqli connect resource. Default is internally stored resource.
   * @return bool The return value from mysqli_rollback()
   * @see mysqli_rollback
   */
  function Rollback($extsock = -1)
    {
    if($extsock == -1)
      {
      $s = $this->sock;
      }
    else
      {
      $s = $extsock;
      }
    if(!$s)
      {
      return(FALSE);
      }
    return(@mysqli_rollback($s));
    }

  /**
   * Returns last used auto_increment id.
   * @param mixed $extsock Optionally an external MySQL socket to use. If not given the internal socket is used.
   * @return integer The last automatic insert id that was assigned by the MySQL server.
   * @see mysql_insert_id
   */
  function LastInsertId($extsock=-1)
    {
    if($extsock==-1)
      {
      return(@mysqli_insert_id($this->sock));
      }
    else
      {
      return(@mysqli_insert_id($extsock));
      }
    }

  /**
   * Returns count of affected rows by last DML operation.
   * @param mixed $extsock Optionally an external MySQLi socket to use. If not given the internal socket is used.
   * @return integer The number of affected rows by previous DML operation.
   * @see mysql_affected_rows
   */
  function AffectedRows($extsock=-1)
    {
    if($extsock==-1)
      {
      return(@mysqli_affected_rows($this->sock));
      }
    else
      {
      return(@mysqli_affected_rows($extsock));
      }
    }

  /**
   * Retrieve last mysql error number.
   * @param mixed $other_sock Optionally your own connection handle to check, else internal will be used.
   * @return integer The MySQL error number of the last operation
   * @see mysqli_errno
   */
  function GetErrno($other_sock = -1)
    {
    if( $other_sock == -1 )
      {
      if(!$this->sock)
        {
        return($this->myErrno);
        }
      else
        {
        return(@mysqli_errno($this->sock));
        }
      }
    else
      {
      if(!$other_sock)
        {
        return($this->myErrno);
        }
      else
        {
        return(@mysqli_errno($other_sock));
        }
      }
    }

  /**
   * Retrieve last mysql error description.
   * @param mixed $other_sock Optionally your own connection handle to check, else internal will be used.
   * @return string The MySQL error description of the last operation
   * @see mysqli_error
   */
  function GetErrorText($other_sock = -1)
    {
    if( $other_sock == -1 )
      {
      if(!$this->sock)
        {
        return($this->myErrStr);
        }
      else
        {
        return(@mysqli_error($this->sock));
        }
      }
    else
      {
      if(!$other_sock)
        {
        return($this->myErrStr);
        }
      else
        {
        return(@mysqli_error($other_sock));
        }
      }
    }

  /**
   * Converts a MySQL default Datestring (YYYY-MM-DD HH:MI:SS) into a strftime() compatible format.
   * You can use all format tags that strftime() supports, this function simply converts the mysql
   * date string into a timestamp which is then passed to strftime together with your supplied
   * format. The converted datestring is then returned.
   * Please do not use this as default date converter, always use DATE_FORMAT() inside a query
   * whenever possible as this is much faster than using this function! Only if you cannot use
   * the MySQL SQL Date converting functions consider using this function.
   * @param string $mysqldate The MySQL default datestring in format YYYY-MM-DD HH:MI:SS
   * @param string $fmtstring A strftime() compatible format string.
   * @return string The converted date string.
   * @see strftime
   * @see mktime
   */
  function ConvertMySQLDate($mysqldate,$fmtstring)
    {
    $dt = explode(' ',$mysqldate);  // Split in date/time
    $dp = explode('-',$dt[0]);                                  // Split date
    $tp = explode(':',$dt[1]);                                  // Split time
    $ts = mktime(intval($tp[0]),intval($tp[1]),intval($tp[2]),intval($dp[1]),intval($dp[2]),intval($dp[0]));    // Create time stamp
    if($fmtstring=='')
      {
      $fmtstring = '%c';
      }
    return(strftime($fmtstring,$ts));
    }

  /**
   * Escapes a given string with the 'mysql_real_escape_string' method.
   * Always use this function to avoid SQL injections when adding dynamic data to MySQL!
   * This function also handles the settings for magic_quotes_gpc/magic_quotes_sybase, if
   * these settings are enabled this function uses stripslashes() first.
   * @param string $str The string to escape.
   * @return string The escaped string.
   */
  function EscapeString($str)
    {
    $data = $str;
    if(get_magic_quotes_gpc())
      {
      $data = stripslashes($data);
      }
    $link = get_resource_type($this->sock);
    if($this->sock && substr($link,0,5) =='mysql')
      {
      return(mysqli_real_escape_string($data,$this->sock));
      }
    else
      {
      $link = mysqli_init();
      return(mysqli_escape_string($data,$link));
      }
    }

  /**
   * Method to set the time_names setting of the MySQL Server.
   * Pass it a valid locale string to change the locale setting of MySQL.
   * Note that this is supported only since 5.0.25 of MySQL!
   * @param string $locale A locale string for the language you want to set, i.e. 'de_DE'.
   * @return integer 0 If an error occures or 1 if change was successful.
   */
  function set_TimeNames($locale)
    {
    $rc = $this->Query("SET lc_time_names='".$locale."'",MYSQL_NUM,1);
    if($rc != 1)
      {
      $this->Print_Error('set_TimeNames(): Error while trying to set lc_time_names to "'.$locale.'" !!!');
      return(0);
      }
    $this->locale = $locale;
    return(1);
    }

  /**
   * Method to return the current MySQL setting for the lc_time_names variable.
   * @return string The current setting for the lc_time_names variable.
   */
  function get_TimeNames()
    {
    $data = $this->Query("SELECT @@lc_time_names",MYSQL_NUM,1);
    if(is_array($data)==false)
      {
      $this->Print_Error('get_TimeNames(): Error while trying to retrieve the lc_time_names variable !!!');
      return(0);
      }
    return($data[0]);
    }

  /**
   * QueryHash() performs a single-row query with bind variable support.
   * All bind vars must be passed as an associative array. The keys of the array are the bind variable names,
   * while the values of the associative array are the actual values to send to the database server.
   * Resflag can be either MYSQLI_NUM or MYSQLI_ASSOC depending on what kind of array you want to be returned.
   * @param string $querystring The SQL query to send to database.
   * @param integer $resflag Decides how the result should be returned:
   *  - MYSQLI_ASSOC = Data is returned as assoziative array
   *  - MYSQLI_NUM   = Data is returned as numbered array
   *  - MYSQLI_BOTH  = Data is returned as both numbered and associative array.
   * @param integer $no_exit Decides how the class should react on errors.
   *                         If you set this to 1 the class won't automatically exit
   *                         on an error but instead return the mysqli_errno value.
   *                         Default of 0 means that the class calls Print_Error()
   *                         and exists.
   * @param array &$bindvarhash The bind vars as associative array (keys = bindvar names, values = bindvar values)
   * @return mixed Either an array as result of the query or an error code or TRUE.
   */
  function QueryHash($querystring, $resflag = MYSQLI_ASSOC, $no_exit = 0, &$bindvarhash)
    {
    if(!$this->sock)
      {
      return($this->Print_Error('QueryHash(): No active Connection!',$querystring));
      }
    if($querystring == '')
      {
      return($this->Print_Error('QueryHash(): No querystring was supplied!'));
      }
    $this->PrintDebug($querystring);
    $this->currentQuery = $querystring;
    if($this->showError == DBOF_RETURN_ALL_ERRORS)
      {
      $no_exit = 1;  // Override if user has set master define
      }
    $start = $this->getmicrotime();
    if(is_array($bindvarhash))
      {
      // Prepare the query and store result:
      $stmt = mysqli_prepare($this->sock, $querystring);
      }
    }
  }
?>
