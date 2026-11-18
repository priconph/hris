# PHP 5.3.10 -> 8.4.27 migration tasks

Target: PHP 8.4.27. Fix each item, keep behaviour identical, add tests where possible.
Items marked **sql-backticks** need backticks added by hand around table/column names; change nothing else in the query.

## index.php
- [x] index.php: `$_SESSION['field'|'status'|'search']` read with isset defaults; JS values via json_encode, search value via htmlspecialchars; `$username` cast to string
- [x] index.php line 22: unterminated `</span` fixed (HTML fix, commit separately from the PHP 8.4 refactor)
- [x] index.php line 277: `mktime(0,0,0,$i,1,2012)` already passes day 1 and int args, no change needed
- [ ] OPEN: timezone not set explicitly. Plan: `date.timezone = Asia/Manila` in php.ini and `date_default_timezone_set('Asia/Manila')` in the global_includes bootstrap (not edited, global_includes is excluded)
- [ ] OPEN: review `HRISDB::get_valid_ip()` and session setup in global_includes (static call, PHP 4 constructor, removed functions, X-Forwarded-For validation); files not yet shared

## search.php
- [x] search.php: GET params must be strings (array input treated as empty); output cells escaped via search_e(); DB call moved inside try/catch(Throwable) with error_log and a generic message (was echoing exception text); null result guarded
- [ ] OPEN: `$legacySql` in `$_SESSION['SQL']` is still an executable SQL string built with `$field` concatenated and `real_escape_string`; consumed by excel.php / excel_2.php, so replace it when those are migrated
- [ ] OPEN: HRISDB methods used here (search_position, search_lookup_pkids, search_employees_list, real_escape_string, get_*_name_by_id) live in global_includes; not reviewed

## excel.php (PhpSpreadsheet from ../media/vendor)
- [x] excel.php: hris-oop path fixed (was absolute `/global_includes/...`, now `__DIR__`); autoload now `__DIR__ . '/../media/vendor/autoload.php'`
- [x] excel.php: global exception handler (error_log + generic message); `$h` initialised; FatherName/MotherName header rows got the missing 3rd element; null-safe string/date handling (DOBirth, DateHired, LOS, EmpNo); int casts in arithmetic and mktime; `$counter` initialised
- [x] excel.php: GET input validated before it reaches SQL/arith: Month (int 1-12), idx (YYYY-MM-DD), id (digits), divname (string), `$_SESSION['SQL']` must be a string; roster `<option>` text escaped
- [ ] OPEN: the report SQL is still built by string concatenation and run through `HRISDB::execute_search_query()` (group values come from DB rows); and `$_SESSION['SQL']` from search.php is executed as-is. Needs a bound-parameter DB method in global_includes
- [ ] OPEN: excel.php has no login check (no `$_SESSION['username']` guard like index.php); adding one would change behaviour, decision needed
- [ ] OPEN: cutoffChecker.php and divisionChecker.php (same folder) still use absolute `/global_includes/...` path, `$_POST` unvalidated, and divisionChecker.php concatenates `$_POST['rosterOptions']` into SQL and a redirect URL: migrate next
- [ ] OPEN: DROP/CREATE VIEW (LengthOfService, ages) run on every request with DEFINER `builder`@`%`; confirm the DB user may do this on MySQL 8
- [ ] OPEN: date()/DATE() use the default timezone (see index.php item)

## Decisions recorded (excel.php round 2)
- DB: `HRISDB::query_params(string $sql, array $params = []): mysqli_result|bool` added in global_includes/hris-oop.php (wraps mysqli::execute_query, PHP 8.2+). Values are bound everywhere, including group-query values from DB rows; column names stay on fixed lists / allowlists.
- search.php stores validated `$_SESSION['SQL_PARAMS']` (field, search, pkids, status); excel.php rebuilds the export query with bound parameters. The old `$_SESSION['SQL']` key is still written for excel_2.php: remove it (and `$legacySql` in search.php) when excel_2.php is migrated.
- excel.php: login check added (same session check and message as index.php, plus HTTP 403). Commit it separately as a security fix.
- Views: DROP/CREATE VIEW removed from excel.php; definitions in migrations/001_views.sql (no DEFINER, SQL SECURITY INVOKER). Must be run once before deploying. DB-side check (VERSION(), CURRENT_USER(), SHOW GRANTS, `builder` user) is done by the user.
- Timezone: Asia/Manila confirmed. php.ini `date.timezone = Asia/Manila` (user), and `date_default_timezone_set('Asia/Manila')` in the global_includes bootstrap (still to add). Item closed.
- Found in global_includes: `HRISDB::execute_search_query()` no longer exists (removed in its migration) but employeeinfo.php (5 static queries) and the old excel_2.php still call it: see OPEN below. `getInstance()` is static in HRISDB and PAS; PAS is declared in hris-oop.php; `ini_set('display_errors', 1)` is at the top of hris-oop.php.
- [ ] OPEN: employeeinfo.php lines ~746, 763, 776, 1279, 1291 call the removed execute_search_query(); replace with get_distinct_degrees(), get_distinct_offense_natures(), get_distinct_offense_definitions(), get_active_departments_all_columns(), get_active_sections_all_columns() (they exist in hris-oop.php)
- [ ] OPEN: excel_2.php still on PHPExcel with `/global_includes/` absolute include, no login check, execute_search_query, view DDL per request, executes `$_SESSION['SQL']`; its `ages` view differs from excel.php's (no EmpStatus filter)
- [ ] OPEN: `ini_set('display_errors', 1)` in hris-oop.php sends errors to the browser; rule says use error_log()

## cutoffChecker.php / divisionChecker.php
- [x] cutoffChecker.php: `__DIR__` include, login check, `$_POST['txtDate']` must be a string and a real YYYY-MM-DD date (400 otherwise), redirect built with http_build_query
- [x] divisionChecker.php: `__DIR__` include, login check, `$_POST['rosterOptions']` must be a string, looked up with query_params (bound); unknown division gives 400 (was an empty report with id=blank); redirect built with http_build_query; DB errors go to error_log
- Note: both pages now also require a login (not asked explicitly; excel.php needs it anyway). Include this in the security-fix commit.
- [x] employeeinfo.php: the 5 removed execute_search_query() calls replaced by existing HRISDB helpers (get_distinct_degrees, get_distinct_offense_natures, get_distinct_offense_definitions, get_active_departments_all_columns, get_active_sections_all_columns)

## Round 3 decisions and excel_2.php
- Decision: ONE shared `ages` view, not filtered by status (SELECT * exposes EmpStatus; the view does not aggregate). excel_2.php keeps its old behaviour (includes inactive employees); excel.php adds `AND EmpStatus = 1` (and `ORDER BY Age` for the age groups) in its own queries. See migrations/001_views.sql.
- [ ] OPEN (HR): confirm with HR whether excel_2.php's Age report is meant to include inactive employees. If not, that is a separate fix after the migration (add `AND EmpStatus = 1` there too).
- [x] excel_2.php: migrated PHPExcel to PhpSpreadsheet (../media/vendor), `__DIR__` includes, login check (403), queries rebuilt with bound parameters (query_params), export rebuilt from `$_SESSION['SQL_PARAMS']`, view DDL removed, Month validated, `$h`/FatherName/MotherName/DOBirth/EmpNo/mktime fixes (same as excel.php)
- [x] excel_query_helpers.php (new): shared by excel.php and excel_2.php (excelQuery, excelHeaderQuery, excelGroupQuery, excelMainQuery, excelBuildSearchQuery, exception handler)
- [x] search.php: raw `$_SESSION['SQL']` and `$legacySql` removed (excel_2.php was the last consumer); only `$_SESSION['SQL_PARAMS']` is written
- [ ] OPEN: excel_2.php LengthOfService group query still reads `SELECT '' FROM ages limit 1` (needs the `ages` view to exist, same as before)
- [ ] OPEN: excel/Classes/ (PHPExcel) is no longer used by excel_2.php; left untouched per decision
- [ ] OPEN: run migrations/001_views.sql once before deploying; not run by me

## Round 4: global_includes bootstrap (hris-oop.php)
- [x] `date_default_timezone_set('Asia/Manila')` added at the very top of hris-oop.php, before any include (db_env_*.php already set it too). Timezone item for index.php / excel.php CLOSED. php.ini `date.timezone = Asia/Manila` is set by the user.
- [x] `ini_set('display_errors', 1)` replaced with `error_reporting(E_ALL); ini_set('display_errors', getenv('APP_ENV') === 'dev' ? '1' : '0'); ini_set('log_errors', '1');` (string values, default off). error_log path is set in php.ini by the user.
- Lint list addition: global_includes/hris-oop.php
- [ ] OPEN: hris-oop.php is in its own git repo (global_includes): commit there separately (query_params, timezone, display_errors).

## Round 5: global_includes path
- [x] All hris/*.php includes now point to the shared folder `__DIR__ . '/../global_includes/...'` (www/global_includes): index, search, employeeinfo (+ its 3 jquery web paths), excel, excel_2, excel_query_helpers dependency, cutoffChecker, divisionChecker, db_hris, view, uploads, updateSection, updateDepartment, searchcategory, group, group-1, manager, newexcel, employeeinfo_03_20_2025, employeeinfo_04062016, employeeinfo_rev1. hris/global_includes/ (the inner copy) is no longer referenced and was not touched in this step.

## Round 6: SaveBasicInfo "A database error occurred"
- [x] employeeinfo.php: `change_employement_history`, `add_employee_history` (x2) and `update_employee_history` in global_includes take a `$fkprod` (product line) argument after `$fksec`; the page still passed the old argument list, which throws ArgumentCountError on PHP 8 (caught and shown as the generic message). Now passes `$prodLine` = `get_employee_product_line($pkid)` (the employee's current value, '' when null), so the column is left unchanged.
- [ ] OPEN: `change_employement_history` returns false when the product line is empty, so an employee with no product line gets no employment-history row on save (same as the library behaviour; page has no product line field).
- [ ] OPEN: `ResEffectDate` (Effectivity) is passed raw from the form; if the column is DATE, an empty value can fail under MySQL strict mode. Column type unconfirmed; left unchanged.

## Round 7: viewing an employee showed "A database error occurred"
- [x] employeeinfo.php: the `get_employee_product_line($pkid)` query I added in Round 6 ran on every POST with a pkid (including just opening an employee from search.php). It is now only run for SaveBasicInfo / addHistory / editHistory. Cause of the error is NOT confirmed: the real message is in the PHP error log (`employeeinfo.php:` line). If `tbl_EmployeeInfo` has no `fkProductLine` column, that query fails and saving/history would still error.

## Round 8: Save button still errors, nothing updated
- [x] employeeinfo.php: the catch now logs class, message, file and line (`employeeinfo.php: ArgumentCountError: ... in hris-oop.php:123`); with `APP_ENV=dev` the same text is also shown on the page (default off). `$message` is now escaped with e() where it is printed (it contains user input in the "employee number already existing" message).
- [ ] OPEN: root cause of the Save error still unknown until the log line (or the APP_ENV=dev message) is seen. Candidates: update_employee / update_employee_employment_info binding '' into an INT or DATE column under MySQL strict mode (TelephoneNo, SSN, TIN, PagIbig, PhilHealth, BloodType, Effectivity...), or the new get_employee_product_line query.
- Note: after any exception the page shows an empty form (`$EmpInfo = employeeDefaults()`), which is why a failed save looks like the data vanished; the data is still in the database.

## Round 9: fkProductLine removed (user decision)
- [x] www/global_includes/hris-oop.php: no `fkProductLine` left. Removed `get_employee_product_line()`; dropped the product-line argument from `add_employee_history`, `change_employement_history`, `update_employee_history`, `update_employee_employment_info` (and their SQL); removed the column from get_distinct_employee / get_distinct_employee_by_employeeno / get_distinct_employeeName; removed the `tbl_ProductLine` joins and the `ProductLine` output column from select_emp_list, the supervisor query (~line 1783), select_emp_history, get_empinfo_by_employee_id, get_emphistory_by_employee_id; `update_productline_status` no longer checks for assigned employees; dead UPDATE statement in add_employee_history deleted.
- [x] employeeinfo.php: Round 6 `$prodLine` workaround removed; calls use the new shorter signatures.
- [ ] OPEN: signature changes affect every other caller of these methods outside hris/ (other systems sharing www/global_includes). Callers inside hris/ were checked: only employeeinfo.php.
- [ ] OPEN: any code reading `$row['ProductLine']` from the five changed selects now gets an undefined key; callers outside hris/ not checked.
- Likely root cause of the Save error (Rounds 6-8): the library still wrote/read `fkProductLine`, which is not a column any more.
