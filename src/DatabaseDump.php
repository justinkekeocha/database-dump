<?php

namespace Justinkekeocha\DatabaseDump;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use stdClass;

class DatabaseDump
{
    protected $resolvedTableName;

    public $filePath;

    protected $fileOffset;

    protected $schema;

    /**
     *  Return an array of files in the directory.
     */
    public function getDirectoryListing($directoryPath): array
    {

        // Check if the directory exists
        if (is_dir($directoryPath)) {
            // Get an array of all the files and directories in the specified directory
            $files = scandir($directoryPath);
            $result = [];
            // Print each file name
            foreach ($files as $file) {
                //Remove current directory and parent directory from listing
                //Choose only files except folders
                if ($file != '.' && $file != '..' && is_file($directoryPath.'/'.$file)) {
                    $result[] = $file;
                }
            }

            return $result;
        } else {
            echo 'The specified directory does not exist.';
        }
    }

    /**
     *  Get a dump file.
     */
    public function getDump(int|string $needle): self
    {
        $dumpFolder = config('database-dump.folder');

        $dumpListings = $this->getDirectoryListing($dumpFolder);

        //check if the pointer is an integer
        $this->filePath = is_int($needle)
            ? $dumpFolder.array_reverse($dumpListings)[$needle]
            : "$dumpFolder$needle";

        return $this;
    }

    /**
     *  Reverse the array and get the first file in the array.
     */
    public function getLatestDump(int|string $needle = 0): self
    {
        return $this->getDump($needle);
    }

    /**
     * Resolves a model or table name to its corresponding table name.
     */
    public function resolveModelOrTableName(string $modelOrTableName): string
    {
        $resolvedTableName = (class_exists($modelOrTableName) && method_exists($modelOrTableName, 'getTable'))
            ? (new $modelOrTableName)->getTable()
            : $modelOrTableName;

        return $resolvedTableName;
    }

    protected function isMarkupTag(stdClass $row): bool
    {
        return isset($row->markup) && isset($row->type) && isset($row->name);
    }

    protected function isTableHeader(stdClass $row)
    {
        return $this->isMarkupTag($row) && ($row->markup == 'header' && $row->type == 'table');
    }

    protected function isTableFooter(stdClass $row): bool
    {
        return $this->isMarkupTag($row) && ($row->markup == 'footer' && $row->type == 'table');
    }

    protected function readFile(int $offset = 0)
    {
        $file = fopen($this->filePath, 'r');

        // Ensure the file is opened
        if (! $file) {
            throw new Exception("Unable to open the file: {$this->filePath}");
        }

        fseek($file, $offset);

        $streamLength = config('database-dump.stream_length');

        try {

            $delimiter = explode('|', 'Kekeochafd77|Justinbdaaefc4e5');

            while (! feof($file)) {

                $this->fileOffset = ftell($file);

                //Removing of white space is mainly in case of JSON pretty print.
                //The reason we don't use something like "Kekeochaee":"Justindbbceaf5" below is that pretty print can add white space in between.
                $line = stream_get_line($file, $streamLength, '"'.$delimiter[1].'"');
                $line = trim("$line"); //remove any leading or trailing whitespace
                $line = rtrim("$line", '"'.$delimiter[0].'":');
                $line = trim("$line"); //remove any leading or trailing whitespace

                if ($this->fileOffset == 0) {
                    $line = trim("$line", '[,');
                } else {
                    $line = trim("$line", ']},');
                }

                $line = "$line}";

                if (! feof($file)) {

                    if ($decodedJson = json_decode($line)) {
                        yield $decodedJson;
                    } else {
                        throw new Exception("Unable to decode the JSON string: {$line}");
                    }
                }
            }
        } finally {
            // Ensure the file is closed
            fclose($file);
        }
    }

    protected function generateSchema(): void
    {
        foreach ($this->readFile() as $row) {
            if ($this->isTableHeader($row)) {
                //search for tables and note offsets
                $this->schema['tables'][$row->name]['file_offset'] = intval($this->fileOffset);
            }
        }
    }

    protected function ensureSafeChunkLength(int $chunkLength, string $tableName): int
    {
        $maxQueryPlaceholders = 65_535;
        $numberOfTableColums = count(Schema::getColumnListing($tableName));
        $safeChunkLength = floor($maxQueryPlaceholders / $numberOfTableColums);

        if ($safeChunkLength < $chunkLength) {
            $chunkLength = $safeChunkLength;
        }

        return $chunkLength;
    }

    /**
     * Seed a table with data from a dump.
     */
    public function seed(string $modelOrTableName, ?int $chunkLength = null, ?callable $formatRowCallback = null): self
    {
        $chunkLength = $chunkLength ?? config('database-dump.chunk_length');

        $tableName = $this->resolveModelOrTableName($modelOrTableName);

        $chunkLength = $this->ensureSafeChunkLength($chunkLength, $tableName);

        /* Generate a schema of the dump and note the file offset of each table.
        This ensures that subsequent seed calls on the same dump file instance don't start afresh,
        But starts gets the already saved offset for the particular table and starts reading from there
        */
        if (! $this->schema) {
            $this->generateSchema();
        }

        if (array_key_exists($tableName, $this->schema['tables']) == false) {
            throw new \InvalidArgumentException("The table '{$tableName}' does not exist in the dump provided.");
        }

        $tableOffset = $this->schema['tables'][$tableName]['file_offset'];

        $tableData = [];

        foreach ($this->readFile($tableOffset) as $row) {

            $isTableHeader = (
                $this->isTableHeader($row) &&
                $row->name == $tableName
            );

            $isTableFooter = (
                $this->isTableFooter($row) &&
                $row->name == $tableName
            );

            //Check header tag
            if (! $isTableHeader && ! $isTableFooter) {
                $rowToArray = (array) $row;
                if (is_callable($formatRowCallback)) {
                    $rowToArray = call_user_func($formatRowCallback, $rowToArray);
                }
                $tableData[] = $rowToArray;
            }

            if ($isTableFooter || count($tableData) == $chunkLength) {
                DB::table($tableName)->insert($tableData);
                $tableData = [];
            }

            //check footer tag
            if ($isTableFooter) {
                break;
            }
        }

        return $this;
    }

    public function isConsistentWithDatabase(): bool
    {

        //TODO: add test to ensure that the first row of the dump is the header that has the schema and stuffs.
        //Also add test for this function.
        foreach ($this->readFile(0) as $row) {
            $tables = (array) $row->data->schema->tables;
            foreach ($tables as $tableName => $data) {
                $tableCount = DB::table($tableName)->count();
                if ($tableCount != $data->count) {
                    throw new Exception("The dump has $data->count records for the table `{$tableName}`, but the database has $tableCount records in the database.");
                }
            }
            break;
        }

        return true;
    }
}
