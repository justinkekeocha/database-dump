# Changelog

All notable changes to `database-dump` will be documented in this file.

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
