# Changelog

All notable changes to `database-dump` will be documented in this file.

## 1.4 - 2024-02-09

Make more methods chainable

**Full Changelog**: https://github.com/justinkekeocha/database-dump/compare/1.3...1.4

## 1.3 - 2024-02-09

New methods were added to aid in seeding the database with dump file.

## 1.1.2 - 2023-12-16

This release refactors the process of generating dump files. Instead of looping through the whole tables at once and adding to the dump file, the new release, chunks the records of each table and streams them into file. This refactored process, prevents hitting of memory limit.
