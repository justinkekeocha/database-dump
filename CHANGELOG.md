# Changelog

All notable changes to `database-dump` will be documented in this file.

## Introduce isConsistent with database - 2024-08-02

This function check the records we have in the dump file against what is in the database, this is especially useful when you drop database and seed it back, to be sure all records are back before bringing the application up

## 3.1 - 2024-07-11

This version decreases downtime when generating database dump

We generate a schema that contains number of rows in each table and limit the records added by that.

This maintains the relationships and allows application to continue running while dump is being generated.
**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/3.0...3.1

## Better handling of JSON delimiter - 2024-06-22

The previous versions used the search for }, to get  the existence of valid JSON. But since there can be nested JSON, this can throw an error, since it is not valid JSON.

To solve this issue, a delimiter string is added at the end of each row, when this delimiter is found, it indicates the end of the row

**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/2.1.1...3.0

## 2.1.1 - 2024-06-15

### What's Changed

* chore(deps): bump aglipanci/laravel-pint-action from 2.3.1 to 2.4 by @dependabot in https://github.com/justinkekeocha/database-dump/pull/4

**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/2.1...2.1.1

## 2.1 - 2024-04-15

This version uses `stream_get_line`for better JSON detection.

**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/2.0...2.1

## 2.0 - 2024-04-15

This version uses a memory efficient method of streaming the records in the dump file using `fread` function and yielding the result. This entails that there is only one record in memory at any point in time. With this approach, this package can read a theoretical large size of file without exhausting memory.

When the seed method is called first, it reads the whole file and generates a schema that stores the offset of the tables in the file before it starts the seeding action. This schema is created so subsequent seed calls on the same instance (obviously the same file) will just move to the file offset where the table was last found and start reading from the offset

**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/1.5.1...2.0

## fix empty rows in tables - 2024-03-23

This release fixes empty rows in tables due to character encoding

**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/1.5...1.5.1

## add support for laravel 9 & 10 - 2024-03-22

### What's Changed

* chore(deps): bump ramsey/composer-install from 2 to 3 by @dependabot in https://github.com/justinkekeocha/database-dump/pull/2

Illuminate contract was update to support multiple versions

**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/1.4.1...1.5

## fix copying of commands - 2024-02-13

**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/1.4...1.4.1

## 1.4 - 2024-02-09

Make more methods chainable

**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/1.3...1.4

## 1.3 - 2024-02-09

New methods were added to aid in seeding the database with dump file.

## 1.1.2 - 2023-12-16

This release refactors the process of generating dump files. Instead of looping through the whole tables at once and adding to the dump file, the new release, chunks the records of each table and streams them into file. This refactored process, prevents hitting of memory limit.
