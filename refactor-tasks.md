# PHP 5.3.10 -> 8.4.27 migration tasks

Target: PHP 8.4.27. Fix each item, keep behaviour identical, add tests where possible.
Items marked **sql-backticks** need backticks added by hand around table/column names; change nothing else in the query.

# PHP 5.3.10 -> 8.4.27 Migration Tasks: `hris` & `hris-oop.php`

**Target Environment:** PHP 8.4.27 (Ubuntu)  
**Core Rules:** Keep behavior identical, avoid hard-coded credentials, secure dynamic queries, and respect existing architecture constraints (keeping `hris_oop.php` aligned with its singleton and configuration files).

---

## Phase 1: Configuration & Environment Setup
- [x] **Function Name Clash Prevention:** Wrap `gi_db_connect_args()` and `gi_db_fail()` in `if (!function_exists(...))` across shared include files.
- [x] **Dedicated DB Configuration Files:** Create separate database configuration files (`db_env_acl.php`, `db_env_hris.php`, etc.) using direct credential assignments (`$user`, `$pass`, `$host`, `$name`) without relying on legacy `getenv()` bottlenecks.
- [ ] **Web Server Environment Variables:** Verify and configure PHP-FPM (`clear_env = no` or explicit `env[...]` entries) if environment-based overrides are required for production.

---

## Phase 2: Core Class (`hris-oop.php`) & Database Modernization
- [x] **Singleton & Strict Charset:** Ensure `HRISDB` maintains singleton integrity and explicitly sets `utf8mb4` charset on connection.
- [x] **Prepared Statements & Helper Methods:** Add secure prepared statement methods (e.g., `get_all_sections_by_department()`, `get_all_departments_by_division()`).
- [ ] **SQL Backticks (`sql-backticks`):** Review and add backticks by hand around table and column names in custom queries across `hris-oop.php` without altering query logic.

---

## Phase 3: Refactoring Callers in the `hris/` Folder
- [x] **`hris/updateSection.php`:** Refactored to utilize `get_all_sections_by_department()` instead of raw `execute_search_query()`.
- [x] **`hris/updateDepartment.php`:** Refactored to utilize `get_all_departments_by_division()` instead of raw `execute_search_query()`.
- [ ] **`hris/search.php`:** Convert remaining `execute_search_query()` calls to secure prepared statements or dedicated helper methods.
- [ ] **`group.php` & `group-1.php`:** Modernize database fetching loops and parameter binding.
- [ ] **`newexcel.php` & `excel_2.php`:** Refactor legacy query execution loops (5+ calls each) to handle modern PHP 8 array access and strict null-safety.

---

## Phase 4: PHP 8.4 Compatibility & Notice Cleanups
- [ ] **Undefined Array Keys & POST Handlers:** Fix warnings on `$_POST` lookups (e.g., `DateOfBirth`, `formtype`) using null coalescing (`??`) or `isset()` checks.
- [ ] **Column Capitalization Verification:** Confirm result array key capitalization (e.g., `$EmpInfo['DOBirth']` vs `$row['DoBirth']`) against live database schemas.
- [ ] **Autocomplete JavaScript Escaping:** Apply `json_encode()` or proper escaping for database values injected into autocomplete scripts to prevent injection or syntax breaks.

---

## Phase 5: Testing & Validation
- [ ] **Syntax Linting:** Run `php -l` across all modified files in the `hris/` and `global_includes/` folders.
- [ ] **Manual UI Regression Testing:** Walk through Basic Info, Dependents, Disciplinary, History, and 201 File tabs to ensure identical runtime behavior.

## `\\SYSTEMONEV2\WWW\hris\dashboard\class\oop.php`
- [ ] line 161 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*
- [ ] line 181 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*

## `\\SYSTEMONEV2\WWW\hris\includes\mssql_pdo.php`
- [ ] line 24 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*
- [ ] line 31 **mssql_*** (error): ext/mssql removed in 7.0 -> PDO_SQLSRV / sqlsrv_*

