<?php

namespace ctf0\MediaManager\Controllers\Moduels;

use Exception;
use Illuminate\Http\Request;
use ctf0\MediaManager\Events\MediaFileOpsNotifications;

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
        $path        = $request->path;
        $copy        = $request->use_copy;
        $result      = [];
        $broadcast   = true;
        $toBroadCast = [];

        foreach ($request->moved_files as $one) {
            $file_name = $one['name'];
            $file_type = $one['type'];
            $defaults  = [
                'name'  => $file_name,
                'type'  => $file_type,
                'size'  => $one['size'],
                'items' => $one['items'] ?? 0,
            ];

            $destination = "{$request->destination}/$file_name";
            $old_path    = !$path ? $file_name : $this->clearDblSlash("$path/$file_name");
            $new_path    = $destination == '../'
                                ? '/' . pathinfo($path, PATHINFO_DIRNAME) . '/' . str_replace('../', '', $destination)
                                : "$path/$destination";

            $pattern = [
                '/[[:alnum:]]+\/\.\.\/\//' => '',
                '/\/\//'                   => '/',
            ];
            $new_path = preg_replace(array_keys($pattern), array_values($pattern), $new_path);

            try {
                if (!file_exists($new_path)) {
                    // copy
                    if ($copy) {
                        // folders
                        if ($file_type == 'folder') {
                            $old = $this->getItemPath($old_path);
                            $new = $this->getItemPath($new_path);

                            if (app('files')->copyDirectory($old, $new)) {
                                $result[] = array_merge($defaults, ['success' => true]);
                            } else {
                                $exc = isset($this->storageDiskInfo['root'])
                                        ? trans('MediaManager::messages.error.moving')
                                        : trans('MediaManager::messages.error.moving_cloud');

                                throw new Exception($exc);
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
                            $result[] = array_merge($defaults, ['success' => true]);

                            $toBroadCast[] = $defaults;

                            // fire event
                            event('MMFileMoved', [
                                'old_path' => $this->getItemPath($old_path),
                                'new_path' => $this->getItemPath($new_path),
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
                $broadcast = false;
                $result[]  = [
                    'success' => false,
                    'message' => "\"$old_path\" " . $e->getMessage(),
                ];
            }
        }

        // broadcast
        if ($broadcast) {
            broadcast(new MediaFileOpsNotifications([
                'op'    => 'move',
                'items' => $toBroadCast,
                'path'  => [
                    'current' => $path,
                    'old'     => pathinfo($old_path, PATHINFO_DIRNAME),
                    'new'     => pathinfo($new_path, PATHINFO_DIRNAME),
                ],
            ]))->toOthers();
        }

        return response()->json($result);
    }
}
