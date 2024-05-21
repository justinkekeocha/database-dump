<?php

namespace Justinkekeocha\DatabaseDump\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Justinkekeocha\DatabaseDump\DatabaseDumpServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Justinkekeocha\\DatabaseDump\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            DatabaseDumpServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_database-dump_table.php.stub';
        $migration->up();
        */
    }
}
