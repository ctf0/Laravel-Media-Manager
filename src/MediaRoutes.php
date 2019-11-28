<?php

namespace ctf0\MediaManager;

class MediaRoutes
{
    public static function routes()
    {
        $controller = config('mediaManager.controller', '\ctf0\MediaManager\Controllers\MediaController');

        app('router')->group([
            'prefix' => 'media',
            'as'     => 'media.',
        ], function () use ($controller) {
            app('router')->get('/', ['uses' => "$controller@index", 'as' => 'index']);
            app('router')->post('upload', ['uses' => "$controller@upload", 'as' => 'upload']);
            app('router')->post('upload-cropped', ['uses' => "$controller@uploadEditedImage", 'as' => 'uploadCropped']);
            app('router')->post('upload-link', ['uses' => "$controller@uploadLink", 'as' => 'uploadLink']);

            app('router')->post('files', ['uses' => "$controller@getFiles", 'as' => 'files']);
            app('router')->post('directories', ['uses' => "$controller@getFolders", 'as' => 'directories']);
            app('router')->post('new-folder', ['uses' => "$controller@createNewFolder", 'as' => 'new_folder']);
            app('router')->post('delete-file', ['uses' => "$controller@deleteItem", 'as' => 'delete_file']);
            app('router')->post('move-file', ['uses' => "$controller@moveItem", 'as' => 'move_file']);
            app('router')->post('rename-file', ['uses' => "$controller@renameItem", 'as' => 'rename_file']);
            app('router')->post('change-vis', ['uses' => "$controller@changeItemVisibility", 'as' => 'change_vis']);
            app('router')->post('lock-file', ['uses' => "$controller@lockItem", 'as' => 'lock_file']);

            app('router')->get('global-search', ['uses' => "$controller@globalSearch", 'as' => 'global_search']);
            app('router')->post('locked-list', ['uses' => "$controller@getLockList", 'as' => 'locked_list']);

            app('router')->post('folder-download', ['uses' => "$controller@downloadFolder", 'as' => 'folder_download']);
            app('router')->post('files-download', ['uses' => "$controller@downloadFiles", 'as' => 'files_download']);
        });
    }
}
