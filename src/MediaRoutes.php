<?php

namespace ctf0\MediaManager;

class MediaRoutes
{
    public static function routes()
    {
        $controller = array_get(config('mediaManager'), 'controller', '\ctf0\MediaManager\Controllers\MediaController');

        app('router')->group([
            'prefix'    => 'media',
            'as'        => 'media.',
        ], function () use ($controller) {
            app('router')->get('/', ['uses' => "$controller@index", 'as' => 'index']);
            app('router')->post('upload', ['uses' => "$controller@upload", 'as' => 'upload']);
            app('router')->post('upload-cropped', ['uses' => "$controller@uploadCropped", 'as' => 'uploadCropped']);
            app('router')->post('upload-link', ['uses' => "$controller@uploadLink", 'as' => 'uploadLink']);

            app('router')->post('files', ['uses' => "$controller@get_files", 'as' => 'files']);
            app('router')->post('directories', ['uses' => "$controller@get_dirs", 'as' => 'directories']);
            app('router')->post('new_folder', ['uses' => "$controller@new_folder", 'as' => 'new_folder']);
            app('router')->post('delete_file', ['uses' => "$controller@delete_file", 'as' => 'delete_file']);
            app('router')->post('move_file', ['uses' => "$controller@move_file", 'as' => 'move_file']);
            app('router')->post('rename_file', ['uses' => "$controller@rename_file", 'as' => 'rename_file']);
            app('router')->post('change_vis', ['uses' => "$controller@change_vis", 'as' => 'change_vis']);
            app('router')->post('lock_file', ['uses' => "$controller@lock_file", 'as' => 'lock_file']);

            app('router')->get('zip_progress/{name?}/{id?}', ['uses' => "$controller@zip_progress", 'as' => 'zip_progress']);
            app('router')->post('folder_download', ['uses' => "$controller@folder_download", 'as' => 'folder_download']);
            app('router')->post('files_download', ['uses' => "$controller@files_download", 'as' => 'files_download']);
        });
    }
}
