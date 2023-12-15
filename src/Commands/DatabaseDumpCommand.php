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

            $this->info('Generating dump....');

            $databaseName = config('database.connections.mysql.database');
            $tables = DB::select('SHOW TABLES');

            $lineBreak = "\n";
            $comma = ",";

            $databaseHeader = "[$lineBreak"  . '{"type":"header","comment":"Export database to JSON"}' . $comma . $lineBreak;
            $databaseHeader .=  '{"type":"database","name":"' . $databaseName . '"}' . $comma . $lineBreak;

            //Create file to stream records into
            $dumpFolder = config('database-dump.folder');
            $fileName = date('Y_m_d_His') . '.json';

            if (!is_dir($dumpFolder)) {
                mkdir($dumpFolder, 0755, true);
            }

            $filePath = "$dumpFolder$fileName";

            file_put_contents($filePath, $databaseHeader);

            foreach ($tables as $tableKey => $table) {

                //Table header
                $tableName = $table->{'Tables_in_' . $databaseName};

                $tableHeader = $lineBreak . '{"type":"table","name":"' . $tableName . '","data":' . $lineBreak . "[$lineBreak";

                //Append table header
                file_put_contents($filePath, $tableHeader, FILE_APPEND);

                // Chunk and stream table records
                $table = DB::table($tableName);
                $orderByColumn = $this->getOrderByColumn($tableName);

                $numberOfTableRecords = $table->count();
                $counter = 1;

                $table->orderBy($orderByColumn)->chunk(config('database-dump.chunk_size'), function ($records) use (&$counter, $numberOfTableRecords, $lineBreak, $comma,  $filePath) {
                    $tableData = "";
                    foreach ($records as $record) {
                        //Done like this in case there are empty tables
                        $addFinishing = $counter != $numberOfTableRecords ? "$comma$lineBreak" : "";
                        $tableData .= json_encode($record, JSON_UNESCAPED_UNICODE) . $addFinishing;

                        $counter++;
                    }

                    file_put_contents($filePath, "$tableData", FILE_APPEND);
                });

                //If not last table, add comma else add closing bracket
                $tableEnding = "$lineBreak]$lineBreak}";
                $addFinishing = array_key_last($tables) == $tableKey ? "$tableEnding$lineBreak]" : "$tableEnding$comma";

                file_put_contents($filePath, $addFinishing, FILE_APPEND);
            }
            $this->info('Database dump has been saved to ' . $filePath);

            $this->call('up');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->call('up');
            throw $e;
        }
    }

    /**
     * Get a suitable column for ordering if 'id' column is not present.
     *
     * @param string $tableName
     * @return string
     */
    private function getOrderByColumn($tableName)
    {
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);


        if (in_array('id', $columns)) {
            $orderByColumn =  'id';
        } elseif (in_array('created_at', $columns)) {
            $orderByColumn =  'created_at';
        } else {
            //Pick first column
            $orderByColumn = reset($columns);
        }

        return $orderByColumn;
    }
}
