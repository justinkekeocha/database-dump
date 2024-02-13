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

            $target = base_path('app/Console/Commands/FreshCommand.php');
            $source = __DIR__ . '/../src/Commands/FreshCommand.php';

            if (!$filesystem->exists($target)) {
                fopen($target, "w");
                $filesystem->copy($source, $target);
            }

            $target = base_path('tests/Feature/DatabaseDump');
            $source = __DIR__ . '/../src/tests/Feature';

            // Check if the directory exists before copying
            if (!$filesystem->isDirectory($target)) {
                $filesystem->copyDirectory($source, $target);
            }
        }
    }
}
