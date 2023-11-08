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
        if (config('database-dump.enable')) {
            $this->call('database:dump');
        }
        parent::handle();
    }
}
