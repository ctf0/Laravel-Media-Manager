<?php

namespace ctf0\MediaManager\App\Controllers\Modules;

use Illuminate\Http\Request;
use ctf0\MediaManager\App\Events\MediaFileOpsNotifications;

trait NewFolder
{
    /**
     * create new folder.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function createNewFolder(Request $request)
    {
        $path            = $request->path;
        $new_folder_name = $this->cleanName($request->new_folder_name, true);
        $full_path       = !$path ? $new_folder_name : $this->clearDblSlash("$path/$new_folder_name");
        $message         = '';

        if ($this->storageDisk->exists($full_path)) {
            $message = trans('MediaManager::messages.error.already_exists');
        } elseif (!$this->storageDisk->makeDirectory($full_path)) {
            $message = trans('MediaManager::messages.error.creating_dir');
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'   => 'new_folder',
            'path' => $path,
        ]))->toOthers();

        return compact('message', 'new_folder_name');
    }
}
