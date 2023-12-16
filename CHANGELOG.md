# Changelog

All notable changes to `database-dump` will be documented in this file.

## 1.1.2 - 2023-12-16

This release refactors the process of generating dump files. Instead of looping through the whole tables at once and adding to the dump file, the new release, chunks the records of each table and streams them into file. This refactored process, prevents hitting of memory limit.
