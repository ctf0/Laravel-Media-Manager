<?php

namespace ctf0\MediaManager\Controllers\Moduels;

use Exception;
use Illuminate\Http\Request;
use ctf0\MediaManager\Events\MediaFileOpsNotifications;

trait Rename
{
    /**
     * rename item.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function renameItem(Request $request)
    {
        $path         = $request->path;
        $filename     = $request->filename;
        $new_filename = $this->cleanName($request->new_filename);
        $message      = '';

        $old_path = !$path ? $filename : $this->clearDblSlash("$path/$filename");
        $new_path = !$path ? $new_filename : $this->clearDblSlash("$path/$new_filename");

        try {
            if (!$this->storageDisk->exists($new_path)) {
                if ($this->storageDisk->move($old_path, $new_path)) {
                    // broadcast
                    broadcast(new MediaFileOpsNotifications([
                        'op'   => 'rename',
                        'path' => $path,
                        'item' => [
                            'type'    => $request->type,
                            'oldName' => $filename,
                            'newName' => $new_filename,
                        ],
                    ]))->toOthers();

                    // fire event
                    event('MMFileRenamed', [
                        'old_path' => $this->getItemPath($old_path),
                        'new_path' => $this->getItemPath($new_path),
                    ]);
                } else {
                    throw new Exception(
                        trans('MediaManager::messages.error.moving')
                    );
                }
            } else {
                throw new Exception(
                    trans('MediaManager::messages.error.already_exists')
                );
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return compact('message', 'new_filename');
    }
}
