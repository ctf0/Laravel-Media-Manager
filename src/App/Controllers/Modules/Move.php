<?php

namespace ctf0\MediaManager\App\Controllers\Modules;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use ctf0\MediaManager\App\Events\MediaFileOpsNotifications;

trait Move
{
    /**
     * move files/folders.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function moveItem(Request $request)
    {
        $copy        = $request->use_copy;
        $destination = $request->destination;
        $result      = [];
        $toBroadCast = [];

        foreach ($request->moved_files as $one) {
            $file_name = $one['name'];
            $file_type = $one['type'];
            $old_path  = $one['storage_path'];
            $defaults  = [
                'name'     => $file_name,
                'old_path' => $old_path,
            ];

            $new_path = "$destination/$file_name";

            try {
                if ($file_type == 'folder' && Str::startsWith($destination, "/$old_path")) {
                    throw new Exception(
                        trans('MediaManager::messages.error.move_into_self')
                    );
                }

                if (!file_exists($new_path)) {
                    // copy
                    if ($copy) {
                        // folders
                        if ($file_type == 'folder') {
                            if (app('files')->copyDirectory($old_path, $new_path)) {
                                $result[] = array_merge($defaults, ['success' => true]);
                            } else {
                                throw new Exception(
                                    isset($this->storageDiskInfo['root'])
                                        ? trans('MediaManager::messages.error.moving')
                                        : trans('MediaManager::messages.error.moving_cloud')
                                );
                            }
                        }

                        // files
                        else {
                            if ($this->storageDisk->copy($old_path, $new_path)) {
                                $result[] = array_merge($defaults, ['success' => true]);
                            } else {
                                throw new Exception(
                                    trans('MediaManager::messages.error.moving')
                                );
                            }
                        }
                    }

                    // move
                    else {
                        if ($this->storageDisk->move($old_path, $new_path)) {
                            $result[]      = array_merge($defaults, ['success' => true]);
                            $toBroadCast[] = $defaults;

                            // fire event
                            event('MMFileMoved', [
                                'old_path' => $old_path,
                                'new_path' => $new_path,
                            ]);
                        } else {
                            $exc = trans('MediaManager::messages.error.moving');

                            if ($file_type == 'folder' && !isset($this->storageDiskInfo['root'])) {
                                $exc = trans('MediaManager::messages.error.moving_cloud');
                            }

                            throw new Exception($exc);
                        }
                    }
                } else {
                    throw new Exception(
                        trans('MediaManager::messages.error.already_exists')
                    );
                }
            } catch (Exception $e) {
                $result[]  = [
                    'success' => false,
                    'message' => "\"$old_path\" " . $e->getMessage(),
                ];
            }
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'    => 'move',
            'path'  => $destination,
        ]))->toOthers();

        return response()->json($result);
    }
}
