# PHP 5.3.10 -> 8.4.27 migration tasks

Target: PHP 8.4.27. Fix each item, keep behaviour identical, add tests where possible.
Items marked **sql-backticks** need backticks added by hand around table/column names; change nothing else in the query.

## `\\SYSTEMONEV2\WWW\hris\dashboard\class\oop.php`
- [ ] line 161 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*
- [ ] line 181 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*

## `\\SYSTEMONEV2\WWW\hris\db_hris.php`
- [ ] line 2 **mysql_*** (error): ext/mysql removed in 7.0 -> PDO or mysqli with prepared statements (manual migration)
- [ ] line 4 **mysql_*** (error): ext/mysql removed in 7.0 -> PDO or mysqli with prepared statements (manual migration)
- [ ] line 6 **mysql_*** (error): ext/mysql removed in 7.0 -> PDO or mysqli with prepared statements (manual migration)

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel.php`
- [ ] line 550 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Autoloader.php`
- [ ] line 31 **removed-ini** (warn): ini directive "mbstring.func_overload" no longer exists in PHP 8 -> delete the call; its old behaviour is gone

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\CachedObjectStorage\APC.php`
- [ ] line 156 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\CachedObjectStorage\DiscISAM.php`
- [ ] line 126 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\CachedObjectStorage\Memcache.php`
- [ ] line 160 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\CachedObjectStorage\MemoryGZip.php`
- [ ] line 98 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\CachedObjectStorage\MemorySerialized.php`
- [ ] line 98 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\CachedObjectStorage\PHPTemp.php`
- [ ] line 118 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\CachedObjectStorage\SQLite.php`
- [ ] line 118 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\CachedObjectStorage\SQLite3.php`
- [ ] line 121 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\CachedObjectStorage\Wincache.php`
- [ ] line 160 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Calculation.php`
- [ ] line 3599 **E_USER_ERROR** (info): trigger_error(E_USER_ERROR) deprecated in 8.4 -> throw an exception

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Calculation\DateTime.php`
- [ ] line 498 **strftime()** (warn): deprecated in 8.1 -> date() / IntlDateFormatter
- [ ] line 517 **strftime()** (warn): deprecated in 8.1 -> date() / IntlDateFormatter
- [ ] line 520 **strftime()** (warn): deprecated in 8.1 -> date() / IntlDateFormatter
- [ ] line 521 **strftime()** (warn): deprecated in 8.1 -> date() / IntlDateFormatter

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Calculation\Functions.php`
- [ ] line 0 **parse-error** (error): does not parse on PHP 8.3: PHP Fatal error:  'break' not in the 'loop' or 'switch' context in \\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Calculation\Functions.php on line 574

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Calculation\TextData.php`
- [ ] line 212 **money_format()** (error): removed in 8.0 -> NumberFormatter (intl)
- [ ] line 219 **money_format()** (error): removed in 8.0 -> NumberFormatter (intl)
- [ ] line 499 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Chart\Renderer\jpgraph.php`
- [ ] line 105 **stripslashes()** (info): magic_quotes were ON by default on many 5.3.10 installs; in 8.4 input is never escaped -> remove if it only undid magic quotes (check old php.ini)

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\NamedRange.php`
- [ ] line 82 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted
- [ ] line 228 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Shared\Drawing.php`
- [ ] line 71 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted
- [ ] line 99 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Shared\PCLZip\pclzip.lib.php`
- [ ] line 5347 **set_magic_quotes_runtime()** (error): removed in 7.0 -> delete the call
- [ ] line 5378 **set_magic_quotes_runtime()** (error): removed in 7.0 -> delete the call

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Shared\PDF\fonts\utils\makefont.php`
- [ ] line 62 **set_magic_quotes_runtime()** (error): removed in 7.0 -> delete the call

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Shared\PDF\tcpdf.php`
- [ ] line 7349 **set_magic_quotes_runtime()** (error): removed in 7.0 -> delete the call
- [ ] line 11638 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 11687 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 11687 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 11688 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 11747 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 11748 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 11798 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 11799 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 11853 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 11854 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 12011 **mcrypt_*** (error): ext/mcrypt removed in 7.2 -> openssl_* or sodium_*
- [ ] line 14139 **each()** (error): removed in 8.0 -> use foreach / key() / current()
- [ ] line 14752 **addslashes()** (warn): not a safe SQL escape -> bind parameters with PDO prepared statements
- [ ] line 14810 **addslashes()** (warn): not a safe SQL escape -> bind parameters with PDO prepared statements
- [ ] line 14937 **addslashes()** (warn): not a safe SQL escape -> bind parameters with PDO prepared statements
- [ ] line 14938 **addslashes()** (warn): not a safe SQL escape -> bind parameters with PDO prepared statements
- [ ] line 17870 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]
- [ ] line 18160 **each()** (error): removed in 8.0 -> use foreach / key() / current()
- [ ] line 18172 **each()** (error): removed in 8.0 -> use foreach / key() / current()
- [ ] line 18614 **stripslashes()** (info): magic_quotes were ON by default on many 5.3.10 installs; in 8.4 input is never escaped -> remove if it only undid magic quotes (check old php.ini)
- [ ] line 19288 **create_function()** (error): removed in 8.0 -> use closures / arrow functions
- [ ] line 19330 **create_function()** (error): removed in 8.0 -> use closures / arrow functions
- [ ] line 19342 **create_function()** (error): removed in 8.0 -> use closures / arrow functions
- [ ] line 19394 **create_function()** (error): removed in 8.0 -> use closures / arrow functions
- [ ] line 20690 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]
- [ ] line 22215 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]
- [ ] line 22277 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]
- [ ] line 22412 **create_function()** (error): removed in 8.0 -> use closures / arrow functions
- [ ] line 22555 **create_function()** (error): removed in 8.0 -> use closures / arrow functions

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Shared\String.php`
- [ ] line 406 **utf8_encode()** (warn): deprecated in 8.2 -> mb_convert_encoding($s, "UTF-8", "ISO-8859-1")
- [ ] line 406 **utf8_decode()** (warn): deprecated in 8.2 -> mb_convert_encoding($s, "ISO-8859-1", "UTF-8")

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Shared\trend\trendClass.php`
- [ ] line 87 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted
- [ ] line 98 **E_USER_ERROR** (info): trigger_error(E_USER_ERROR) deprecated in 8.4 -> throw an exception

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Worksheet.php`
- [ ] line 1402 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted
- [ ] line 2782 **unserialize()** (warn): never unserialize user input -> json_decode() or pass ['allowed_classes' => false]

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Writer\Excel2007\Chart.php`
- [ ] line 120 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted
- [ ] line 164 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted
- [ ] line 217 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted
- [ ] line 1020 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Writer\Excel2007\Drawing.php`
- [ ] line 47 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Writer\Excel2007\Rels.php`
- [ ] line 291 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Writer\Excel2007\Workbook.php`
- [ ] line 299 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted
- [ ] line 315 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted

## `\\SYSTEMONEV2\WWW\hris\excel\Classes\PHPExcel\Writer\Excel5\Workbook.php`
- [ ] line 203 **param-order** (warn): required parameter after optional parameter (deprecated since 8.0) -> reorder or make required one nullable/defaulted

## `\\SYSTEMONEV2\WWW\hris\includes\mssql_pdo.php`
- [ ] line 24 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*
- [ ] line 31 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*

