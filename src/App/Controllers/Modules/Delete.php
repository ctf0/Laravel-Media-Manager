<?php

namespace ctf0\MediaManager\App\Controllers\Modules;

use Illuminate\Http\Request;
use ctf0\MediaManager\App\Events\MediaFileOpsNotifications;

trait Delete
{
    /**
     * delete files/folders.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function deleteItem(Request $request)
    {
        $path        = $request->path;
        $result      = [];
        $toBroadCast = [];

        foreach ($request->deleted_files as $one) {
            $name      = $one['name'];
            $type      = $one['type'];
            $item_path = $one['storage_path'];
            $defaults  = [
                'name' => $name,
                'path' => $item_path,
            ];

            $del = $type == 'folder'
                    ? $this->storageDisk->deleteDirectory($item_path)
                    : $this->storageDisk->delete($item_path);

            if ($del) {
                $result[]      = array_merge($defaults, ['success' => true]);
                $toBroadCast[] = $defaults;

                // fire event
                event('MMFileDeleted', [
                    'file_path' => $item_path,
                    'is_folder' => $type == 'folder',
                ]);
            } else {
                $result[] = array_merge($defaults, [
                    'success' => false,
                    'message' => trans('MediaManager::messages.error.deleting_file'),
                ]);
            }
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'    => 'delete',
            'items' => $toBroadCast,
            'path'  => $path,
        ]))->toOthers();

        return response()->json($result);
    }
}
