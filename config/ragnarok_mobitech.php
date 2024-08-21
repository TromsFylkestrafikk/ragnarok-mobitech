<?php

return [
    'client_id' => env('MOBITECH_CLIENT_ID'),
    'client_secret' => env('MOBITECH_CLIENT_SECRET'),
    'expiration_date' => env('MOBITECH_EXPIRATION_DATE'),
    'download_url' => env('MOBITECH_DOWNLOAD_URL'),
    'file_list_url' => env('MOBITECH_FILE_LIST_URL'),
    'scope' => env('MOBITECH_SCOPE'),
    'token_endpoint' => env('MOBITECH_TOKEN_ENDPOINT'),

    /*
     |--------------------------------------------------------------------------
     | Disk name used for temporary files
     |--------------------------------------------------------------------------
     */
    'tmp_disk' => 'tmp',
];
