<?php

namespace Justinkekeocha\DatabaseDump;

use Exception;
use Illuminate\Support\Facades\DB;
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
            ? (new $modelOrTableName())->getTable()
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

        fseek($file, $offset);

        // Ensure the file is opened
        if (! $file) {
            throw new Exception("Unable to open the file: {$this->filePath}");
        }

        $buffer = ''; // Initialize a buffer to accumulate JSON text
        $inJson = false; // Flag to track whether we are inside a JSON structur

        try {

            while (! feof($file)) {
                $line = fgets($file);

                $this->fileOffset = ftell($file);

                //if File format is still in line with package dump generator
                $decodeJson = json_decode(substr($line, 0, -2));
                if ($decodeJson) {
                    yield $decodeJson;
                } else {
                    //This was added in case file wast formmated by mistake with prettier
                    $trimmedLine = trim($line);
                    if ($trimmedLine == '{') {
                        // Start of a JSON object
                        $inJson = true;
                        $buffer .= $trimmedLine;
                    } elseif ($inJson) {
                        $buffer .= $trimmedLine;
                        if ($trimmedLine == '},' || $trimmedLine == '}') {
                            // End of a JSON object
                            $json = rtrim($buffer, ','); // Remove any trailing comma
                            $decoded = json_decode($json);
                            if ($decoded) {
                                yield $decoded;
                            }
                            $buffer = ''; // Reset buffer after yielding the decoded JSON
                            $inJson = false; // Reset JSON flag
                        }
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

    /**
     * Seed a table with data from a dump.
     */
    public function seed(string|array $modelOrTableName, ?int $chunkLength = null, ?callable $formatRowCallback = null): self
    {
        $chunkLength = $chunkLength ?? config('database-dump.chunk_length');

        if (is_array($modelOrTableName)) {
            foreach ($modelOrTableName as $row) {
                $tables[] = $this->resolveModelOrTableName($row);
            }
            //Ignore $formatRow
        } else {
            $tables[] = $this->resolveModelOrTableName($modelOrTableName);
        }

        //GenerateSchema
        if (! $this->schema) {
            $this->generateSchema();
        }

        foreach ($tables as $tableName) {

            if (array_key_exists($tableName, $this->schema['tables']) == false) {
                throw new \InvalidArgumentException("The table '{$tableName}' does not exist in the dump provided.");
            }

            $tableOffset = $this->schema['tables'][$tableName]['file_offset'];

            $tableData = [];

            foreach ($this->readFile($tableOffset) as $row) {

                $isHeader = (
                    $this->isTableHeader($row) &&
                    $row->name == $tableName
                );

                $isFooter = (
                    $this->isTableFooter($row) &&
                    $row->name == $tableName
                );

                //Check header tag
                if (! $isHeader && ! $isFooter) {
                    $rowToArray = (array) $row;
                    if (is_callable($formatRowCallback)) {
                        $rowToArray = call_user_func($formatRowCallback, $rowToArray);
                    }
                    $tableData[] = $rowToArray;
                }

                if ($isFooter || count($tableData) == $chunkLength) {
                    DB::table($tableName)->insert($tableData);
                    $tableData = [];
                }

                //check footer tag
                if ($isFooter) {
                    break;
                }
            }
        }

        return $this;
    }
}
