# PHP 5.3.10 -> 8.4.27 migration tasks

Target: PHP 8.4.27. Fix each item, keep behaviour identical, add tests where possible.
Items marked **sql-backticks** need backticks added by hand around table/column names; change nothing else in the query.

## `\\SYSTEMONEV2\WWW\hris\dashboard\class\oop.php`
- [ ] line 161 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*
- [ ] line 181 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*

## `\\SYSTEMONEV2\WWW\hris\global_includes\jquery-1\development-bundle\demos\autocomplete\search.php`
- [ ] line 591 **addslashes()** (warn): not a safe SQL escape -> bind parameters with PDO prepared statements
- [ ] line 597 **addslashes()** (warn): not a safe SQL escape -> bind parameters with PDO prepared statements
- [ ] line 616 **addslashes()** (warn): not a safe SQL escape -> bind parameters with PDO prepared statements

## `\\SYSTEMONEV2\WWW\hris\includes\mssql_pdo.php`
- [ ] line 24 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*
- [ ] line 31 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*

