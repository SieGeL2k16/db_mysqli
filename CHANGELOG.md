### v1.0.2 (02-Jan-2020)

- Fixed deprecation warning under PHP 7.4.x

### v1.0.1

##### 17-Jun-2018

### v1.0.0

##### 31-May-2018
- Preparation for adding class to composer repository:
  - Changed class name to lowercase "db_mysqli"
  - Added Namespace "spfalz", so class must be now instantiated as spfalz/db_mysqli.
  - Changed examples to work with new namespace schema.

### v0.2.7:

##### 26-Nov-2017 
- Public release as 0.2.7

##### 11-Aug-2017 
- Fixed AffectedRows() when using prepared statements,this value is now determined in Execute() and AffectedRows().

### v0.2.6:

##### 01-Jun-2017 
- Fixed warnings and wrong PHPDoc comments.
- MYSQLDB_COMPATIBLE_MODE is now working, previous version called wrong method to enable this mode.

### v0.2.5:

##### 25-Jan-2017 - Public release as 0.2.5

##### 21-Jan-2017 
- Documentation of class updated.
- microtime() call redirected to microtime(true) instead of using the legacy variant with list().

### v0.2.4:

##### 12-Nov-2016 
- Renamed base class to db_mysqli.class.php. Required
  as my own framework autoloads PHP classes by using
  "classname.class.php" as searchpattern.

##### 18-Oct-2016 
- Added default variable $row to FetchResult() to avoid PHP warnings

### v0.2.3:

##### 14-Sep-2016 
- Fixed Return code of QueryHash() in case of an error.
  If an integer error is returned from Execute() we pass
  this back, else we just return TRUE.
  Also enhanced error handling in Execute(), QueryHash()
  and QueryResultHash() to always return correct error message.
- QueryHash() now also checks if return value from Execute()
  is an instance of mysqli_result instead of checking for
  boolean value.

### v0.2.2:

##### 12-Apr-2016 
- Fixed exception in FetchResult() if wrong type of variable
  is given to mysqli_stmt_result_metadata(). Now the type is
  checked before calling this function.

### v0.2.1:

##### 28-Mar-2016 
- Changed example "test_queryresulthash.php" to auto-create the
  test table "MYSQLI_DB_TEST_QUERIES", populate it with data
  and drop it automatically at the end of the test.

##### 07-Mar-2016 
- Added new method: `QueryResultHash()`

   This method allows to use bind vars when fetching resultset data from MySQL. The FetchResult() method was enhanced to have a single fetch method for both QueryResult() and QueryResultHash()
- Added new example "test_queryresulthash.php" to demonstrate the usage of the new method.
- Fixed the example "test_bind_vars.php".

### V0.2.0:

##### 08-Jan-2015 
- Made GetErrorText() & GetErrno() aware of Statement errors:
  if Prepare() failed the internal class error variables are set
  and utilized in both GetErr*() methods.


##### 06-Jan-2015 
- Changed format of bind vars, now you just have to pass in the following format:`[<VAL>,<TYPE>]` 

       <VAL>   => The value to add
       <TYPE>  => Variable type, see DBOF_TYPE_* types or look in the php.net manual.

##### 10-Dec-2015 
- Added new example test_bind_vars.php which tests the new methods
  related to binding params and result sets including a small benchmark
  to compare bind-vars vs. static queries
- Added new methods:
  - Prepare()
  - Execute()
- Rewritten QueryHash() to utilize Prepare() and Execute() methods.

##### 07-Dec-2015 
- Added Bindvar support, currently only QueryHash() method is implemented.
- Reworked all examples and documentation

### V0.1.3:

##### 09-Jan-2014 
- Public release as v0.1.3.
- Finished documentation.

##### 05-Jan-2014 
- Updated documentation and added missing README to release as 0.1.3
- Added method "GetPConnect()" to retrieve persistant connection flag.

### V0.1.2:

##### 28-Sep-2014 
- Removed all remaining references to MYSQL inside the documentation.

### V0.1.1:

##### 01-Sep-2014 
- Renamed "EnableCompatibleMode()" to "SetCompatMode()" and added missing defines for MYSQL_ASSOC, MYSQL_BOTH and MYSQL_NUM

##### 24-Aug-2014 
- Added all examples from MySQL() class, also added new tests.
- Added new methods:
  - Set_CharSet()
  - Get_CharSet()
  - EnableCompatibleMode()
  - DescTable()
  - GetErrorHandling()
  - AffectedRows()
  - NumRows()
  - PerformNewInsert()
  - SetPConnect()

- Changed constructor method, now it is defined as __construct(), also changed all class variables and constants.
- During PHP's decision to drop the mysql extension in favour of
  the mysqli class, it was necessary to complete this class as
  a replacement for my old mysql class. This in turn requires
  renaming of all defines to match the old class. Also code
  from old class ported to this class to make it usable as if
  it was the old class.

### V0.1.0:

##### 16-Dec-2008 
- Added new methods:
  - QueryResult()
  - FreeResult()

##### 15-Dec-2008 
- Added new methods:
  - ConvertMySQLDate()
  - LastInsertId()
  - EscapeString()
  - set_TimeNames()
  - get_TimeNames()

##### 24-Aug-2008 
- Added new methods:
  - AffectedRows()
  - GetErrNo()
  - GetErrorText()

##### 16-Mar-2008 
- Added new methods:
  - Query()
  - Commit()
  - RollBack()
  - SetAutoCommit()
  - GetAutoCommit()
- Added possibility to configure the TCP port used to connect to the MySQL server.

##### 14-Mar-2008 
- Added first example "test_general.php" which tests basic class functionality. Ported OCI8 test functions to MySQLi.
- Added new methods:
  - SendMailOnError()
  - GetQueryTime()
  - GetQueryCount()
  - Version()

##### 13-Mar-2008 
- Added constructor and the following methods:
  - Connect()
  - Disconnect()
  - GetDebug()
  - SetDebug()
  - PrintDebug()
  - getmicrotime()
  - SetErrorHandling()
  - Print_Error()
  - GetClassVersion()
  - GetConnectionHandle()
  - SetConnectionHandle()

##### 10-Mar-2008 
- Initial version, started as clone of MySQL & OCI8 class.
-----------------------------------------------------------------------------
