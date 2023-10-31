<?php

namespace Justinkekeocha\DatabaseDump;

use Illuminate\Support\ServiceProvider;
use Justinkekeocha\DatabaseDump\Commands\DatabaseDump;

class DatabaseDumpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {    //Merge package config file with application config file with the same key
        $this->mergeConfigFrom(
            __DIR__ . '/../config/database-dump.php',
            'database-dump'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/database-dump.php' => config_path('database-dump.php')
            ], 'database-dump-config');

            $this->publishes([
                __DIR__ . '/Commands/FreshCommand.php' => app_path('Console/Commands/FreshCommand.php'),
            ], 'database-dump-config');


            $this->commands([
                DatabaseDump::class,
            ]);
        }
    }
}
