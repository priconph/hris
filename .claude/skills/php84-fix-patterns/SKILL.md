---
name: php84-fix-patterns
description: Ready-made fix recipes for migrating ACDCS code from PHP 5.3.10 to 8.4.27 (mysql_*, E_USER_ERROR, string offsets, each/create_function, MySQL 8 DISTINCT/ORDER BY, undefined vars/keys, DataTables scripts). Use when editing a file from refactor-tasks.md, when /php84-migrate or /php84-migrate-next is running, or when a PHP 8 / MySQL 8 error from this project is pasted.
---

# PHP 5.3.10 -> 8.4.27 fix patterns (ACDCS)

Use this together with CLAUDE.md, `php84-migrate-folder.prompt.md` (Grep the section you need, never read it all) and the `/php84-migrate` and `/php84-migrate-next` commands. This skill holds the recipes already decided in this project, so do not re-derive them.

## Workflow (every file)
1. Pick files from `refactor-tasks.md` with open `[ ]` items, at most 5 per run. Skip `[x]`.
2. Read only the lines you need, edit by hand, one file at a time.
3. Mark cleared items `[x]` in `refactor-tasks.md` (re-read it first; the user edits it at the same time, so an Edit can fail with "file changed" and must be retried).
4. After each file print: file path, items cleared, items left, open items.
5. Verify with the Grep tool, never a shell: search the file for the old pattern (`mysql_`, `\$\w+\{`, `each\(`, `create_function`) and confirm only comments remain.
6. Never run the refactor tool again, `php -l`, `phpcs`, or git, unless the command's own run rules allow it. Do not re-run the tool: it regenerates `refactor-tasks.md` and wipes the hand edits.
7. Grep with brace globs (`*.{php,js}`) or subfolder globs can return nothing wrongly. Search a directory with `glob: *.php` and re-check an empty result.

## Ask before guessing (open items, not edits)
- Bare constant `utf8mb4` (should probably be `'UTF-8'`, unconfirmed). Ask before quoting it.
- Where DB credentials come from (`<CONFIG_SOURCE>`), the `<MSSQL_PDO_HELPER>`, and the YPICS return type for `mssql_*` files.
- Replace or hand-patch the bundled html2pdf / TCPDF (suggested: spipu/html2pdf via Composer).
- Do not edit backup or old copies (`*bak*`, `*_mj`, `patch\...`, `*X.php`) unless the user says so. Files the user said not to edit stay untouched.

## Recipes

### mysql_* in a standalone script (DataTables server-side scripts)
Decision for this project: use the shared `ACDCS::getInstance()` mysqli connection, not `getPdo()`. The runtime is PHP 8.4.27 only, so `execute_query()` (8.2+) is fine and 5.3 compatibility is not a requirement.
- Delete the hard-coded `$gaSql` credentials and `mysql_pconnect` / `mysql_select_db`. Add `$db = ACDCS::getInstance(); $aParams = array();`.
- `LIMIT`: `intval()` both values. Sort direction: allow only `asc` / `desc`.
- Every user value becomes `?` and is appended to `$aParams` in the same order as the SQL text (search, then column filters, then category / control_no / drawing_no / dates).
- Run with `$db->execute_query($sql, $aParams)`; the two count queries (`SELECT FOUND_ROWS()`, `COUNT(...)`) use `$db->query($sql)`; rows via `->fetch_array()`.
- Remove `or die(mysql_error())` (mysqli throws since PHP 8.1; never echo error text to the browser).
- Initialise `$query = ''` before appending with `.=`. Default missing `$_GET` keys (`sSearch`, `sEcho`, `category`, ...) and `isset()` the indexed ones (`bSearchable_$i`).
- Include paths: use `__DIR__.'/acdcs_oop.php'` in root scripts; `../acdcs_oop.php` is only right inside a subfolder.

### mysql_* in a normal page or class
Guide 3.1: `getPdo()` with `<CONFIG_SOURCE>`, named parameters, backticks on class SQL (3.1a). `mssql_*` stays on `<MSSQL_PDO_HELPER>` and gets no backticks.

### mssql_* (SQL Server, class YPICS in edcs_oop.php)
Decision for this project: `pdo_sqlsrv`, DSN `sqlsrv:Server=...;Database=...`, `PDO::ERRMODE_EXCEPTION`, credentials left in the class for now (open item). Class methods return an array of rows (`fetchAll(PDO::FETCH_BOTH)`, same keys as `mssql_fetch_array`), never a result handle. Callers change `while($row = mssql_fetch_array($result))` to `foreach($rows as $row)`; where the handler does `$return['result'] = json_encode($result)` keep the rows in `$rows` and set `$result = false` (that is what the old handle encoded to). No backticks on SQL Server queries. A column name cannot be bound: accept only `/^[A-Za-z_][A-Za-z0-9_]*$/`. Only the live handlers are migrated (`acdcs_handler.php`, `acdcs_pats_ppd_handler.php`, `acdcs_cnppts_handler.php`), never backup copies.

### MySQL 8 error 3065 (DISTINCT with ORDER BY on a column not selected)
Symptom: `Expression #1 of ORDER BY clause is not in SELECT list ... incompatible with DISTINCT`.
Fix: `SELECT file_name AS drawing_no FROM t WHERE ... GROUP BY file_name ORDER BY MAX(pkid) DESC`. Same for `SELECT DISTINCT control_number ... ORDER BY pkid`. Check every copy of the class (acdcs_oop.php has three, plus acdcs_cnppts_oop.php and acdcs_pats_ppd_oop.php). ORDER BY a column that depends on a selected primary key is fine.

### trigger_error(..., E_USER_ERROR) (deprecated in 8.4)
In `__clone` / `__wakeup` of singletons: `throw new LogicException('Clone is not allowed.')` / `'Deserializing is not allowed.'`. Other places: `throw new RuntimeException('<same message>')`. Both exist in 5.3.

### Curly-brace string offsets (parse error in 8.0)
`$s{0}` -> `$s[0]`, `$code{$i}` -> `$code[$i]`, `$a['k']{0}` -> `$a['k'][0]`. Use Edit with `replace_all` per distinct pattern. Skip braces inside strings and regex quantifiers (`[0-9]{1,3}`). Re-Grep `\$\w+\{` afterwards.

### Removed functions
- `each()`: `while (list($k,$v) = each($a))` -> `foreach ($a as $k => $v)`.
- `create_function('$m', 'global $x; ...')` -> `function ($m) use ($x) { ... }`. Check that the enclosing method really declares the same `global`.
- `set_magic_quotes_runtime()` / `get_magic_quotes_runtime()`: delete the call.
- `split()` -> `explode()` (or `preg_split()` for a regex). `ini_set('auto_detect_line_endings')` is only deprecated: ask before removing.
- Function parameter named `$_FILES`: rename (`$files`) and update the body; callers still pass `$_FILES`.
- Duplicate parameter names: rename the second.

### unserialize()
`unserialize($s, ['allowed_classes' => false])` when the data is a plain array. No `version_compare` guard is needed: the runtime is PHP 8.4.27 only (see CLAUDE.md).

### Undefined variables and array keys (PHP 8 warnings corrupt JSON/PDF output)
Initialise before loops and conditionals (`$x = ''`), `isset()` / `?? ''` for `$_GET` and `$_POST` keys. Keep the request parameter names and JSON keys unchanged.

### Exceptions and output
No `echo $e`, `die(mysql_error())` or SQL text to the browser: `error_log()` and a generic message.

### Hard-coded credentials
Never copy them into new code. When you see `root` / passwords, delete the block in the converted file and list the remaining places (e.g. `acdcs_oop.php`) as open items.

## Known hotspots (already analysed)
- `acdcs_oop.php`: 4 singleton classes, 3 copies of the SQL; `select_csaf_info` and `select_csaf_assessor_section` build unbound SQL; `set_charset('utf-8')`; hard-coded credentials.
- PDF viewer chain: `csaf_all_docs.php` -> `html2pdf_v4.03/examples/exemple13.php` -> `examples/res/exemple13.php` -> html2pdf -> TCPDF 5.0.002 (bare `utf8mb4`, undefined `$dt_*` / `$ab_*_signature` variables).
- `mssql_*`: `edcs_oop.php` (class YPICS) and the three handlers wait for `<MSSQL_PDO_HELPER>`.
