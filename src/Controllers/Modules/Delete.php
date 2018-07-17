<?php

namespace ctf0\MediaManager\Controllers\Moduels;

use Illuminate\Http\Request;
use ctf0\MediaManager\Events\MediaFileOpsNotifications;

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
            $item_path = !$path ? $name : $this->clearDblSlash("$path/$name");
            $defaults  = [
                'name' => $name,
                'type' => $type,
            ];

            // folder
            if ($type == 'folder') {
                if ($this->storageDisk->deleteDirectory($item_path)) {
                    $result[] = array_merge($defaults, ['success' => true]);

                    $toBroadCast[] = array_merge($defaults, [
                        'path' => $item_path,
                        'url'  => null,
                    ]);

                    // fire event
                    event('MMFileDeleted', [
                        'file_path' => $this->getItemPath($item_path),
                        'is_folder' => true,
                    ]);
                } else {
                    $result[] = array_merge($defaults, [
                        'success' => false,
                        'message' => trans('MediaManager::messages.error_deleting_file'),
                    ]);
                }
            }

            // file
            else {
                if ($this->storageDisk->delete($item_path)) {
                    $result[] = array_merge($defaults, [
                        'success' => true,
                        'url'     => $this->resolveUrl($item_path),
                    ]);

                    $toBroadCast[] = array_merge($defaults, [
                        'path' => $path,
                        'url'  => $this->resolveUrl($item_path),
                    ]);

                    // fire event
                    event('MMFileDeleted', [
                        'file_path' => $this->getItemPath($item_path),
                        'is_folder' => false,
                    ]);
                } else {
                    $result[] = [
                        'success' => false,
                        'name'    => $item_path,
                        'type'    => $type,
                        'message' => trans('MediaManager::messages.error_deleting_file'),
                    ];
                }
            }
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'    => 'delete',
            'items' => $toBroadCast,
        ]))->toOthers();

        return response()->json($result);
    }
}
