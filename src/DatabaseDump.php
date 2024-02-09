<?php

namespace Justinkekeocha\DatabaseDump;

use Illuminate\Support\Facades\DB;

class DatabaseDump
{
    protected $resolvedTableName;

    public $dumpTables;

    public $tableData;

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
     *  Reverse the array and get the first file in the array.
     */
    public function getLatestDump(int|string $needle = 0): string
    {
        return $this->getDump($needle);
    }

    public function getDump(int|string $needle): string
    {
        $dumpFolder = config('database-dump.folder');

        $dumpListings = $this->getDirectoryListing($dumpFolder);

        //check if the pointer is an integer
        return is_int($needle)
            ? $dumpFolder.array_reverse($dumpListings)[$needle]
            : "$dumpFolder$needle";
    }

    /**
     * Resolves a model or table name to its corresponding table name.
     */
    public function resolveModelOrTableName(string $modelOrTableName): string
    {
        $resolvedTableName = (class_exists($modelOrTableName) && method_exists($modelOrTableName, 'getTable'))
            ? (new $modelOrTableName())->getTable()
            : $modelOrTableName;

        return $resolvedTableName;
    }

    /**
     * Resolves and sets the table name based on the provided model or table name.
     */
    private function setResolvedTableName(?string $modelOrTableName = null): string
    {

        if ($modelOrTableName !== null) {
            $this->resolvedTableName = $this->resolveModelOrTableName($modelOrTableName);
        }

        if ($this->resolvedTableName) {
            return $this->resolvedTableName;
        } else {
            throw new \InvalidArgumentException('No model or table name provided.');
        }
    }

    /**
     * Get the tables in a dump.
     */
    public function getDumpTables(string $dumpFilePath): self
    {
        $fileContents = file_get_contents($dumpFilePath);
        $jsonData = json_decode($fileContents, true);
        $dumpTables = array_slice($jsonData, 2);

        $this->dumpTables = $dumpTables;

        return $this;
    }

    /**
     * Get the data of a table in a dump.
     */
    public function getTableData(?string $modelOrTableName = null, ?array $dumpTables = null): self
    {

        $dumpTables = is_array($dumpTables) ? $dumpTables : $this->dumpTables;

        if (! $dumpTables) {
            throw new \InvalidArgumentException('No dump tables provided.');
        }

        $resolvedTableName = $this->setResolvedTableName($modelOrTableName);

        $tableKey = array_search($resolvedTableName, array_column($dumpTables, 'name'));

        if ($tableKey === false) {
            throw new \InvalidArgumentException("The table '{$resolvedTableName}' does not exist in the dump provided.");
        }

        $this->tableData = $dumpTables[$tableKey]['data'];

        return $this;
    }

    /**
     * Seed a table with data from a dump.
     */
    public function seed(?string $modelOrTableName = null, ?array $tableData = null, ?int $chunkLength = null, ?callable $formatRowCallback = null): self
    {

        $resolvedTableName = $this->setResolvedTableName($modelOrTableName);

        // Use the provided if given, otherwise use the stored one.
        $tableData = $tableData ?? $this->tableData;

        $chunkLength = $chunkLength ?? config('database-dump.chunk_length');

        if ($tableData) {
            $chunks = array_chunk($tableData, $chunkLength);
            foreach ($chunks as $chunk) {
                if (is_callable($formatRowCallback)) {
                    $formattedChunk = [];
                    foreach ($chunk as $row) {
                        $formattedChunk[] = call_user_func($formatRowCallback, $row);
                    }
                    $chunk = $formattedChunk;
                }
                DB::table($resolvedTableName)->insert($chunk);
            }
        }

        return $this;
    }
}
