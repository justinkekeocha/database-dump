[![Latest Version on Packagist](https://img.shields.io/packagist/v/justinkekeocha/database-dump.svg?style=flat-square)](https://packagist.org/packages/justinkekeocha/database-dump)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/justinkekeocha/database-dump/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/justinkekeocha/database-dump/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/justinkekeocha/database-dump/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/justinkekeocha/database-dump/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/justinkekeocha/database-dump.svg?style=flat-square)](https://packagist.org/packages/justinkekeocha/database-dump)

This package intercepts the `migrate:fresh` command, creates a dump of your database and proceeds with normal operation of the migrate:fresh command. This action is useful when you forget to export your database before running migrations.

This package uses a memory efficient method of streaming the records in the dump file using `fread` function and yielding the result. This entails that there is only one record in memory at any point in time. With this approach, this package can read a theoretical infinite size of file without exhausting memory.

This package is inspired from the export function in phpMyAdmin.

## Contents

-   [Installation](#installation)
-   [Usage](#usage)
    -   [Dump database data](#dump-database-data)
    -   [Seed database with dump file](#seed-database-with-dump-file)
    -   [Get specific dump file](#get-specific-dump-file)
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

         $databaseDump = DatabaseDump::getLatestDump("save/2024_04_14_233109.json");

        $this->command->outputComponents()->info("Using dump:  $databaseDump->filePath");


        $this->call([
            UserSeeder::class,
        ], parameters: compact('databaseDump'));
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
    public function run($databaseDump): void
    {
        $databaseDump->seed(User::class);

        //You can also use table name instead of model.

        $databaseDump->seed('users');
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
    public function run($databaseDump): void
    {
        $databaseDump->seed(Country::class, formatRowCallback: function ($row) {
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

### Seed table

```php

use Justinkekeocha\DatabaseDump\Facades\DatabaseDump;
use App\Models\Country;
use App\Models\Timezone;
use App\Models\User;

DatabaseDump::getLatestDump()->seed(User::class);

//You can seed multiple tables at once.
DatabaseDump::getLatestDump()->seed(Country::class)
->seed(Timezone::class)
->seed(User::class);

```

When seeding from the same dump file, it is more efficient to call the seed method on the already instantiated class. This is because when the seed method is called first, it reads the whole file and generates a schema that stores the offset of the tables in the file before it starts the seeding action. This schema is created so subsequent seed calls on the same instance (obviously the same file) will just move to the file offset where the table was last found and start reading from the offset.

```php

use Justinkekeocha\DatabaseDump\Facades\DatabaseDump;
use App\Models\Country;
use App\Models\Timezone;
use App\Models\User;

//Whole file will be read 3 times
DatabaseDump::getLatestDump()->seed(Country::class);
DatabaseDump::getLatestDump()->seed(Timezone::class);
DatabaseDump::getLatestDump()->seed(User::class);


//Whole file will be read only once.
DatabaseDump::getLatestDump()->seed(Country::class)
->seed(Timezone::class)
->seed(User::class);

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
