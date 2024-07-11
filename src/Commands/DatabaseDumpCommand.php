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

            $startTime = microtime(true);

            $schema = $this->generateSchema();

            with(new TwoColumnDetail($this->getOutput()))->render(
                'Database dump',
                '<fg=yellow;options=bold>GENERATING</>'
            );

            $databaseName = $schema['database_name'];

            //Create file to stream records into
            $dumpFolder = config('database-dump.folder');
            $fileName = $schema['file_name'];

            if (!is_dir($dumpFolder)) {
                mkdir($dumpFolder, 0755, true);
            }

            $filePath = "$dumpFolder$fileName";

            $lineBreak = "\n";

            $complexDelimiter = $this->generateComplexDelimiter();
            $splittedDelimiter = explode('|', $complexDelimiter);
            $delimiter = '"' . $splittedDelimiter[0] . '":"' . $splittedDelimiter[1] . '"';

            $databaseHeader = "[$lineBreak" .
                '{"markup":"header","type":"database","name":"' . $databaseName . '","comment":"Export database to JSON","version":"3","delimiter":' . '"' . $complexDelimiter . '"},' . $lineBreak .
                '{"markup":"footer","type":"database","name":"' . $databaseName . '",' . $delimiter . '},' . "$lineBreak$lineBreak";

            file_put_contents($filePath, $databaseHeader, FILE_APPEND);

            $tables = $schema['tables'];

            foreach ($tables as $tableName => $numberOfTableRecords) {

                $table = DB::table($tableName);

                $tableHeaderFinishing = $numberOfTableRecords > 0 ? "$lineBreak$lineBreak" : "$lineBreak";

                $tableHeader = '{"markup":"header","type":"table","name":"' . $tableName . '",' . $delimiter . '},' . $tableHeaderFinishing;

                //Append table header
                file_put_contents($filePath, $tableHeader, FILE_APPEND);

                // Chunk and stream table records
                $orderByColumn = $this->getOrderByColumn($tableName);

                $processedRecords = 0;

                $table->orderBy($orderByColumn)->chunk(config('database-dump.chunk_length'), function ($records) use ($numberOfTableRecords, &$processedRecords, $lineBreak, $delimiter, $filePath) {
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
                            $encodedJSON = rtrim($encodedJSON, '}') . ',' . $delimiter . '}';
                            $tableData .= "$encodedJSON,$lineBreak";
                        }

                        $processedRecords++;

                        //Limit to when snapshot was taken.
                        if ($numberOfTableRecords == $processedRecords) break;
                    }

                    file_put_contents($filePath, "$tableData", FILE_APPEND);

                    //Limit to when snapshot was taken.
                    //Must return false to break.
                    if ($numberOfTableRecords == $processedRecords) return false;
                });

                $tableFooterBeginning = $numberOfTableRecords > 0 ? "$lineBreak" : '';
                $tableFooter = $tableFooterBeginning . '{"markup":"footer","type":"table","name":"' . $tableName . '",' . $delimiter . '}';
                $addFinishing = array_key_last($tables) == $tableName ? "$tableFooter$lineBreak]" : "$tableFooter,$lineBreak$lineBreak";

                file_put_contents($filePath, $addFinishing, FILE_APPEND);
            }

            $runTime = $this->runTimeForHumans($startTime);

            with(new TwoColumnDetail($this->getOutput()))->render(
                'Database dump',
                "<fg=gray>$runTime</> <fg=green;options=bold>DONE</>"
            );

            $this->newLine();

            $this->components->info("Database dump saved to $filePath");

            return self::SUCCESS;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function generateSchema(): array
    {
        //This function is used to reduce downtime.
        //We count the number of records in each table before we start the dump.
        //This way, we don't need to want till we stream all records before bring application up.
        $this->call('down');

        $databaseName = DB::connection()->getDatabaseName();
        $tables = DB::select('SHOW TABLES');

        $schema = [
            'file_name' => date('Y_m_d_His') . '.json',
            'database_name' => $databaseName,
        ];

        foreach ($tables as $table) {
            $tableName = $table->{'Tables_in_' . $databaseName};
            $tableCount = DB::table($tableName)->count();
            $schema['tables'][$tableName] = $tableCount;
            with(new TwoColumnDetail($this->getOutput()))->render(
                $tableName,
                "<fg=gray>$tableCount</> rows"
            );
        }

        $this->call('up');

        return $schema;
    }

    private function generateComplexDelimiter()
    {
        $randomString1 = bin2hex(random_bytes(1));
        $randomString2 = str_shuffle('abcdef' . bin2hex(random_bytes(1)));

        return "Kekeocha{$randomString1}|Justin{$randomString2}";
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
