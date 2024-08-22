<?php

namespace Justinkekeocha\DatabaseDump;

use App\Console\Commands\FreshCommand;
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
             */
            $source = __DIR__ . '/../src/Commands/FreshCommand.php';

            // Define target directory
            $targetDirectory = base_path('app/Console/Commands');
            // Define target file path
            $target = $targetDirectory . '/FreshCommand.php';

            if (!$filesystem->exists($target)) {
                // Create target directory if it doesn't exist
                $filesystem->ensureDirectoryExists($targetDirectory);

                // Copy the file, creating it if it doesn't exist
                $filesystem->put($target, $filesystem->get($source));
            }

            /**
             * Copy tests
             */
            $source = __DIR__ . '/../src/tests/Feature';

            $target = base_path('tests/Feature/DatabaseDump');

            // Check if the directory exists before copying
            if (!$filesystem->isDirectory($target)) {
                $filesystem->copyDirectory($source, $target);
            }
        }

        //TODO: test that artisan command works when not running in console and when running in console
        //https://laracasts.com/discuss/channels/laravel/target-illuminatedatabasemigrationsmigrationrepositoryinterface-is-not-instantiable?reply=448657
        //https://laracasts.com/discuss/channels/laravel/target-illuminatedatabasemigrationsmigrationrepositoryinterface-is-not-instantiable-after-updating-to-laravel-11?reply=938527
        $this->app->singleton(FreshCommand::class, function ($app) {
            return new FreshCommand($app['migrator']);
        });
    }
}
