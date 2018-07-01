<?php

return [
    /*
     * ignore files pattern
     *
     * ignore any file starts with "."
     */
    'ignore_files' => '/^\..*/',

    /*
     * filesystem disk
     */
    'storage_disk' => 'public',

    /*
     * manager controller
     */
    'controller' => '\ctf0\MediaManager\Controllers\MediaController',

    /*
     * remove any file special chars except (. _ -)
     */
    'allowed_fileNames_chars' => '.\_\-',

    /*
     * remove any folder special chars except (_ -)
     *
     * to add & nest folders in one go use '\/\_\-'
     */
    'allowed_folderNames_chars' => '\_\-',

    /*
     * disallow uploading files with the following mimetypes
     * https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    'unallowed_mimes' => ['php', 'java'],

    /*
     * other mime-types for images
     */
    'image_extended_mimes' => [
        'binary/octet-stream', // aws
    ],

    /*
     * when file names gets cleand up
     */
    'sanitized_text' => uniqid(),

    /*
     * display file last modification time as
     *
     * check "/vendor/nesbot/carbon/src/Carbon/Carbon.php"
     */
    'last_modified_format' => 'toDateString',

    /*
     * hide file extension in files list
     */
    'hide_files_ext' => true,

    /*
     * load image preview when item is clicked
     */
    'lazy_load_image_on_click' => false,

    /*
     * automatically invalidate cache after ?
     * in "Minutes"
     */
    'cacheExpiresAfter' => 60,

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
];
