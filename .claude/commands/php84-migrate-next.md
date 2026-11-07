---
description: Continue PHP 8.4 migration from refactor-tasks.md for the next 5 files, without re-running the tool
allowed-tools: Read, Edit, Write, Grep, Glob
---

Continue the PHP 5.3.10 to PHP 8.4.27 migration. Follow CLAUDE.md and `php84-migrate-folder.prompt.md`. Use Grep to find and Read only the section you need.

## Run rules
- Do not run the refactor tool again.
- Do not run any shell command, check, or git command.

## Steps
1. Read `refactor-tasks.md` and pick the next 5 files that still have open items. Skip files already marked `[x]`.
2. Edit code by hand, one file at a time, using the same conversion rules as `/php84-migrate`.
3. After each file, mark its items `[x]` in `refactor-tasks.md` and show a short summary: file path, items cleared, items left, open items.
4. Stop after at most 5 files so I can review.

## Conversion rules
- Convert `mysql_*` to PDO prepared statements (guide 3.1).
- Keep `mssql_*` on `<MSSQL_PDO_HELPER>`.
- Do not add backticks to SQL Server queries.
- Keep behaviour, parameter names, session keys, and JSON keys unchanged.
- Never hard-code credentials. Never guess; list unknowns as open items.