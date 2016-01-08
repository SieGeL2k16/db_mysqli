<?php
/**
 * Database Class provides access to MySQL with the new MySQLi (improved) extension.
 * All functions are nearly the same as on the MySQL class, except that additional methods
 * are included to support bind variables.
 * See docs/ for a complete overview of all methods.
 * Requires dbdefs.inc.php for global access data (user,pw,host,port,dbname,appname).
 * @author Sascha 'SieGeL' Pfalz <php@saschapfalz.de>
 * @version 0.2.0 (07-Dec-2015)
 * @license http://opensource.org/licenses/bsd-license.php BSD License
 */

/**
 * Main class definition.
 * @package db_MySQLi
 */
class db_MySQLi
  {
 /** Class version. */
  private $classversion = '0.2.0';

  /** Internal connection handle. */
  protected $sock = NULL;

  /** Name of database to connect to */
  protected $host = '';

  /** Name of database user used for connection */
  protected $user = '';

  /** Password of database user used for connection */
  protected $password = '';

  /** Name of schema to be used */
  protected $database = '';

  /** Portnumber to use when connecting to MySQL */
  protected $port = 3306;

  /** Name of Application that uses this class */
  protected $appname = '';

  /** Counts processed queries against the database */
  private $querycounter = 0;

  /** Contains the total SQL execution time in microseconds. */
  private $querytime = 0.000;

  /** Debugstate, default is OFF. */
  protected $debug = db_MySQLi::DBOF_DEBUG_OFF;

  /** Stores active statement handle. */
  private $stmt = NULL;

  /** Error code of last mysql operation (set in Print_Error()) */
  private $myErrno = 0;

  /** Error string of last mysql operation (set in Print_Error()) */
  private $myErrStr = '';

  /** Email Address for the administrator of this project */
  private $AdminEmail = '';

  /** The SAPI type of php (used to detect CLI sapi) */
  private $SAPI_type = '';

  /** Contains the actual query to be processed. */
  private $currentQuery = '';

  /** Flag indicates how the class should interact with errors */
  private $showError = db_MySQLi::DBOF_SHOW_NO_ERRORS;

	/** Flag indicates if persistant connections should be used */
	private $usePConnect = FALSE;

	/** Character set to use. */
	private $characterset = '';

	/** Character set to use. */
	private $locale = '';

	/** Number of rows from result set */
	private $num_rows = 0;

  /** DEBUG: No Debug Info */
  const DBOF_DEBUG_OFF           = 1;
  /** DEBUG: Debug to screen */
  const DBOF_DEBUG_SCREEN        = 2;
  /** DEBUG: Debug to error.log */
  const DBOF_DEBUG_FILE          = 4;

  /** Displays errors with reduced informations and terminates execution. */
	const	DBOF_SHOW_NO_ERRORS			= 0;
  /** Displays errors with all available informations and terminates execution. */
	const DBOF_SHOW_ALL_ERRORS		= 1;
  /** Returns the error code back to the callee. You are responsible for error management. */
	const DBOF_RETURN_ALL_ERRORS	= 2;

  /** DescTable(): Column name. */
	const DBOF_MYSQL_COLNAME  = 0;
  /** DescTable(): Column type. */
	const DBOF_MYSQL_COLTYPE  = 1;
  /** DescTable(): Column size. */
	const DBOF_MYSQL_COLSIZE  = 2;
  /** DescTable(): Column flags. */
	const DBOF_MYSQL_COLFLAGS = 3;

  /**
   * Type definitions for QueryHash() / QueryResultHash()
   * @see http://php.net/manual/en/mysqli-stmt.bind-param.php
   * @since 0.2.0
   */
  const DBOF_TYPE_INT       = 'i';
  const DBOF_TYPE_DOUBLE    = 'd';
  const DBOF_TYPE_STRING    = 's';
  const DBOF_TYPE_BLOB      = 'b';

  /** Define to detect prepare() errors */
  const PREPARE_ERROR = 'Prepare() failure - Check SQL!';

  /**
   * Constructor of class.
   * The constructor takes default values from dbdefs.inc.php.
   * Please see this file for further informations about the setable options.
   * @param string $extconfig Optional other filename for dbdefs.inc.php, defaults to "dbdefs.inc.php".
   */
  function __construct($extconfig='')
    {
    // Check if the mysqli_* functions exists:
    if(function_exists('mysqli_connect')==false)
      {
      throw new Exception('ERROR: mysqli_connect() function does not exist in your PHP installation - class is not useable !');
      }
    if($extconfig == '')
      {
      require_once('dbdefs.inc.php');
      }
    else
      {
      require_once($extconfig);
      }
    // Now check if important defines are correctly set in dbdefs.inc.php
    if(!defined('MYSQLAPPNAME'))
      {
      $this->setErrorHandling(db_MySQLi::DBOF_SHOW_ALL_ERRORS);
      $this->Print_Error('dbdefs.inc.php: "MYSQLAPPNAME" IS MISSING! Please check Class installation!');
      }
		$this->appname = MYSQLAPPNAME;
    if(defined('DB_ERRORMODE'))                       // You can set a default behavour for error handling in debdefs.inc.php
      {
      $this->setErrorHandling(DB_ERRORMODE);
      }
    else
      {
      $this->setErrorHandling(db_MySQLi::DBOF_SHOW_NO_ERRORS);   // Default is not to show too much informations
      }
    if(defined('MYSQLDB_ADMINEMAIL'))
      {
      $this->AdminEmail = MYSQLDB_ADMINEMAIL;         // If set use this address instead of default webmaster
      }
		else
			{
    	$this->AdminEmail	= (isset($_SERVER['SERVER_ADMIN'])) ? $_SERVER['SERVER_ADMIN'] : '';
			}
    // Check if user requested persistant connection per default in dbdefs.inc.php
    if(defined('MYSQLDB_USE_PCONNECT') && MYSQLDB_USE_PCONNECT != 0)
      {
      $this->usePConnect = TRUE;
      }
    // Check if user wants to have set a specific character set with 'SET NAMES <cset>'
    if(defined('MYSQLDB_CHARACTERSET') && MYSQLDB_CHARACTERSET != '')
      {
      $this->characterset = MYSQLDB_CHARACTERSET;
      }
    // Check if user wants to have set a specific locale with 'SET lc_time_names = <locale>;'
    if(defined('MYSQLDB_TIME_NAMES') && MYSQLDB_TIME_NAMES != '')
      {
      $this->locale = MYSQLDB_TIME_NAMES;
      }
		// Check if compatible mode should be enabled:
		if(defined('MYSQLDB_COMPATIBLE_MODE') && MYSQLDB_COMPATIBLE_MODE == TRUE)
			{
			$this->EnableCompatibleMode();
			}
    $this->SAPI_type  = @php_sapi_name();
    } // __construct()

	/**
	 * Compatibility method to simulate old db_mysql class behavour.
	 * You can enable this by setting the define MYSQLDB_COMPATIBLE_MODE to TRUE (defaults to FALSE).
	 */
	public function SetCompatMode()
		{
		if(!defined('DBOF_DEBUGOFF'))
			{
			define('DBOF_DEBUGOFF', db_MySQLi::DBOF_DEBUG_OFF);
			}
		if(!defined('DBOF_DEBUGSCREEN'))
			{
			define('DBOF_DEBUGSCREEN', db_MySQLi::DBOF_DEBUG_SCREEN);
			}
		if(!defined('DBOF_DEBUGFILE'))
			{
			define('DBOF_DEBUGFILE', db_MySQLi::DBOF_DEBUG_FILE);
			}
		if(!defined('DBOF_SHOW_NO_ERRORS'))
			{
			define('DBOF_SHOW_NO_ERRORS', db_MySQLi::DBOF_SHOW_NO_ERRORS);
			}
		if(!defined('DBOF_SHOW_ALL_ERRORS'))
			{
			define('DBOF_SHOW_ALL_ERRORS', db_MySQLi::DBOF_SHOW_ALL_ERRORS);
			}
		if(!defined('DBOF_RETURN_ALL_ERRORS'))
			{
			define('DBOF_RETURN_ALL_ERRORS', db_MySQLi::DBOF_RETURN_ALL_ERRORS);
			}
    if(!defined('MYSQL_ASSOC'))
      {
      define('MYSQL_ASSOC', MYSQLI_ASSOC);
      }
    if(!defined('MYSQL_NUM'))
      {
      define('MYSQL_NUM', MYSQLI_NUM);
      }
    if(!defined('MYSQL_BOTH'))
      {
      define('MYSQL_BOTH', MYSQLI_BOTH);
      }
		} // EnableCompatibleMode()

  /**
   * Performs the connection to MySQL.
   * If anything goes wrong calls Print_Error().
   * You should set the defaults for your connection by setting user,pass,host,port and database in dbdefs.inc.php
   * and leave connect() parameters empty.
   * If there is an active connection already stored internally this value is returned and no new connection is made.
   * If MYSQLDB_CHARACTERSET define is set a "SET NAMES '<charset>'; is used straight after connecting.
     * @param string $user Username used to connect to DB.
   * @param string $pass Password to use for given username.
   * @param string $host Hostname of database to connect to.
   * @param string $db Schema to use on MySQL DB Server.
   * @param integer $port TCP port to use for connection. Defaults to 3306.
   * @return mixed Either the DB connection handle or NULL in case of an error.
   */
  public function Connect($user='',$pass='',$host='',$db='',$port = 0)
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
      $this->user = MYSQLDB_USER;
      }
    if($pass!='')
      {
      $this->pass = $pass;
      }
    else
      {
      $this->pass = MYSQLDB_PASS;
      }
    if($host!='')
      {
      $this->host = $host;
      }
    else
      {
      $this->host = MYSQLDB_HOST;
      }
    if($db!='')
      {
      $this->database = $db;
      }
    else
      {
      $this->database = MYSQLDB_DATABASE;
      }
    if(!$port)
      {
      if(defined('MYSQLDB_PORT'))
        {
        $this->port = MYSQLDB_PORT;
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
		if($this->usePConnect == TRUE)
			{
			$db_host = sprintf("p:%s",$this->host);
			}
		else
			{
			$db_host = $this->host;
			}
    $start = $this->getmicrotime();
    $this->printDebug('mysqli_connect('.sprintf("%s/%s@%s",$this->user,$this->pass,$db_host).')');
    $this->sock = @mysqli_connect($db_host,$this->user,$this->pass,$this->database,$this->port);
    if(!$this->sock)
      {
      $this->Print_Error('Connect(): Connection to '.$this->host.':'.$this->port.' failed!');
      return(0);
      }
    $this->querytime+= ($this->getmicrotime() - $start);
    if($this->characterset != '')
      {
      $rc = @mysqli_set_charset($this->sock,$this->characterset);
      if($rc === FALSE)
        {
        $this->Print_Error('Connect(): Error while trying to set character set "'.$this->characterset.'": '.@mysqli_error($this->sock));
        return(0);
        }
      }
    if($this->locale != '')
      {
      $rc = $this->Query("SET lc_time_names='".$this->locale."'",MYSQLI_NUM,1);
      if($rc != 1)
        {
        $this->Print_Error('Connect(): Error while trying to set lc_time_names to "'.$this->locale.'" !!!');
        return(0);
        }
      }
    return($this->sock);
    } // Connect()

  /**
   * Disconnects from MySQL.
   * You may optionally pass an external link identifier.
   * @param mixed $other_sock Optionally your own connection handle to close, else internal will be used.
   */
  public function Disconnect($other_sock=-1)
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
    } // Disconnect()

  /**
   * Prints out MySQL Error in own <div> container and exits.
   * Please note that this function does not return as long as you have not set db_MySQLi::DBOF_RETURN_ALL_ERRORS!
   * @param string $ustr User-defined Error string to show
   * @param mixed $var2dump Optionally a variable to print out with print_r()
 	 * @param integer $exit_on_error If set to default of 1 this function terminates execution of the script by calling exit, else it simply returns.
   */
  private function Print_Error($ustr="",$var2dump="", $exit_on_error = 1)
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
    if($this->showError == db_MySQLi::DBOF_RETURN_ALL_ERRORS)
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
    if($this->showError == db_MySQLi::DBOF_SHOW_ALL_ERRORS)
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
      echo("<br>\n");
      if($this->AdminEmail != '')
        {
        echo("Please inform <a href=\"mailto:".$this->AdminEmail."\">".$this->AdminEmail."</a> about this problem.");
        }
      echo("</code>\n");
      echo("</div>\n");
      echo("<div align=\"right\"><small>PHP ".phpversion()." / db_MySQLi class ".$this->classversion."</small></div>\n");
    	@error_log($this->appname.': Error in '.$filename.': '.$ustr.' ('.chop(strip_tags($errstr)).')',0);
      }
    else
      {
      echo("\n");
      if($this->AdminEmail != '')
        {
        echo("Please inform ".$this->AdminEmail." about this problem.\n");
        }
      echo("\nRunning on PHP ".phpversion()." / db_MySQLi class ".$this->classversion."\n");
      }
    $this->Disconnect();
   	if($exit_on_error)
      {
      exit;
      }
 		} // Print_Error()

  /**
   * Performs a single-row query and returns result (if one exists).
   * Resflag can be MYSQLI_NUM or MYSQLI_ASSOC
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
   */
  public function Query($querystring, $resflag = MYSQLI_ASSOC, $no_exit = 0)
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
    if($this->showError == db_MySQLi::DBOF_RETURN_ALL_ERRORS)
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
    $this->num_rows = @mysqli_num_rows($rc);
    $row = mysqli_fetch_array($rc,$resflag);
    mysqli_free_result($rc);
    $this->querytime+= ($this->getmicrotime() - $start);
    return($row);
    } // Query()


  /**
   * Performs a multi-row query and returns result identifier.
   * @param string $querystring The Query to be executed
   * @param integer $no_exit The error indicator flag, can be one of:
   *  - 0 = (Default), In case of an error Print_Error is called and script terminates
   *  - 1 = In case of an error this function returns the error from mysqli_errno()
   * @return mixed A resource identifier or an errorcode (if $no_exit = 1)
   */
  public function QueryResult($querystring, $no_exit = 0)
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
    $this->num_rows = @mysqli_num_rows($rc);
    if(is_null($this->stmt))
      {
      $this->stmt = $rc;
      }
    return($rc);
    } // QueryResult()

  /**
   * Fetches next row from result handle.
   * Returns either numeric (MYSQLI_NUM) or associative (MYSQLI_ASSOC) array
   * for one data row as pointed to by result var.
   * @param mixed $result The resource identifier as returned by QueryResult()
   * @param integer $resflag How you want the data to be returned:
   *  - MYSQLI_ASSOC = Data is returned as assoziative array
   *  - MYSQLI_NUM   = Data is returned as numbered array
   * @return array One row of the resulting query or NULL if there are no data anymore
   */
	public function FetchResult($result,$resflag = MYSQLI_ASSOC,&$bindparams=null)
    {
    if(!$result)
      {
      return($this->Print_Error('FetchResult(): No valid result handle!'));
      }
    $start = $this->getmicrotime();
    if(!($result instanceof mysqli_stmt))
      {
      $resar = mysqli_fetch_array($result,$resflag);
      $this->querytime+= ($this->getmicrotime() - $start);
      return($resar);
      }
    $rc = mysqli_stmt_fetch($result);   // We fetch here only one row!
    if($rc === TRUE)
      {
      if(is_null($bindparams) === FALSE)
        {
        $row  = array();
        foreach($bindparams as $k=>$v)
          {
          $row[$k] = $v;
          }
        }
      else
        {
        $row = $rc;
        }
      }
    else
      {
      $row = $rc;
      }
    $this->querytime+= ($this->getmicrotime() - $start);
    return($row);
    } // FetchResult()

  /**
   * Frees result returned by QueryResult() and Prepare().
   * It is a good programming practise to give back what you have taken, so after processing
   * your Multi-Row query with FetchResult() finally call this function to free the allocated
   * memory.
   * @param mixed $result The resource identifier you want to be freed. If not given the internal statement handle is used.
   * @return mixed The resulting code of mysqli_free_result (can be ignored).
   */
  public function FreeResult($result=NULL)
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
      if($result instanceof mysqli_stmt)
        {
        $myres = mysqli_stmt_close($result);
        }
      else
        {
        $myres = mysqli_free_result($result);
        }
      }
    $this->querytime+= ($this->getmicrotime() - $start);
    return($myres);
    } // FreeResult()

  /**
   * Sets debug level for debugging of SQL Queries.
   * $state can have these values:
   * - db_MySQLi::DBOF_DEBUG_OFF    = Turn off debugging
   * - db_MySQLi::DBOF_DEBUG_SCREEN = Turn on debugging on screen (every Query will be dumped on screen)
   * - db_MySQLi::DBOF_DEBUG_FILE   = Turn on debugging on PHP errorlog
   * You can mix the debug levels by adding the according defines!
   * @param integer $state The DEBUG Level you want to be set
   */
  public function SetDebug($state)
    {
    $this->debug = $state;
    } // SetDebug()

  /**
   * Returns the current debug setting.
   * @return integer The debug setting (bitmask)
   * @see db_MySQLi::SetDebug()
   */
  public function GetDebug()
    {
    return($this->debug);
    } // GetDebug()

  /**
   * Handles output according to internal debug flag.
   * @param string $msg The Text to be included in the debug message.
   */
  public function PrintDebug($msg)
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
    if($this->debug & db_MySQLi::DBOF_DEBUG_SCREEN)
      {
      @printf($formatstr,$msg);
      }
    if($this->debug & db_MySQLi::DBOF_DEBUG_FILE)
      {
      @error_log('DEBUG: '.$msg,0);
      }
    } // PrintDebug()

  /**
   * Returns version of this class.
   * @return string The version of this class.
   */
  public function GetClassVersion()
    {
    return($this->classversion);
    } // GetClassVersion()

  /**
   * Returns MySQL Server Version.
   * Opens an own connection if no active one exists.
   * @return string MySQL Server Version
   */
  public function Version()
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
    } // Version()

  /**
   * Returns amount of queries executed by this class.
   * @return integer Query counter
   */
  public function GetQueryCount()
    {
    return($this->querycounter);
    } // GetQueryCount()

  /**
   * Returns amount of time spent on queries executed by this class.
   * @return float Time in seconds.msecs spent in executin MySQL code.
   */
  public function GetQueryTime()
    {
    return($this->querytime);
    } // GetQueryTime()

  /**
   * Returns microtime in format s.mmmmm.
   * Used to measure SQL execution time.
   * @return float the current time in microseconds.
   */
  private function getmicrotime()
    {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
    } // getmicrotime()

  /**
   * Allows to set the class error reporting level in case of an error.
   *
   * - db_MySQLi::DBOF_SHOW_NO_ERRORS    => Show no security-relevant informations
   * - db_MySQLi::DBOF_SHOW_ALL_ERRORS   => Show all errors (useful for develop)
   * - db_MySQLi::DBOF_RETURN_ALL_ERRORS => No error/autoexit, just return the mysql error code.
   * @param integer $val The Error Handling mode you wish to use.
   */
  public function SetErrorHandling($val)
    {
    $this->showError = $val;
    } // SetErrorHandling()

  /**
   * Retrieve current error reporting level.
   * @return integer The Error Handling mode currently in use.
   * @since 0.1.1
   */
  public function GetErrorHandling()
    {
    return($this->showError);
    } // GetErrorHandling()

  /**
   * Returns current connection handle.
   * Returns either the internal connection socket or -1 if no active handle exists.
   * Useful if you want to work with mysqli* functions in parallel to this class.
   * @return mixed Internal socket value
   */
  public function GetConnectionHandle()
    {
    return($this->sock);
    } // GetConnectionHandle()

  /**
   * Allows to set internal socket to external value.
   * @param mixed New socket handle to set (as returned from mysqli_connect())
   */
  public function SetConnectionHandle($extsock)
    {
    $this->sock = $extsock;
    } // SetConnectionHandle()

  /**
   * Checks if we are already connected to our database.
   * If not terminates by calling Print_Error().
   * @see Print_Error()
   * @since 0.1.1
   */
  public function CheckSock()
    {
    if(!$this->sock)
      {
      return($this->Print_Error('<b>!!! NOT CONNECTED TO A MYSQL DATABASE !!!</b>'));
      }
    } // CheckSock()

  /**
   * Send error email if programmer has defined a valid email address and enabled it with the define MYSQLDB_SENTMAILONERROR.
   * @param integer $merrno MySQL errno number
   * @param string $merrstr MySQL error description
   * @param string $uerrstr User-supplied error description
   */
  private function SendMailOnError($merrno,$merrstr,$uerrstr)
    {
    if(!defined('MYSQLDB_SENTMAILONERROR') || MYSQLDB_SENTMAILONERROR == 0 || $this->AdminEmail == '')
      {
      return;
      }
    $sname    = (isset($_SERVER['SERVER_NAME']) == TRUE) ? $_SERVER['SERVER_NAME'] : '';
    $saddr    = (isset($_SERVER['SERVER_ADDR']) == TRUE) ? $_SERVER['SERVER_ADDR'] : '';
    $raddr    = (isset($_SERVER['REMOTE_ADDR']) == TRUE) ? $_SERVER['REMOTE_ADDR'] : '';
    if($sname == '')
      {
      if(function_exists('posix_uname') === TRUE)
        {
        $pos = posix_uname();
        $server = $pos['nodename'];
        }
      else
        {
        $server = (isset($_ENV['HOSTNAME']) === TRUE) ? $_ENV['HOSTNAME'] : 'n/a';
        }
      }
    else
      {
      $server  = $sname.' ('.$saddr.')';
      }
    $uagent  = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if($uagent == '')
      {
      $uagent = 'n/a';
      }
    $message = "db_MySQLi class v".$this->classversion.": Error occured on ".date('r')." !!!\n\n";
    $message.= "      APPLICATION: ".$this->appname."\n";
    $message.= "  AFFECTED SERVER: ".$server."\n";
    $message.= "       USER AGENT: ".$uagent."\n";
    $message.= "       PHP SCRIPT: ".$_SERVER['SCRIPT_FILENAME']."\n";
    $message.= "   REMOTE IP ADDR: ".$raddr." (".@gethostbyaddr($raddr).")\n";
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
    if(defined('MYSQLDB_MAIL_EXTRAARGS') && MYSQLDB_MAIL_EXTRAARGS != '')
      {
      @mail($this->AdminEmail,'db_MySQLi class v'.$this->classversion.' ERROR #'.$merrno.' OCCURED!',$message,MYSQLDB_MAIL_EXTRAARGS);
      }
    else
      {
      @mail($this->AdminEmail,'db_MySQLi class v'.$this->classversion.' ERROR #'.$merrno.' OCCURED!',$message);
      }
    } // SendMailOnError()

  /**
   * Sets autocommit flag.
   * @param bool $state TRUE = Autocommit enabled, FALSE = autocommit disabled.
   * @param resource $extsock Optional an external mysqli connect resource, default is internally stored resource.
   * @return bool The returnvalue of mysqli_autocommit. FALSE in any case if no open link is found.
   * @see http://php.net/manual/en/mysqli.autocommit.php
   */
  public function SetAutoCommit($state,$extsock = -1)
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
    } // SetAutoCommit()

  /**
   * Retrieves autocommit flag.
   * Note that this only works for connected (!) databases, as we have to read the state from the server!
   * @param resource $extsock Optional an external mysqli connect resource. Default is internally stored resource.
   * @return bool TRUE = autocommit is enabled, FALSE = autocommit is disabled.
   * @see http://php.net/manual/en/mysqli.autocommit.php
   */
  public function GetAutoCommit($extsock = -1)
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
    } // GetAutoCommit()

  /**
   * Commits the current transaction.
   * @param resource $extsock Optional an external mysqli connect resource. Default is internally stored resource.
   * @return bool The return value from mysqli_commit()
   * @see mysqli_commit
   */
  public function Commit($extsock = -1)
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
    } // Commit()

  /**
   * Rollback current transaction.
   * @param resource $extsock Optional an external mysqli connect resource. Default is internally stored resource.
   * @return bool The return value from mysqli_rollback()
   * @see http://php.net/manual/en/mysqli.rollback.php
   */
  public function Rollback($extsock = -1)
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
    } // Rollback()

  /**
   * Returns last used auto_increment id.
   * @param mixed $extsock Optionally an external MySQL socket to use. If not given the internal socket is used.
   * @return integer The last automatic insert id that was assigned by the MySQL server.
   */
  public function LastInsertId($extsock=-1)
    {
    if($extsock==-1)
      {
      return(@mysqli_insert_id($this->sock));
      }
    else
      {
      return(@mysqli_insert_id($extsock));
      }
    } // LastInsertId()

  /**
   * Returns count of affected rows by last DML operation.
   * @param mixed $extsock Optionally an external MySQLi socket to use. If not given the internal socket is used.
   * @return integer The number of affected rows by previous DML operation.
   */
  public function AffectedRows($extsock=-1)
    {
    if($extsock==-1)
      {
      return(@mysqli_affected_rows($this->sock));
      }
    else
      {
      return(@mysqli_affected_rows($extsock));
      }
    } // AffectedRows()

  /**
   * Retrieve last mysql error number.
   * @param mixed $other_sock Optionally your own connection handle to check, else internal will be used.
   * @return integer The MySQL error number of the last operation
   * @see http://php.net/manual/en/mysqli.errno.php
   */
  public function GetErrno($other_sock = -1)
    {
    if( $other_sock == -1 )
      {
      if(!$this->sock)
        {
        return($this->myErrno);
        }
      else
        {
        $merr = @mysqli_errno($this->sock);
        if($merr == 0)
          {
          $merr = $this->myErrno;
          }
        return($merr);
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
        $merr = @mysqli_errno($other_sock);
        if($merr == 0)
          {
          $merr = $this->myErrno;
          }
        return($merr);
        }
      }
    } // GetErrno()

  /**
   * Retrieve last mysql error description.
   * @param mixed $other_sock Optionally your own connection handle to check, else internal will be used.
   * @return string The MySQL error description of the last operation
   * @see http://php.net/manual/en/mysqli.error.php
   */
  public function GetErrorText($other_sock = -1)
    {
    if( $other_sock == -1 )
      {
      if(!$this->sock)
        {
        return($this->myErrStr);
        }
      else
        {
        $merr = @mysqli_error($this->sock);
        if($merr == "")
          {
          $merr = $this->myErrStr;
          }
        return($merr);
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
        $merr = @mysqli_error($other_sock);
        if($merr == "")
          {
          $merr = $this->myErrStr;
          }
        return($merr);
        }
      }
    } // GetErrorText()

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
   */
  public function ConvertMySQLDate($mysqldate,$fmtstring)
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
    } // ConvertMySQLDate()

  /**
   * Escapes a given string with the 'mysqli_real_escape_string' method.
   * Always use this function to avoid SQL injections when adding dynamic data to MySQL!
   * This function also handles the settings for magic_quotes_gpc/magic_quotes_sybase, if
   * these settings are enabled this function uses stripslashes() first.
   * @param string $str The string to escape.
   * @return string The escaped string.
   */
  public function EscapeString($str)
    {
    $data = $str;
    if(get_magic_quotes_gpc())
      {
      $data = stripslashes($data);
      }
    if($this->sock)
      {
      return(@mysqli_real_escape_string($this->sock,$data));
      }
    else
      {
      $link = @mysqli_init();
      return(@mysqli_escape_string($link,$data));
      }
    } // EscapeString()

  /**
   * Method to set the time_names setting of the MySQL Server.
   * Pass it a valid locale string to change the locale setting of MySQL.
   * Note that this is supported only since 5.0.25 of MySQL!
   * @param string $locale A locale string for the language you want to set, i.e. 'de_DE'.
   * @return integer 0 If an error occures or 1 if change was successful.
   */
  public function Set_TimeNames($locale)
    {
    $rc = $this->Query("SET lc_time_names='".$locale."'",MYSQLI_NUM,1);
    if($rc != 1)
      {
      $this->Print_Error('set_TimeNames(): Error while trying to set lc_time_names to "'.$locale.'" !!!');
      return(0);
      }
    $this->locale = $locale;
    return(1);
    } // Set_TimeNames()

  /**
   * Method to return the current MySQL setting for the lc_time_names variable.
   * @return string The current setting for the lc_time_names variable.
   */
  public function Get_TimeNames()
    {
    $data = $this->Query("SELECT @@lc_time_names",MYSQLI_NUM,1);
    if(is_array($data)==false)
      {
      $this->Print_Error('get_TimeNames(): Error while trying to retrieve the lc_time_names variable !!!');
      return(0);
      }
    return($data[0]);
    } // Get_TimeNames()

  /**
   * Method to set the character set of the current connection.
   * You must specify a valid character set name, else the class will report an error.
   * See http://dev.mysql.com/doc/refman/5.0/en/charset-charsets.html for a list of supported character sets.
   * @param string $charset The charset to set on the MySQL server side.
   * @return integer 1 If all works, else 0 in case of an error.
   * @since 0.1.1
   */
  public function Set_CharSet($charset)
    {
    $rc = mysqli_set_charset($this->sock,$charset);
    if($rc != 1)
      {
      $this->Print_Error('set_Names(): Error while trying to set_charset("'.$charset.')" !!!');
      return(0);
      }
    $this->characterset = $charset;
    return(1);
    } // set_CharSet()

  /**
   * Method to return the current MySQL setting for the character_set variables.
   * Note that MySQL returns a list of settings, so this method returns all character_set related
   * settings as an associative array.
   * @return array The current settings for the character_set variables.
   * @since 0.1.1
   */
  public function Get_CharSet()
    {
    $retarr = array();
    $stmt = $this->QueryResult("SHOW VARIABLES LIKE 'character_set%'",1);
    while($d = $this->FetchResult($stmt,MYSQLI_NUM))
      {
      array_push($retarr,$d);
      }
    $this->FreeResult($stmt);
    return($retarr);
    } // get_CharSet()

	/**
	 * Returns text representation for a given column type.
	 * Taken from http://de1.php.net/manual/en/mysqli-result.fetch-field-direct.php#114882
	 * @param integer $type_id The Id to convert.
	 */
	public static function Type2TXT($type_id)
		{
	  static $types;
	  if (!isset($types))
	    {
	    $types = array();
	    $constants = get_defined_constants(true);
	    foreach ($constants['mysqli'] as $c => $n) if (preg_match('/^MYSQLI_TYPE_(.*)/', $c, $m)) $types[$n] = $m[1];
	    }
    return array_key_exists($type_id, $types)? $types[$type_id] : NULL;
		} // Type2TXT()

	/**
	 * Returns text representation for column flags.
	 * Taken from http://de1.php.net/manual/en/mysqli-result.fetch-field-direct.php#114882
	 * @param integer $flags_num The Flags to convert.
	 */
	public static function Flags2TXT($flags_num)
		{
	  static $flags;
	  if (!isset($flags))
	    {
	    $flags = array();
	    $constants = get_defined_constants(true);
	    foreach ($constants['mysqli'] as $c => $n) if (preg_match('/MYSQLI_(.*)_FLAG$/', $c, $m)) if (!array_key_exists($n, $flags)) $flags[$n] = $m[1];
	    }
    $result = array();
	  foreach ($flags as $n => $t) if ($flags_num & $n) $result[] = $t;
	  return implode(' ', $result);
		} // Flags2TXT()


  /**
   * Returns the description for a given table.
   * Array returned has the following fields:
   * 0 => name of the field
   * 1 => type of field
   * 2 => The fieldsize
   * 3 => Field flags (like auto_increment).
   * @param string $tname Name of table to describe.
   * @return array Numerical array with all required table informations.
   * @since 0.1.1
   */
  public function DescTable($tname)
    {
    $retarray = array();
    $weopen = 0;
    if(!$this->sock)
      {
      $this->Connect();
      $weopen = 1;
      }
    if($this->debug)
      {
      $this->PrintDebug('DescTable('.$tname.') called - Self-Connect: '.$weopen);
      }
    $start 		= $this->getmicrotime();
    $query 		= 'SELECT * FROM '.$tname.' LIMIT 1';
		$result		= @mysqli_stmt_result_metadata(@mysqli_prepare($this->sock,$query));
		$lv				= 0;
		while($fobj = @mysqli_fetch_field($result))
			{
			$retarray[$lv][db_MySQLi::DBOF_MYSQL_COLNAME] 	= $fobj->name;
      $retarray[$lv][db_MySQLi::DBOF_MYSQL_COLTYPE]   = db_MySQLi::Type2TXT($fobj->type);
      $retarray[$lv][db_MySQLi::DBOF_MYSQL_COLSIZE]   = $fobj->length;
      $retarray[$lv][db_MySQLi::DBOF_MYSQL_COLFLAGS]  = db_MySQLi::Flags2TXT($fobj->flags);
			$lv++;
			}
    @mysqli_free_result($result);
    if($weopen)
      {
      $this->Disconnect();
      }
    $this->querytime+= ($this->getmicrotime() - $start);
    return($retarray);
    } // DescTable()

  /**
   * Returns the number of rows in the result set.
   * Use this after a SELECT or SHOW etc. command has been executed.
   * For DML operations like INSERT, UPDATE, DELETE the method AffectedRows() has to be used.
   * @return integer Number of affected rows.
   * @since 0.1.1
   */
  function NumRows()
    {
    return($this->num_rows);
    }	// NumRows()

  /**
   * Function performs an insert statement from a given variable list.
   * The insert statements will be constructed as NEW Insert style, and aligned to the "max_allowed_packet" boundary.
   * This can dramatically improve bulk-inserts compared to fire every INSERT statement one by one.
   * The array passed must be constructed with the keys defined as fieldnames and the values as the corresponding values.
   * Looks like this:
   *
   * - $data[0]['fieldname1'] = 'wert0/1';
   * - $data[0]['fieldname2'] = 'wert0/2';
   * - $data[1]['fieldname1'] = 'wert1/2';
   * - $data[1]['fieldname2'] = 'wert1/2';
   *
   * NOTE: Database must be connected!
   * @param string $table_name Name of table to perform the insert against.
   * @param array &$fields The associative array
   * @param string $sql Either "INSERT" or "REPLACE", no other command is allowed here!
   * @return array A numeric array with [0] => created query count, [1] => Querysize or FALSE in case of an error.
   * @see examples/test_new_insert.php
   */
  function PerformNewInsert($table_name,&$fields,$sql='INSERT')
    {
    $qcount = 0;
    $qsize  = 0;

    if(StrToUpper($sql) != 'INSERT' && StrToUpper($sql) != 'REPLACE')
      {
      return(FALSE);
      }
    $this->checkSock();

    // First retrieve the max_allowed_packet size:
    $d = $this->Query("SHOW SESSION VARIABLES LIKE 'max_allowed_packet'");
    $max_allowed_packet = intval($d['Value']);

    // Now start with the initial insert command:
    $INSERT = $sql.' INTO '.$table_name.'(';

    // Add the fields:
    foreach($fields as $count => $data) break;
    foreach($data as $fieldname => $fieldvalue)
      {
      $INSERT.=$fieldname.',';
      }
    $INSERT = substr($INSERT,0,strlen($INSERT)-1).') VALUES ';
    reset($fields);
    $query = $INSERT;
    foreach($fields as $count => $data)
      {
      $buffer = '(';
      foreach($data as $fieldname => $fieldvalue)
        {
        if(is_integer($fieldvalue))
          {
          $buffer.=$fieldvalue.',';
          }
        else
          {
          $buffer.="'".$this->EscapeString($fieldvalue)."',";
          }
        }
      $buffer = substr($buffer,0,strlen($buffer)-1).'),';
      if((strlen($query) + strlen($buffer) + 2) < $max_allowed_packet)
        {
        $query.=$buffer;
        }
      else
        {
        // Flush the current query, then append the new values and start again:
        $query = substr($query,0,strlen($query)-1);
        $qsize+= strlen($query);
        $rc = $this->Query($query,MYSQLI_ASSOC,1);
        if($rc != 1)
          {
          return(FALSE);
          }
        $query = $INSERT.$buffer;
        $qcount++;
        }
      }
    // Also add of course the remaining data:
    if($query != '')
      {
      $query = substr($query,0,strlen($query)-1);
      $qsize+= strlen($query);
      $rc = $this->Query($query,MYSQLI_ASSOC,1);
      if($rc != 1)
        {
        return(FALSE);
        }
      $qcount++;
      }
    //printf("QUERIES GENERATED: %d - total size of all queries: %d\n",$qcount,$qsize);
    return(array($qcount,$qsize));
    } // PerformNewInsert()

 /**
   * Sets connection behavour.
   * If FALSE class uses mysqli_connect to connect.
   * If TRUE class uses mysqli_connect to connect with prefix "p:" (Persistant connection).
   * @param boolean $conntype The new setting for persistant connections.
   * @return boolean The previous state.
   * @since 0.1.1
   */
  function SetPConnect($conntype)
    {
    if(is_bool($conntype) == FALSE)
      {
      return($this->usePConnect);
      }
    $oldtype = $this->usePConnect;
    $this->usePConnect = $conntype;
    return($oldtype);
    } // SetPConnect()

  /**
   * Returns current persistant connection flag.
   * @return boolean The current setting (TRUE/FALSE).
   * @since 0.1.3
   */
  public function GetPConnect()
    {
    return($this->usePConnect);
    } // GetPConnect()

  /**
   * Prepares a SQL statement so that the variables can be bound afterwards.
   * Returns the statement handle or FALSE in case of an error.
   * @param string $sql The Query to prepare
   * @return mixed Either the valid statement handle or FALSE in case of an error.
   * @since 0.2.0
   */
  public function Prepare($sql)
    {
    $stmt = mysqli_stmt_init($this->sock);
    if(mysqli_stmt_prepare($stmt,$sql) === FALSE)
      {
      $this->MyErrno  = mysqli_stmt_errno($stmt);
      $this->myErrStr = mysqli_stmt_error($stmt);
      return(FALSE);
      }
    return($stmt);
    }

  /**
   * Executes an prepared statement and optionally bind variables for bindvar-enabled queries.
   * See examples/test_bind_vars.php for a working example.
   * @param mysqli_stmt $stmt The statement handle as returned by Prepare()
   * @param integer $no_exit Decides how the class should react on errors. If you set this to 1 the class won't automatically exit on an error but instead return the mysqli_errno value.
   * @param array $bindvars Associative array with variables to bind via mysqli_stmt_bind_param().
   * @since 0.2.0
   */
  public function Execute($stmt,$no_exit = 0, &$bindvars=null)
    {
    if(is_null($bindvars) === FALSE)
      {
      $args = array($stmt,'');
      $alist= array();
      for($b = 0; $b < count($bindvars); $b++)
        {
        $args[1] .= $bindvars[$b][1];
        $args[]   = &$bindvars[$b][0];    // mysqli_stmt_bind_param() requires references!
        }
      if(call_user_func_array('mysqli_stmt_bind_param', $args) === FALSE)
        {
        if($no_exit)
          {
          $reterror = @mysqli_errno($this->sock);
          return($reterror);
          }
        else
          {
          return($this->Print_Error('Execute(): mysqli_stmt_bind_param() failed!'));
          }
        }
      }
    if(@mysqli_stmt_execute($stmt) === FALSE)
      {
      if($no_exit)
        {
        $reterror = @mysqli_errno($this->sock);
        return($reterror);
        }
      else
        {
        return($this->Print_Error('Execute(): mysqli_stmt_execute() failed!'));
        }
      }
    // Check if we have a result set returned, in this case prepare for fetching
    $metadata = @mysqli_stmt_result_metadata($stmt);
    if($metadata !== FALSE)
      {
      if(@mysqli_stmt_store_result($stmt) == FALSE)
        {
        if($no_exit)
          {
          $reterror = @mysqli_errno($this->sock);
          return($reterror);
          }
        else
          {
          return($this->Print_Error('Execute(): mysqli_stmt_store_result() failure!'));
          }
        }
      }
    return($metadata);
    } //-- Execute()

 /**
   * Single query method with Bind var support.
   * Resflag can be "MYSQLI_NUM" or "MYSQLI_ASSOC" depending on what kind of array you want to be returned.
   * @param string $querystring The SQL query to send to database.
   * @param integer $resflag Decides how the result should be returned:
   *  - MYSQLI_ASSOC = Data is returned as assoziative array
   *  - MYSQLI_NUM   = Data is returned as numbered array
   *  - MYSQLI_BOTH  = Data is returned as both numbered and associative array.
   * @param integer $no_exit Decides how the class should react on errors. If you set this to 1 the class won't automatically exit on an error but instead return the mysqli_errno value.
   * @param array &$bindvars The array with bind vars for the given statement.
   * @return mixed Either an array as result of the query or an error code or TRUE.
   * @since 0.2.0
   */
  public function QueryHash($querystring, $resflag = MYSQLI_ASSOC, $no_exit = 0, &$bindvars=null)
    {
    if(!$this->sock)
      {
      return($this->Print_Error('QueryHash(): No active Connection!'));
      }
    if($querystring == '')
      {
      return($this->Print_Error('QueryHash(): No querystring was supplied!'));
      }
    $this->PrintDebug($querystring);
    $this->currentQuery = $querystring;
    if($this->showError == db_MySQLi::DBOF_RETURN_ALL_ERRORS)
      {
      $no_exit = 1;  // Override if user has set master define
      }
    $start = $this->getmicrotime();
    // If we are called without bind vars, we use the existing Query() function
    if(is_null($bindvars) === TRUE)
      {
      return($this->Query($querystring,$resflag,$no_exit));
      }
    $stmt   = $this->Prepare($querystring);
    if($stmt === FALSE)
      {
      if(!$no_exit)
        {
        return($this->Print_Error(sprintf('QueryHash(): Prepare() failed: %s!',$this->myErrStr)));
        }
      else
        {
        return(-1);  // Return an error code
        }
      }
    $result = $this->Execute($stmt,$no_exit,$bindvars);
    if($result == true)
      {
      // Code below taken from http://php.net/manual/en/mysqli-stmt.bind-result.php#102179 and slightly modified - Thank you!
      $vars = array($stmt);
      $data = array();
      while($field = mysqli_fetch_field($result))
        {
        $vars[] = &$data[$field->name]; // pass by reference
        }
      call_user_func_array('mysqli_stmt_bind_result', $vars);
      $row = $this->FetchResult($stmt,$resflag,$data);
      $this->FreeResult($result);
      }
    else  // No result set found, so just return TRUE
      {
      $row = TRUE;
      }
    $this->FreeResult($stmt);
    return($row);
    } // QueryHash()

  } // db_MySQLi()
