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
    */
    'chunk_length' => 5000,
];
