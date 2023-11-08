<?php

namespace Justinkekeocha\DatabaseDump;

class DatabaseDump
{
    public function getDirectoryListing($directoryPath)
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

    public function getLatestDump()
    {
        $dumpListings = $this->getDirectoryListing(config('database-dump.folder'));

        return end($dumpListings);
    }
}
