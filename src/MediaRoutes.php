<?php

namespace ctf0\MediaManager;

use Illuminate\Support\Facades\Route;

class MediaRoutes
{
    public function __construct()
    {
        Route::group([
            'as'        => 'media.',
            'prefix'    => 'media',
        ], function () {
            Route::get('/', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@index', 'as' => 'index']);
            Route::post('upload', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@upload', 'as' => 'upload']);
            Route::post('files', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@files', 'as' => 'files']);
            Route::post('directories', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@get_all_dirs', 'as' => 'directories']);
            Route::post('new_folder', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@new_folder', 'as' => 'new_folder']);
            Route::post('delete_file_folder', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@delete_file_folder', 'as' => 'delete_file_folder']);
            Route::post('move_file', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@move_file', 'as' => 'move_file']);
            Route::post('rename_file', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@rename_file', 'as' => 'rename_file']);
        });
    }
}
