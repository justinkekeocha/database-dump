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

            /**
             * Copy commands
             *
             */

            $source = __DIR__ . '/../src/Commands/FreshCommand.php';

            // Define target directory
            $targetDirectory = base_path('app/Console/Commands');

            // Create target directory if it doesn't exist
            $filesystem->ensureDirectoryExists($targetDirectory);

            // Define target file path
            $target = $targetDirectory . '/FreshCommand.php';


            if (!$filesystem->exists($target)) {
                // Copy the file, creating it if it doesn't exist
                $filesystem->put($target, $filesystem->get($source));
            }

            /**
             * Copy tests
             *
             */

            $source = __DIR__ . '/../src/tests/Feature';

            $target = base_path('tests/Feature/DatabaseDump');

            // Check if the directory exists before copying
            if (!$filesystem->isDirectory($target)) {
                $filesystem->copyDirectory($source, $target);
            }
        }
    }
}
