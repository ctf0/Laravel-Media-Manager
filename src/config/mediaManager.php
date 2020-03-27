<?php

return [
    /*
     * ignore any file starts with "."
     */
    'ignore_files' => '/^\..*/',

    /*
     * filesystem disk
     */
    'storage_disk' => env('FILESYSTEM_DRIVER', 'public'),

    /*
     * manager controller
     */
    'controller' => '\ctf0\MediaManager\App\Controllers\MediaController',

    /*
     * remove any file special chars except
     * dot .
     * dash -
     * underscore _
     * single quote ''
     * white space
     * parentheses ()
     * comma ,
     */
    'allowed_fileNames_chars' => '\._\-\'\s\(\),',

    /*
     * remove any folder special chars except
     * dash -
     * underscore _
     * white space
     *
     * to add & nest folders in one go add '\/'
     * avoid using '#' as browser interpret it as an anchor
     */
    'allowed_folderNames_chars' => '_\-\s',

    /*
     * disallow uploading files with the following mimetypes
     * https://www.iana.org/assignments/media-types/media-types.xhtml
     */
    'unallowed_mimes' => ['php', 'java'],

    /*
     * extra mime-types
     */
    'extended_mimes' => [
        // any extra mime-types that doesnt have "image" in it
        'image' => [
            'binary/octet-stream', // aws
        ],

        // any extra mime-types that doesnt have "compressed" in it
        'archive' => [
            'application/x-tar',
            'application/zip',
        ],
    ],

    /*
     * when file names gets cleand up
     *
     * put here any global function that
     * doesnt take arguments
     */
    'sanitized_text' => 'uniqid',

    /*
     * display file last modification time as
     * http://carbon.nesbot.com/docs/#api-formatting
     */
    'last_modified_format' => 'toDateString',

    /*
     * hide file extension in files list
     */
    'hide_files_ext' => true,

    /*
     * in-order to get the folder items count & size
     * we need to recursively get all the files inside the folders
     * which could make the request take longer
     */
    'get_folder_info' => true,

    /*
     * do you want to enable broadcasting the changes
     * made by one user to others ?
     *
     * "laravel-echo" must be installed
     */
    'enable_broadcasting' => false,

    /*
     * show "an itunes like" content ratio bar
     */
    'show_ratio_bar' => true,

    /*
     * preview files b4 uploading
     */
    'preview_files_before_upload' => true,

    /*
     * Database connection
     */
    'database_connection' => env('DB_CONNECTION'),

    /*
     * Locked items table name
     */
    'table_locked' => 'locked',

    /*
     * loaded chunk amount "pagination"
     */
    'pagination_amount' => 50,
];
