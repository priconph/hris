---
description: Migrate PHP 5.3.10 code to PHP 8.4 using the refactor tool, then fix remaining items by hand, max 5 files per run
allowed-tools: Read, Edit, Write, Grep, Glob, Bash(tools/php53-to-84-refactor.php run:*)
---

Migrate this project from PHP 5.3.10 to PHP 8.4.27. Follow CLAUDE.md and `php84-migrate-folder.prompt.md`. For the latter, use Grep to find and Read only the section you need.

## Run rules
- Do not run `php -l`, `phpcs`, or any other check.
- Do not run any git command.
- The only allowed shell command is, using PHP 8.4:
  `tools/php53-to-84-refactor.php run . --quiet --no-backticks --exclude=BACKUP --report=report-all.json --tasks=refactor-tasks.md`

## Steps
1. Run the tool command above once.
2. Edit code by hand to clear the items in `refactor-tasks.md`, one file at a time.
3. After each file, show a short summary: file path, items cleared, items left, open items.
4. Stop after at most 5 files so I can review.

## Conversion rules
- Convert `mysql_*` to PDO prepared statements (guide 3.1 in `php84-migrate-folder.prompt.md`).
- Keep `mssql_*` on the separate SQL Server PDO helper (`<MSSQL_PDO_HELPER>`).
- Do not add backticks to SQL Server queries.
- Keep behaviour, parameter names, session keys, and JSON keys unchanged.
- Never hard-code credentials.
- Never guess. Collect every unknown as an open item and list them at the end.