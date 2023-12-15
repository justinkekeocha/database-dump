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
     *  Set the chunk size of data to be processed at once.
    */
    'chunk_size' => 5000,
];
