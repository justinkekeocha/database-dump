# Introduction

This package intercepts the `migrate:fresh` command, creates a dump of your database and proceeds with normal operation of the migrate:fresh command.
This is useful when you forget to export your database, before running migrations.

You can also use this package to generate a dump of your database in JSON format.

## Installation

```bash
composer require justinkekeocha/laravel-database-dump
```

Publish the assets with this artisan command.

```bash
php artisan vendor:publish --tag=database-dump-config
```

## Usage

```php

# Dump database records before running migrations
php artisan migrate:fresh

# Dump database records
php artisan database:dump
```

## Sample

Sample dump can be found [here](https://github.com/justinkekeocha/laravel-database-dump/blob/main/sample-dump.json)

## Contributing

This package was created for personal use, but pull requests are welcome.

## License

[MIT](https://choosealicense.com/licenses/mit/)
