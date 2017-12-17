<?php

namespace ctf0\MediaManager;

use Illuminate\Support\Facades\Route;

class MediaRoutes
{
    public static function routes()
    {
        Route::group([
            'prefix'    => 'media',
            'as'        => 'media.',
        ], function () {
            Route::get('/', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@index', 'as' => 'index']);
            Route::post('upload', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@upload', 'as' => 'upload']);
            Route::post('files', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@get_files', 'as' => 'files']);
            Route::post('directories', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@get_dirs', 'as' => 'directories']);
            Route::post('new_folder', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@new_folder', 'as' => 'new_folder']);
            Route::post('delete_file', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@delete_file', 'as' => 'delete_file']);
            Route::post('move_file', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@move_file', 'as' => 'move_file']);
            Route::post('rename_file', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@rename_file', 'as' => 'rename_file']);
            Route::post('lock_file', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@lock_file', 'as' => 'lock_file']);

            Route::post('folder_download', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@folder_download', 'as' => 'folder_download']);
            Route::post('files_download', ['uses' => '\ctf0\MediaManager\Controllers\MediaController@files_download', 'as' => 'files_download']);
        });
    }
}
