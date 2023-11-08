<?php

namespace Justinkekeocha\DatabaseDump;

use Illuminate\Filesystem\Filesystem;
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
            ->hasCommand(DatabaseDumpCommand::class);

        if ($this->app->runningInConsole()) {
            (new Filesystem)->copyDirectory(__DIR__ . '/../src/tests/Unit', base_path('tests/Unit'));
        }
    }
}
