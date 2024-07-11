<?php

namespace App\Console\Commands;

use Illuminate\Database\Console\Migrations\FreshCommand as LaravelFreshCommand;

class FreshCommand extends LaravelFreshCommand
{
    /**
     * Execute the console command.
     */
    public function handle()
    {

        if ($this->laravel->environment('production')) {

            if (! config('database-dump.enable')) {

                $this->warn('Warning: The database-dump package is disabled. Running this command in production can lead to potential loss of data.');

                if (! $this->confirm('Do you wish to continue?')) {
                    return;
                }
            }
        }

        if (config('database-dump.enable')) {
            $this->call('database:dump');
        }

        parent::handle();
    }
}
