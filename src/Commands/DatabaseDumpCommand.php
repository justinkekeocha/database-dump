<?php

namespace Justinkekeocha\DatabaseDump\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DatabaseDumpCommand extends Command
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
        try {
            $this->call('down');

            $databaseName = config('database.connections.mysql.database');
            $tables = DB::select('SHOW TABLES');

            $lineBreak = "\n";

            $header = '{"type":"header","comment":"Export database to JSON"},' . $lineBreak;
            $header .= '{"type":"database","name":"' . $databaseName . '"},' . $lineBreak;

            $data = $header . $lineBreak;

            $firstTableKey = array_key_first($tables);
            foreach ($tables as $tableKey => $table) {
                $tableName = $table->{'Tables_in_' . $databaseName};
                $records = DB::table($tableName)->get();

                $recordsJson = $lineBreak;

                $lastRecord = array_key_last($records->toArray());

                foreach ($records as $recordKey => $record) {

                    $addComma = $lastRecord == $recordKey ? '' : ',';

                    $recordsJson .= json_encode($record, JSON_UNESCAPED_UNICODE) . $addComma . $lineBreak;
                }

                $addComma = $firstTableKey ==  $tableKey ? '' : ',';

                $data .= $addComma . '{"type":"table","name":"' . $tableName . '","data":' . $lineBreak . "[$recordsJson]" . $lineBreak . '}' . $lineBreak;
            }

            // Wrap the objects in an array if you want the entire JSON to be an array
            $jsonOutput = "[$lineBreak" . $data . ']';

            $dumpFolder = config('database-dump.folder');
            $fileName = date('Y_m_d_His') . '.json';

            if (!is_dir($dumpFolder)) {
                mkdir($dumpFolder, 0755, true);
            }

            $filePath = "$dumpFolder$fileName";
            file_put_contents($filePath, $jsonOutput);

            $this->info('Database dump has been saved to ' . $filePath);

            $this->call('up');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->call('up');
            throw $e;
        }
    }
}
