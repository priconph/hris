# PHP 5.3.10 -> 8.4.27 migration tasks

Target: PHP 8.4.27. Fix each item, keep behaviour identical, add tests where possible.
Items marked **sql-backticks** need backticks added by hand around table/column names; change nothing else in the query.

Exclude the ##\\systemonev2\www\hris\-1 for migration. No need to edit the file inside this folder


