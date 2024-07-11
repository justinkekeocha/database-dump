<?php

return [

    /*
     *  Enable or disable the package.
    */
    'enable' => true,

    /*
     *  Set the folder generated dumps should be save in.
    */

    'folder' => database_path('dumps/'),

    /*
     *  Set the chunk length of data to be processed at once.
     *  The lower this is, the more time it may take to process things.
     *  The higher this is, the more memory it may consume
     *  and more likely hood of hitting database placeholder limit of 65,535 placeholders.
    */
    'chunk_length' => 5_000,

    /*
    *  Set the maximum stream length of data to be processed at once.
    *  This is the maximum size a row in a table is expected to have in your database
    *  This is set to a reasonable default of 1MB
    *  If your database rows are larger than this, you may want to increase this value.
    *  Read more: https://www.php.net/manual/en/function.stream-get-line.php
    */

    'stream_length' => (1 * 1024 * 1024),
];
