<?php

namespace ctf0\MediaManager\App\Controllers\Moduels;

use Illuminate\Http\Request;
use ctf0\MediaManager\App\Events\MediaFileOpsNotifications;

trait Lock
{
    /**
     * get locked items & directories list.
     *
     * @param [type] $dirs
     */
    public function getLockList(Request $request)
    {
        return response()->json($this->lockList($request->path));
    }

    /**
     * get data.
     *
     * @param [type] $path
     */
    public function lockList($path)
    {
        return [
            'locked' => $this->db->pluck('path'),
            'dirs'   => $this->getDirectoriesList($path),
        ];
    }

    /**
     * lock/unlock files/folders.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function lockItem(Request $request)
    {
        $path       = $request->path;
        $lockedList = $this->db->pluck('path')->toArray();

        $toRemove = [];
        $toAdd    = [];
        $result   = [];

        foreach ($request->list as $item) {
            $url  = $item['path'];
            $name = $item['name'];

            if (in_array($url, $lockedList)) {
                $toRemove[] = $url;
            } else {
                $toAdd[] = ['path' => $url];
            }

            $result[] = [
                'message' => trans('MediaManager::messages.lock_success', ['attr' => $name]),
            ];
        }

        if ($toRemove) {
            $this->db->whereIn('path', $toRemove)->delete();
        }

        if ($toAdd) {
            $this->db->insert($toAdd);
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'      => 'lock',
            'path'    => $path,
        ]))->toOthers();

        return compact('result');
    }
}
