<?php

return [
    /*
     * ignore files pattern
     */
    'ignore_files' => '/^\..*/',

    /*
     * filesystem disk
     */
    'storage_disk'=> 'public',

    /*
     * remove any file special chars except (. _ -)
     */
    'allowed_fileNames_chars'=> '.\_\-',

    /*
     * remove any folder special chars except (_ -)
     *
     * to add & nest folders in one go use '\/\_\-'
     */
    'allowed_folderNames_chars'=> '\/\_\-',

    /*
     * disallow uploading files with the following mimetypes
     * https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
     */
    'unallowed_mimes' => ['php', 'java'],

    /*
     * when file names gets cleand up
     */
    'sanitized_text'=> 'sanitized',

    /*
     * css farmework
     */
    'framework'=> env('MIX_MM_FRAMEWORK'),
];
