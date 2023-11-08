<?php

namespace Justinkekeocha\DatabaseDump\Commands;

use Illuminate\Console\Command;

class DatabaseDumpCommand extends Command
{
    public $signature = 'database-dump';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
