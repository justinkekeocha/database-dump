<?php

namespace Justinkekeocha\DatabaseDump;

use Illuminate\Filesystem\Filesystem;
use Justinkekeocha\DatabaseDump\Commands\DatabaseDumpCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
    }

    public function boot()
    {
        parent::boot();

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../resources/stubs/.gitignore.stub' => config('database-dump.folder') . '.gitignore',
            ], 'database-dump-config');

            $filesystem = (new FileSystem);

            $sourcePath = __DIR__ . '/../src/tests/Feature';
            $destinationPath = base_path('tests/Feature/DatabaseDump');

            // Check if the directory exists before copying
            if (!$filesystem->isDirectory($destinationPath)) {
                $filesystem->copyDirectory($sourcePath, $destinationPath);
            }
        }
    }
}
