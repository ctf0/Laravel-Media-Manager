<?php

namespace ctf0\MediaManager;

use Illuminate\Support\Facades\Route;

class MediaRoutes
{
    public static function routes()
    {
        $controller = array_get(config('mediaManager'), 'controller', '\ctf0\MediaManager\Controllers\MediaController');

        Route::group([
            'prefix'    => 'media',
            'as'        => 'media.',
        ], function () use ($controller) {
            Route::get('/', ['uses' => "$controller@index", 'as' => 'index']);
            Route::post('upload', ['uses' => "$controller@upload", 'as' => 'upload']);
            Route::post('upload-cropped', ['uses' => "$controller@uploadCropped", 'as' => 'uploadCropped']);
            Route::post('upload-link', ['uses' => "$controller@uploadLink", 'as' => 'uploadLink']);

            Route::post('files', ['uses' => "$controller@get_files", 'as' => 'files']);
            Route::post('directories', ['uses' => "$controller@get_dirs", 'as' => 'directories']);
            Route::post('new_folder', ['uses' => "$controller@new_folder", 'as' => 'new_folder']);
            Route::post('delete_file', ['uses' => "$controller@delete_file", 'as' => 'delete_file']);
            Route::post('move_file', ['uses' => "$controller@move_file", 'as' => 'move_file']);
            Route::post('rename_file', ['uses' => "$controller@rename_file", 'as' => 'rename_file']);
            Route::post('lock_file', ['uses' => "$controller@lock_file", 'as' => 'lock_file']);

            Route::get('zip_progress/{name?}', ['uses' => "$controller@zip_progress", 'as' => 'zip_progress']);
            Route::post('folder_download', ['uses' => "$controller@folder_download", 'as' => 'folder_download']);
            Route::post('files_download', ['uses' => "$controller@files_download", 'as' => 'files_download']);
        });
    }
}
