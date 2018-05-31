# db_mysqli class Documentation 

#####Last updated on 31-May-2018


## 1. INTRODUCTION

This class is used as 1:1 replacement for my old db_mysql class, which will
stop functioning in the near future, as the PHP devs decided to drop the
"mysql" extension in favour of the "mysqli" extension.

This class uses the "mysqli" (MySQL improved) extension and has all methods
the db_MySQL class also provides and also provides additional methods during
the extended functionality of the mysqli extension. So i.e. the bind variables
are now part of this class.


## 2. REQUIREMENTS

To use this class you have to met the following requirements:

- PHP 5.x or 7.x with enabled "mysqli" extension. Tested with 5.6.29, 7.0.14 and 7.1.1

- MySQL Database 4.x or newer. Tested with MySQL 5.6.35, 5.7.17 and MariaDB 5.5


## 3. INSTALLATION AND USAGE

Copy the supplied db_mysqli.class.php to a directory of your choice, a good
place would be the inc/ directory of your project. Also copy the file
dbdefs.inc.php to the same directory you have copied the db_mysqli.class.php
file.

The file "dbdefs.inc.php" serves as the configuration file for the class.
You may give an alternate path to this file in the constructor of this class.

The following defines can be set to use the class inside dbdefs.inc.php:

### MYSQLDB_HOST

Hostname or IP address of target database.


### MYSQLDB_PORT

Opt. Portnumber of target DB, defaults to 3306.


### MYSQLDB_USER

MySQL Username used as default connection.


### MYSQLDB_PASS

MySQL Password for useraccount above.


### MYSQLDB_DATABASE

The schema name to use.


### MYSQLAPPNAME

Name of your application. This is used in error messages.


### DB_ERRORMODE

How errors should be handled. Default is to show only limited informations for
safety reasons. See description of `setErrorHandling()` for further details.


### MYSQLDB_ADMINEMAIL

Specify an email address to be used whenever an error occures.
This email is shown in error messages and if _MYSQLDB_SENTMAILONERROR_ is set
also used to sent out an automatic mail to that address in case of an error.


### MYSQLDB_SENTMAILONERROR

Flag indicating if the class should auto-send emails to the defined EMail
address whenever an error occures. Set it to _1_ to enable auto-sending,
and set it to _0_ to disable this behavour.


### MYSQLDB_MAIL_EXTRAARGS

Use this define to pass additional parameter to the `mail()` command in
`SendmailOnError()`. Some servers might need to set the -f parameter when
using PHP's mail() command, and to allow this also here in the class you
can use this define. Default is unset.


### MYSQLDB_USE_PCONNECT

If set to _1_ persistant connections are used, else standard connects are used.
This can be set also on a script-by-script basis via the method `setPConnect()`.


### MYSQLDB_CHARACTERSET

You can set here the character set the class should set to MySQL during the
connect phase. This allows to set the MySQL communication i.e. to 'utf8'.
If this define is not set the default character set of MySQL is used.

### MYSQLDB_TIME_NAMES

You can set here the default language used for date and time translations.
Specify here the values as listed under the following url:

http://dev.mysql.com/doc/refman/5.0/en/locale-support.html

If this define is not set the default language of the MySQL Server is used.


### MYSQLDB_COMPATIBLE_MODE

If you are using this class as replacement for my old "db_MySQL" class,
enable this switch and set it to _TRUE_. The class will simulate the old class
and auto-define all required defines so that you need to change only the
constructor call, everything else should work out-of-the-box.
__New projects should never use this and leave it to the default value of FALSE.__


### How to use

To use the class you have to require() the class code, the rest is done
automatically when you first instantiate the class. Normally you may have one
PHP script which includes several others, here would be the ideal place to put
the require() statement for the class, i.e.:

```PHP
// ...Your require() statements

require("path/to/mysqlidb_class.php");

// ..Rest of your code here
```

Once this is done and you have added the proper values in dbdefs.inc.php you
can now start using the class, this would look like this for example:

```PHP
require("mysqlidb_class.php");

$db = new spfalz\db_mysqli;
$db->Connect();
$mver = $db->Version();
$db->Disconnect();
echo("Your MySQL Server is V".$mver);
```

As you can see in this example the dbdefs.inc.php file is automatically loaded
when you create the first instance of the db_mysqli object.
You can also use a different configfile by specifying a different path to
your config inside the constructor, like this:

```PHP
require("mysqlidb_class.php");

$db = new spfalz\db_mysqli('/path/to/my/own/config.inc.php');
$db->Connect();
$mver = $db->Version();
$db->Disconnect();
echo("Your MySQL Server is V".$mver);
```


## 4. METHOD OVERVIEW

I've provided a auto-generated method overview inside the docs subfolder of
the distribution archive which was generated by phpDocumentor.

The class provides the following methods:

### `__construct([mixed $extconfig = ''])`

This is the constructor of the class. Before you can use any of the class
functions you have to create a new instance of it.
NOTE: If you PHP installation has no mysqli functionality build in the class
throws an exception in the constructor.

Example:

```PHP
$db = new spfalz\db_mysqli;
```

You may also give an alternate path to the database definition file:

```PHP
$db = new spfalz\db_mysqli("/path/to/your/own/dbdefs.inc.php");
```

If you ommit the path to dbdefs.inc.php the class tries to include this file
from within the same directory where the class resides.


### `integer AffectedRows ([mixed $extsock = -1])`

Returns the amount of affected rows based on previous DML operation. Note
the word DML (Data Manipulation Language) which implies that this method
only returns values for INSERT, UPDATE, DELETE or REPLACE commands! If no
external connection handle is given the internal saved handle is used.


### `void CheckSock ()`

Internal function that checks if the internal socket variable is populated.
If this is not the case class calls Print_Error() and prints out an error
stating __"!!! NOT CONNECTED TO AN MYSQL DATABASE !!!"__.


### `void Commit ()`

Commits a transaction. Note that this is only supported for transaction-
enabled storage engines like InnoDB; MyISAM tables are not transactional and
therefor this command simply does nothing.


### `mixed Connect ([string $user = ''], [string $pass = ''], [string $host = ''],[string $db = ''], [integer $port=0])`

Performs connection to a MySQL database server. Normally you do not have to
supply here any of the parameters, as these parameters are taken from the
dbdefs.inc.php file automatically.
If an error occures during the connect attempt the class either returns an
error code to the callee (if __DB_ERRORMODE__ is set to db_mysqli::DBOF_RETURN_ALL_ERRORS)
or prints out an error message and terminates execution.
If all goes well this method returns the connection handle. You do not have
to save this value, the class stores this handle internally and uses this
handle whenever you do not supply an handle.


### `string ConvertMySQLDate (string $mysqldate, string $fmtstring)`

Converts a MySQL default Datestring (_YYYY-MM-DD HH:MI:SS_) into a `strftime()`
compatible format. You can use all format tags that strftime() supports, this
function simply converts the mysql date string into a timestamp which is then
passed to strftime together with your supplied format. If _$fmtstring_ is empty
the class uses '_%c_' as default format string.

The converted datestring is then returned.

Please do not use this as default date converter, always use DATE_FORMAT()
inside a query whenever possible as this is much faster than using this
function! Only if you cannot use the MySQL SQL Date converting functions
consider using this function.


### `array  DescTable  (string $tname)`

This method describes a given table and returns the structure of the table
as array. The following fields are returned:

| 0 | Column name
| 1 | Column type
| 2 | Column size
| 3 | Column flags


Please note that this method only returns basic informations about the
structure of a table, no constraints or other meta informations are returned.

See _examples/test_desc_tables.php_ for an example how to use this method.


### `void Disconnect ([mixed $other_sock = -1])`

Disconnects from MySQL database. If no external connection handle is given
the class disconnects the internal connection handle, else the supplied one.


### `string EscapeString (string $str)`

Allows to escape a string before adding it to MySQL. For safety you should
always use this method before performing a query. Mainly if you plan to
save data from Web forms you MUST (!) escape all data, else SQL injection
maybe possible! This method also checks first if the `magic_quotes_gpc()`
setting is enabled and calls `stripslashes()` if it is activated.


### `array FetchResult (mixed $result, [integer $resflag = MYSQLI_ASSOC])`

Retrieves next row from statement handle $result and returns the data in
either numeric or associative array format depending on flag $resflag.
The statement handle is returned from `QueryResult()`. If no more data are
found it returns NULL. Classic usage is something like this:

```PHP
$stmt = $db->QueryResult("SELECT FOO FROM BAR ORDER BY FOO");
while($data = $db->FetchResult($stmt))
  {
  echo($data['FOO']);
  }
$db->FreeResult($stmt);
```

Default return format is always associative, if you want to have numeric
arrays you have to change the line above to $db->FetchResult($stmt,MYSQLI_NUM)


### `array Flags2TXT (integer $flags_num)`

Returns a space separated list of all flags for a given column. Typical
values returned from this method may: "NOT_NULL", "PRI_KEY" etc.
Usage is shown inside the `DescTable()` method.


### `bool GetAutoCommit(mixed $extsock = -1)`

Returns the autocommit flag status of the connected MySQL session.
_TRUE_ indicates that Autocommit is enabled.


### `mixed FreeResult (mixed $result)`

After the last row is recieved from `FetchResult()` you should free the
statement handle with this function. PHP normally frees all allocated
resources automatically when the script terminates, but you should always
free all your own allocated resources yourself as this is good programming
practise.


### `string GetClassVersion ()`

Returns the class Version. The format of the version string is
MAJOR.MINOR.PATHLEVEL versionnumber, i.e. "0.1.3".


### `mixed GetConnectionHandle ()`

Returns the internally saved connection handle as returned by `Connect()`.
This is useful if you want to use the mysqli_* functions of PHP on an already
connected database handle. Returns _-1_ if no active connection handle exists.


### `integer  GetDebug  ()`

Returns the current bitmask for debug handling. See `SetDebug()` for further
details about debugging with this class.


### `integer  GetErrno  ([mixed $other_sock = -1])`

Returns the error code from the last SQL operation. You can pass your own
connection handle here if you want.


### `integer  GetErrorHandling(void)`

Returns the error handling method currently in use by the class.
See `SetErrorHandling()` for details.


### `string GetErrorText ([mixed $other_sock = -1])`

Returns the error description from the last SQL operation. You can pass your
own connection handle here if you want.


### `bool  GetPConnect()`

Returns the currently used setting for persistant connections.
_TRUE_ if persistant connections are enabled, else _FALSE_.


### `integer GetQueryCount ()`

Returns the current query counter. Whenever the class performs a query
against the database server an internal counter is incremented. This is
useful to track errors, as the `Print_Error()` function dumps out this value,
making it more easy to find the errornous query inside your scripts by simply
counting the queries down to the one where the error occures.


### float `GetQueryTime ()`

Returns amount of time spend on queries executed by this class.
The format is "seconds.microseconds".


### `array get_CharSet ()`

Method to return the current MySQL setting for the character_set variables.

Note that MySQL returns a list of settings, so this method returns all
character_set related settings as an associative array.

See _examples/test_locale.php_ for an example.


### `string get_TimeNames ()`

Method to return the current MySQL setting for the __lc_time_names__ variable.

See _examples/test_locale.php_ for an example.


### `integer LastInsertId ([mixed $extsock = -1])`

Returns last used auto_increment id. Whenever you INSERT a row with an
auto_increment field defined in the underlying table, MySQL auto-increments
this field. With this method you can retrieve the newly updated value.
If no external connection handle is given the internal handle is used.


### `integer NumRows()`

Returns the number of rows in the result set.
Use this after a SELECT or SHOW etc. command has been executed.
For DML operations like INSERT, UPDATE, DELETE the method `AffectedRows()`
has to be used.


### `array PerformNewInsert(string $table_name, array &$fields,[string $sql='INSERT'])`

Performs an INSERT or REPLACE statement from a given variable list.
The statements will be constructed as NEW Insert style, and aligned to the
__max_allowed_packet__ boundary. This can dramatically improve bulk-inserts
compared to fire every INSERT statement one by one.
Note that this method ONLY (!) supports INSERT and REPLACE statements,
all other types are not supporting these NEW-STYLE SQL statements.

The array passed must be constructed with the keys defined as fieldnames and
the values as the corresponding values.

Looks like this:

```PHP
$data[0]['fieldname1'] = 'value0/1';
$data[0]['fieldname2'] = 'value0/2';
$data[1]['fieldname1'] = 'value1/2';
$data[1]['fieldname2'] = 'value1/2';
```

NOTE: Database must be connected!

See also _examples/new_insert.php_ for a working example of this method.


### `void PrintDebug (string $msg)`

Depending on the current DEBUG setting the class dumps out debugging
informations either on screen, to the error.log of PHP or to both. If debug
is not enabled this function does nothing. This is extremly useful when
tracking errors, you can simply call `SetDebug()` with an debug level of your
choice before the query in question and the class dumps out what happens.

Example:

```PHP
$db->SetDebug(db_mysqli::DBOF_DEBUGSCREEN);
$db->Query('SELECT FOO FROM BAR WHERE DUMMY=1');
```

Would result in dumping out the query on screen. See examples for further
details how to use this.


### `mixed Query (string $querystring, [integer $resflag = MYSQLI_ASSOC],integer $no_exit)`

Performs a single-row query and returns result, either as numeric or as
associative array, depending on the $resflag setting.
With the $no_exit flag you can selectively instruct the class NOT to exit
in case of an error (set to 1), even if your master define _DB_ERRORMODE_ has
a different setting.
This method returns the result of the call as array whenever the `mysql_query()`
method returns a non-boolean value.
For all other commands only the numeric value of the call result is returned.
Please remember that associative arrays are case-sensitive, you have to
specify the array index name exactly as specified inside the query!


### `mixed QueryResult (string $querystring, integer $no_exit)`

Performs a multi-row query and returns a statement handle ready to pass to
`FetchResult()` and `FreeResult()`.
With the $no_exit flag you can selectively instruct the class NOT to exit
in case of an error (set to 1), even if your master define _DB_ERRORMODE_ has
a different setting.


### `void Rollback ()`

Rolls back current transaction.
Note that this is only supported for transaction-enabled storage engines like
InnoDB; MyISAM tables are not transactional and therefor this command simply
does nothing.


### `bool SetAutoCommit (bool $state,mixed $extsock = -1)`

Enable (`$state = TRUE`) or disable (`$state = FALSE`) the MySQL AutoCommit flag.
Returns the value from mysqli_autocommit() function call.
If $extsock is given, the setting of this flag will belong to your passed
resource, else the internal resource is used.
See http://php.net/manual/en/mysqli.autocommit.php for details.


### `void SetCompatMode(void)`

If you are replacing my old db_MySQL class with this db_mysqli class, you may
want to check first if all is working without affecting your source code.
This method helps you by defining all old class constants as defines.
The following list is defined (if not defined yet):

OLD DEFINE                | VALUE ASSIGNED TO IT
--------------------------|---------------------------------------
DBOF_DEBUGOFF             | db_mysqli::DBOF_DEBUG_OFF
DBOF_DEBUGSCREEN          | db_mysqli::DBOF_DEBUG_SCREEN
DBOF_DEBUGFILE            | db_mysqli::DBOF_DEBUG_FILE
DBOF_SHOW_NO_ERRORS       | db_mysqli::DBOF_SHOW_NO_ERRORS
DBOF_SHOW_ALL_ERRORS      | db_mysqli::DBOF_SHOW_ALL_ERRORS
DBOF_RETURN_ALL_ERRORS    | db_mysqli::DBOF_RETURN_ALL_ERRORS
MYSQL_ASSOC               | MYSQLI_ASSOC
MYSQL_NUM                 | MYSQLI_NUM
MYSQL_BOTH                | MYSQLI_BOTH

If possible rewrite your source to use only the new defines!


### `void SetConnectionHandle (mixed $extsock)`

Allows to overwrite the internal socket by an external value. However you
REALLY should know what you are doing here, as the class does not track this
change, it simply overwrite the internal handle!


### `void SetDebug (integer $state)`

Function allows debugging of SQL Queries inside your scripts.

$state can have these values:

CONSTANT                     | DESCRIPTION
-----------------------------|--------------------------------------------------------------------
db_mysqli::DBOF_DEBUG_OFF    | Turn off debugging
db_mysqli::DBOF_DEBUG_SCREEN | Turn on debugging on screen (every Query will be dumped on screen)
db_mysqli::DBOF_DEBUG_FILE   | Turn on debugging on PHP errorlog

You can mix the debug levels by adding the according defines. Also you can
retrieve the current debug level setting by calling the method `GetDebug()`.


### `void setErrorHandling (integer $val)`

Allows to set the class handling of errors.

CONSTANT                          | DESCRIPTION
----------------------------------|------------------------------------------------------
db_mysqli::DBOF_SHOW_NO_ERRORS    | Show no security-relevant informations
db_mysqli::DBOF_SHOW_ALL_ERRORS   | Show all errors (useful for develop)
db_mysqli::DBOF_RETURN_ALL_ERRORS | No error/autoexit, just return the mysqli_error code.


### `boolean setPConnect($conntype)`

Change the connection method to either persistant connections or standard
connections.

Set $conntype = TRUE to activate Persistant connections.
Set $conntype = FALSE to deactivate persistant connections.

Default is standard connections.


### `integer set_CharSet(string $charset)`

Method to set the character set of the current connection.

You must specify a valid character set name, else the class will report an error.
See http://dev.mysql.com/doc/refman/5.0/en/charset-charsets.html for a list
of supported character sets.

Return 1 on success, else failure.


### `integer set_TimeNames (string $locale)`

Method to set the time_names setting of the MySQL Server.

Pass it a valid locale string to change the locale setting of MySQL.
Note that this is supported only since 5.0.25 of MySQL!
The locale string is something like 'de_DE' for example.

Returns 0 If an error occures or 1 if change was successful.


### `string Type2TXT(integer $type_id)`

Returns the textual representation for a given column type.
Typical return values would be i.e. "VAR_STRING".

See _examples/test_desc_table.php_ for an example.


### `string Version()`

Returns Database Versionstring. If no active connection exists when calling
this function this method connects itself to the database, retrieve the
version string and disconnects afterwards. If an active connection exists
this connection is used and of course not terminated.


### `QueryHash($SQL, $resflag = MYSQLI_ASSOC, $no_exit = 0, &$bindvars=null)`

A single query function with bind variable support.
To avoid SQL injections and other possible hacking attempts against your
database you should consider using ONLY (!) bind vars for new applications.
Bind vars are a safe way to pass data from/to your MySQL server, the SQL
itself contains only placeholders and the data itself will be "bind" afterwards.

An example:

```
INSERT INTO foo(field1,field2) VALUES(?,?)
```

As you can see the values for field1 and field2 are not directly written to
the SQL statement but will be added later with bind_param calls. This way
no SQL injection can happen, as all data is added separately. Also this
has the nice effect that the SQL statement can be cached, because it won't
change anymore during runtime.
This class supports bind vars in both directions, so you can use bind vars
to INSERT/UPDATE/DELETE/MERGE and also use bind vars inside a WHERE clause
for SELECT statements.

The bind vars are passed as an associative array with value/type pairs.

If you want to insert for the example SQL above the following values:

field1 => 1 (integer)
field2 => 'TEST' (string)

You have to define the following array for the bindvars parameter of QueryHash():

$bindvars = array([1,'i'],['TEST','s']);

'i' and 's' are type definitions of the passed variables, MySQL defines the
following types:

'i' => Integer
's' => String
'd' => Double
'b' => Blob

The class defines four constants for these values:

db_mysqli::DBOF_TYPE_INT;
db_mysqli::DBOF_TYPE_DOUBLE;
db_mysqli::DBOF_TYPE_STRING;
db_mysqli::DBOF_TYPE_BLOB;

See _examples/test_bind_vars.php_ for an example how to use it.


### `QueryResultHash($SQL,$no_exit =0, &$bindvars=null)`

Same functionality as QueryResult() but also supports bind variables.
See QueryHash() for explanation how to use these bind variables.

See _examples/test_bind_vars.php_ and _examples/test_queryresulthash.php_
for examples how to use this method.


## `Prepare($SQL)`

"Prepares" an SQL statement for binding of variables. You must call
this method if you want to manually bind and execute your SQL.
Returns TRUE if all was OK else FALSE and internal error variables
are filled.

See _examples/test_bind_vars.php_ for an example how to use it.


## `Execute($stmt,$no_exit = 0, &$bindvars=null)`

Executes an already prepared statement with optional bind variables.
If bindvars are given, they are bind first before the statement itself
is executed.
Returns either a result set ready for use with "FetchResult()" or FALSE
if no result set exists (INSERT etc. doesn't return a resultset).
Returns an integer in case of an error and automatic error reporting is
disabled.

See _examples/test_bind_vars.php_ for an example how to use it.


## 5. REPLACE "db_MySQL" with "db_mysqli"

If you have used my other MySQL class db_MySQL, you can simply replace that
old class with this db_mysqli class by changing the constructor from:

```PHP
$db = new db_MySQL;
```

to

```PHP
$db = new spfalz\db_mysqli;
```

If you have used the db_MySQL class constants in your code, you should
either set the configuration define __MYSQLDB_COMPATIBLE_MODE__ to TRUE or
alternatively call the compatibility method `SetCompatMode()`.
This method will set all class defines like "DBOF_SHOW_NO_ERRORS" and also
the MYSQL_* defines, in case your PHP installation does not have the mysql
extension included.
Please try to avoid using the `SetCompatMode()`, it it always better to use the
class prefix, so use i.e. db_mysqli::DBOF_SHOW_NO_ERRORS.



## 6. FINAL WORDS AND CONTACT ADDRESSES

I'm using this class now in several projects and never encountered any
problems. However we all know that no software is 100% bugfree, so if you
have found a bug or have suggestions or feature requests feel free to contact
me on the following URL: http://www.saschapfalz.de/contact.php.

Happy coding!
---
