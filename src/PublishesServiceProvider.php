<?php

namespace Justinkekeocha\DatabaseDump;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class PublishesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            (new Filesystem)->copyDirectory(__DIR__ . '/../src/tests/Unit', base_path('tests/Unit'));
        }
    }
}
