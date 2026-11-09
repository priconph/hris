# Task: Refactor `employeeinfo.php` for PHP 8.4 (load employee by PKID first)

## Context
- Legacy PHP 5.x HRIS page (`employeeinfo.php`), being migrated to **PHP 8.4.27**.
- Uses `HRISDB::getInstance()` (from `db_hris.php` / `global_includes/hris-oop.php`), mysqli result sets, jQuery 1.7 / jQuery UI 1.8, and a single big `<form name="EmployeeInfo">` with 9 tabs.
- Read the whole file before changing anything. Do NOT change the DB class method signatures without telling me first.

## PRIMARY GOAL (do this first)
**Fetch the Employee Details by PKID at the very top of the request, before any rendering, and pass that data to every UI input field and other dependent field.**

Required behavior:
1. Resolve `$pkid` once, at the top, from `$_POST['pkid']` (also accept `$_GET['pkid']` if index.php links to this page that way. Check how index.php calls this page and tell me what you found). Cast/validate it as a positive integer.
2. Create ONE function, e.g. `loadEmployeeDetails(int $pkid): array`, that:
   - calls `HRISDB::getInstance()->get_employee_info_by_id($pkid)`,
   - uses `mysqli_fetch_assoc()` and handles `null`/`false` (no row found),
   - merges the row over a **default array containing every key the template uses**, with `''` as the default, so a new employee (no PKID) and a missing employee both render without warnings,
   - computes `Age` safely (see "Dates" below).
3. Save/update/delete handlers (`SaveBasicInfo`, `Dependent*`, `Offense*`, `*History`) run AFTER the pkid is resolved. After any successful write, **re-load `$EmpInfo` by PKID** so the UI shows fresh data.
4. For a new employee (no pkid), `$EmpInfo = defaults`, and the page keeps the "Create new employee" behavior with tabs 2–7 disabled.
5. Every field in the template (`$EmpInfo['LastName']`, `EmpNo`, `fkDivision`, `fkDepartment`, `fkSection`, `fkPosition`, `HiringStatus`, `EmpStatus`, `SalaryType`, `Effectivity`, `Reason`, `Picturepath`, etc.) must come from this one loaded array. No `$EmpInfo` access may happen when the array is undefined or null.
6. Do not invent column names. Take them from the existing template usage and `get_employee_info_by_id`. If you can't confirm a key, list it for me.

## Known PHP 8.x breakages to fix
- **Bare constants** `$taxdep = yes;` / `no;` in the dependents table are a fatal Error in PHP 8. Use `'yes'` / `'no'`.
- **Short open tags** `<?` (the tabs comment block and `<? echo HRISDB...get_salary_type_by_id($i)`) must be `<?php`.
- **`$EmpInfo` undefined/null**: on a GET request, or a POST that isn't `SaveBasicInfo` with no pkid, `$EmpInfo` doesn't exist, yet it is used in `document.title`, `<h3>`, and the form. Fixed by the primary goal above.
- **Null to string functions** (deprecated since 8.1): `htmlentities(null)`, `strlen(null)`, `new DateTime(null)`, `date(..., strtotime(null))`. Guard them.
- **Dates**: `new DateTime()` throws on invalid input in PHP 8, and `'0000-00-00'` / `''` / `null` are all treated differently. Create ONE helper `formatDateForView($value, $fmt = 'm/d/Y')` that returns `''` for empty/null/`0000-00-00`/invalid values and use it everywhere (DOB, DateGraduated, DateHired, dependents DoBirth, offense Effectivity/Notification, history DateStart). Make `GetAge()` return `''` on invalid input.
- **Bug**: in the dependents table the birthday cell and hidden `age` cell check `$EmpInfo['DOBirth']` but format `$row['DoBirth']`. It should check `$row['DoBirth']`.
- **`$_POST` defaulting loop** that mutates `$_POST`: replace with a local `$post = array_merge($defaults, $_POST)` (or `??`) and use `$post[...]` below. Do not write into `$_POST`.
- **Misleading line** `$pkid = $_POST["EmployeeNo"];` runs before the `pkid` check, so a new employee's EmployeeNo is used as pkid. Remove it and confirm nothing relies on it.
- **Resignation table**: if `EmpStatus` is not 1/2/3, the `<table id="resignInfo">` open tag is never echoed but `</table>` is. Add a default branch.
- `htmlentities($systemID = $row['division'])` — drop the pointless `$systemID` assignment.
- `session_start()` is called after includes and `ob_start()`. Move `session_start()` to the very top and check `isset($_SESSION['username'])` (redirect to login if missing, or tell me what index.php does). `$checkSection` is unused, so confirm and remove or keep.
- `PAS::getInstance()` is used in the Appraisal tab but not included in this file. Confirm where it is loaded.
- mysqli in PHP 8.1+ throws `mysqli_sql_exception` by default. Make sure DB calls in this page won't white-screen: catch where it makes sense and show a friendly error.
- Empty strings sent to DATE/INT columns can fail under MySQL strict mode. Pass `null` for empty dates (DOB, DateGraduated, DateHired, dependent DOB, offense dates) if the DB layer allows it. Tell me if the DB methods need changing.

## Security (do in the same pass, small and safe)
- **SQL injection**: `"SELECT * FROM tbl_EmploymentHistory WHERE fkEmployee = ".$EmpInfo['pkid']` and the other inline queries. Use a prepared statement or a new `HRISDB` method (e.g. `get_employment_history_by_employee_id($pkid)`).
- **XSS**: every `echo $EmpInfo[...]` / `echo $row[...]` inside HTML or attributes must go through a helper `e($v)` = `htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8')`. The JS arrays built from DB data (degree tags, offense tags, Departments/Sections arrays) must use `json_encode()` instead of manual string concatenation and `str_replace`.
- Validate `formtype` against a whitelist.

## Constraints
- **Keep behavior and UI identical.** No redesign, no framework change, no jQuery upgrade, no renaming of form fields, element IDs, or `name=` attributes (the JS depends on them, including the `rows[n][...]` column-index logic in `onselect`).
- Don't touch the JS validation logic unless a PHP-generated line breaks (e.g. `document.title` line, the `selectedTab` line).
- Keep comments in English; add short comments for new helpers.
- Keep the code style of the file; no big reformatting of unrelated lines, so the diff stays reviewable.
- Optional, ask first: Post/Redirect/Get after saves.

## Process
1. First reply with a short **plan** (what you'll change, in order, and any questions about `HRISDB` methods or `index.php`). Wait for my OK if there are blocking questions, otherwise proceed.
2. Implement the **PRIMARY GOAL** first, then the PHP 8 fixes, then the security items.
3. Run `php -l employeeinfo.php` using PHP 8.4 if available.
4. Finish with a **change summary**: bullet list of what changed, any DB-class changes required, any assumptions, and a manual test checklist:
   - open an existing employee (all tabs populated)
   - open the page for a new employee (only tab 1 enabled)
   - save basic info
   - add / update / delete dependent
   - add / update / delete offense
   - add / edit / delete employment history
   - resigned and rehired employees (resignation panel visibility)
   - employee with empty/`0000-00-00` dates
   - 201 file upload dialog still opens