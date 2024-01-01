<?php

namespace Justinkekeocha\DatabaseDump;

class DatabaseDump
{
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
    public function getLatestDump(int $pointer = 0): string
    {
        $dumpFolder = config('database-dump.folder');

        $dumpListings = $this->getDirectoryListing($dumpFolder);

        return $dumpFolder.array_reverse($dumpListings)[$pointer];
    }
}
