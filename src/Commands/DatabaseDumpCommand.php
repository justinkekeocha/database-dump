<?php

namespace Justinkekeocha\DatabaseDump\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\InteractsWithTime;

class DatabaseDumpCommand extends Command
{
    use InteractsWithTime;

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

            with(new TwoColumnDetail($this->getOutput()))->render(
                'Database dump',
                '<fg=yellow;options=bold>GENERATING</>'
            );

            $startTime = microtime(true);

            $databaseName = DB::connection()->getDatabaseName();

            $tables = DB::select('SHOW TABLES');

            //Create file to stream records into
            $dumpFolder = config('database-dump.folder');
            $fileName = date('Y_m_d_His').'.json';

            if (! is_dir($dumpFolder)) {
                mkdir($dumpFolder, 0755, true);
            }

            $filePath = "$dumpFolder$fileName";

            $lineBreak = "\n";

            $databaseHeader = "[$lineBreak".
                '{"markup":"header","type":"database","name":"'.$databaseName.'","comment":"Export database to JSON", "version":"2"},'.$lineBreak.
                '{"markup":"footer","type":"database","name":"'.$databaseName.'"},'."$lineBreak$lineBreak";

            file_put_contents($filePath, $databaseHeader, FILE_APPEND);

            foreach ($tables as $tableKey => $table) {

                //Table header
                $tableName = $table->{'Tables_in_'.$databaseName};

                $table = DB::table($tableName);

                $numberOfTableRecords = $table->count();

                $tableHeaderFinishing = $numberOfTableRecords > 0 ? "$lineBreak$lineBreak" : "$lineBreak";

                $tableHeader = '{"markup":"header","type":"table","name":"'.$tableName.'"},'.$tableHeaderFinishing;

                //Append table header
                file_put_contents($filePath, $tableHeader, FILE_APPEND);

                // Chunk and stream table records
                $orderByColumn = $this->getOrderByColumn($tableName);

                $counter = 1;

                $table->orderBy($orderByColumn)->chunk(config('database-dump.chunk_length'), function ($records) use (&$counter, $lineBreak, $filePath) {
                    $tableData = '';

                    foreach ($records as $record) {

                        $encodedJSON = json_encode($record, JSON_UNESCAPED_UNICODE);
                        //If malformed JSON filter the record
                        if ($encodedJSON === false) {
                            $filteredData = [];

                            foreach ($record as $key => $value) {
                                // If boolean or UTF-8 encoded, keep the value
                                if (is_bool($value) || mb_detect_encoding($value, 'UTF-8', true)) {
                                    $filteredData[$key] = $value;
                                }
                            }

                            $encodedJSON = json_encode($filteredData, JSON_UNESCAPED_UNICODE);
                        }

                        if ($encodedJSON) {
                            $tableData .= "$encodedJSON,$lineBreak";
                        }
                        $counter++;
                    }
                    file_put_contents($filePath, "$tableData", FILE_APPEND);
                });

                $tableFooterBeginning = $numberOfTableRecords > 0 ? "$lineBreak" : '';
                $tableFooter = $tableFooterBeginning.'{"markup":"footer","type":"table","name":"'.$tableName.'"}';
                $addFinishing = array_key_last($tables) == $tableKey ? "$tableFooter$lineBreak]" : "$tableFooter,$lineBreak$lineBreak";

                file_put_contents($filePath, $addFinishing, FILE_APPEND);
            }

            $runTime = $this->runTimeForHumans($startTime);

            with(new TwoColumnDetail($this->getOutput()))->render(
                'Database dump',
                "<fg=gray>$runTime</> <fg=green;options=bold>DONE</>"
            );

            $this->newLine();

            $this->components->info("Database dump saved to $filePath");

            $this->call('up');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->call('up');
            throw $e;
        }
    }

    /**
     * Get a suitable column for ordering if 'id' column is not present.
     */
    private function getOrderByColumn(string $tableName): string
    {
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);

        if (in_array('id', $columns)) {
            $orderByColumn = 'id';
        } elseif (in_array('created_at', $columns)) {
            $orderByColumn = 'created_at';
        } else {
            //Pick first column
            $orderByColumn = reset($columns);
        }

        return $orderByColumn;
    }
}
