[![Latest Version on Packagist](https://img.shields.io/packagist/v/justinkekeocha/database-dump.svg?style=flat-square)](https://packagist.org/packages/justinkekeocha/database-dump)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/justinkekeocha/database-dump/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/justinkekeocha/database-dump/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/justinkekeocha/database-dump/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/justinkekeocha/database-dump/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/justinkekeocha/database-dump.svg?style=flat-square)](https://packagist.org/packages/justinkekeocha/database-dump)

This package intercepts the `migrate:fresh` command, creates a dump of your database and proceeds with normal operation of the migrate:fresh command.
This is useful when you forget to export your database, before running migrations.

You can also use this package to generate a dump of your database in JSON format.

## Installation

You can install the package via composer:

```bash
composer require justinkekeocha/database-dump
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="database-dump-config"
```

This is the contents of the published config file:

```php
return [

    'enable' => true,
    'folder' => database_path('dumps/'),
];
```

## Usage

```php
# Dump database records before running migrations
php artisan migrate:fresh

# Dump database records
php artisan database:dump
```

## Sample

Sample dump can be found [here](../../blob/main/sample-dump.json)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Kekeocha Justin Chetachukwu](https://github.com/justinkekeocha)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
