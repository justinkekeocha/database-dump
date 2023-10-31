<?php

namespace Justinkekeocha\DatabaseDump\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:dump';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump all table records in a JSON format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $databaseName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');

        $data = [
            ["type" => "header", "comment" => "Export database to JSON"],
            ["type" => "database", "name" => $databaseName],
        ];

        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $databaseName};
            $records = DB::table($tableName)->get();

            $tableData = [
                "type" => "table",
                "name" => $tableName,
                "data" => $records,
            ];
            $data[] = $tableData;
        }

        $jsonOutput = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $dumpFolder = config('database-dump.folder');
        $fileName =   date('YmdHis');

        if (!is_dir($dumpFolder)) {
            mkdir($dumpFolder, 0755, true);
        }
        $filePath = "$dumpFolder/$fileName.json";
        file_put_contents($filePath, $jsonOutput);

        $this->info('Database dump has been saved to ' . $filePath);
    }
}
