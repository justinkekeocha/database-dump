<?php

namespace Justinkekeocha\DatabaseDump;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Justinkekeocha\DatabaseDump\Commands\DatabaseDumpCommand;

class DatabaseDumpServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('database-dump')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_database-dump_table')
            ->hasCommand(DatabaseDumpCommand::class);
    }
}
