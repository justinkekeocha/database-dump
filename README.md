[![Latest Version on Packagist](https://img.shields.io/packagist/v/justinkekeocha/database-dump.svg?style=flat-square)](https://packagist.org/packages/justinkekeocha/database-dump)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/justinkekeocha/database-dump/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/justinkekeocha/database-dump/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/justinkekeocha/database-dump/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/justinkekeocha/database-dump/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/justinkekeocha/database-dump.svg?style=flat-square)](https://packagist.org/packages/justinkekeocha/database-dump)

This package intercepts the `migrate:fresh` command, creates a dump of your database and proceeds with normal operation of the migrate:fresh command. This action is useful when you forget to export your database before running migrations.

You can also use this package to generate a dump of your database in JSON format.

This package is inspired from the export function in phpMyAdmin.

## Contents

-   [Installation](#installation)
-   [Usage](#usage)
    -   [Dump database data](#dump-database-data)
    -   [Seed database with dump file](#seed-database-with-dump-file)
    -   [Get specific dump file](#get-specific-dump-file)
    -   [Get dump tables](#get-dump-tables)
    -   [Get table data](#get-table-data)
    -   [Seed table](#seed-table)
-   [Sample](#sample)
-   [Testing](#testing)
-   [Changelog](#changelog)
-   [Contributing](#contributing)
-   [Credits](#credits)
-   [License](#license)

## Installation

You can install the package via composer:

```bash
composer require justinkekeocha/database-dump
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="database-dump-config"
```

These are the contents of the published config file:

```php
return [

    /*
     *  Enable or disable the package.
    */
    'enable' => true,

    /*
     *  Set the folder generated dumps should be save in.
    */

    'folder' => database_path('dumps/'),

    /*
     *  Set the chunk length of data to be processed at once.
    */
    'chunk_length' => 5000,
];
```

## Usage

### Dump database data

```php
# Dump database data before running migrations
php artisan migrate:fresh

# Dump database data
php artisan database:dump
```

### Seed database with dump file

Load dump file from DatabaseSeeder and pass the dump tables through the `$this->call` method in the seeder class:

```php

# database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Justinkekeocha\DatabaseDump\Facades\DatabaseDump;
use Database\Seeders\UserSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $dump = DatabaseDump::getLatestDump();

        $this->command->outputComponents()->info("Using dump: $dump->dumpFilePath");

        $dumpTables =  $dump->getDumpTables();

        $this->call([
            UserSeeder::class,
        ], parameters: compact('dumpTables'));
    }
}
```

The dump tables data are now available in individual seeder files and you can now seed the table with the data provided:

```php
# database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($dumpTables): void
    {
        $dumpTables->getTableData(User::class)->seed();

        //You can also use table name instead of model.

        $dumpTables->getTableData('users')->seed();
    }
}

```

You can manipulate the rows before seeding:

```php
# database/seeders/CountrySeeder.php

namespace Database\Seeders;

use App\Models\Country;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run($dumpTables): void
    {
        $dumpTables->getTableData(Country::class)->seed(formatRowCallback: function ($row) {
                //331.69 ms
                return [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'code' => 22
                ];

                //OR

                //338.95 ms
                $changes = [
                    'code' => '22'
                ];

                return  collect($row)->only(['id', 'name'])->merge($changes)->all();
    });
    }
}

```

### Get specific dump file

```php

use Justinkekeocha\DatabaseDump\Facades\DatabaseDump;

//Get dump by position in array of directory listing
//Array starts from latest dump file in specified config('database-dump.folder')
DatabaseDump::getDump(1); //Get second dump in the array.

//Get dump by dump file name
DatabaseDump::getDump("2024_01_08_165939.json");

//Get the latest dump
DatabaseDump::getLatestDump();

```

### Get dump tables

Get the tables in a dump file and the records in each table.

```php

use Justinkekeocha\DatabaseDump\Facades\DatabaseDump;

DatabaseDump::getLatestDump()->getDumpTables();

```

### Get table data

Get the data in a table in a dump file.

```php

use Justinkekeocha\DatabaseDump\Facades\DatabaseDump;
use App\Models\User;

DatabaseDump::getLatestDump()->getDumpTables()->getTableData(User::class);

//You can also specify the table name instead of using model
DatabaseDump::getLatestDump()->getDumpTables()->getTableData('users');

```

### Seed table

```php

use Justinkekeocha\DatabaseDump\Facades\DatabaseDump;
use App\Models\User;

DatabaseDump::getLatestDump()->getDumpTables()->getTableData(User::class)->seed();

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

-   [Kekeocha Justin Chetachukwu](https://github.com/justinkekeocha)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
