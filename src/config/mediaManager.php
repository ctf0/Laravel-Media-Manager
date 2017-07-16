<?php

return [
    // ignore files pattern
    'ignore_files' => '/^\..*/',

    // filesystem disk
    'storage_disk'=> 'public',

    // remove any file special chars except (. _ -)
    'allowed_fileNames_chars'=> '.\_\-',

    // remove any folder special chars except (_ -)
    // to add & nest folders in one go use '\/\_\-'
    'allowed_folderNames_chars'=> '\_\-',

    // when file names gets cleand up
    'sanitized_text'=> 'sanitized',

    // media manager root url
    'root_url'=> '/media',

    // css farmework
    'framework'=> env('MIX_MM_FRAMEWORK'),
];
