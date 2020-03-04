<?php

namespace ctf0\MediaManager\App\Controllers\Modules;

use Illuminate\Http\Request;
use ctf0\MediaManager\App\Events\MediaFileOpsNotifications;

trait Lock
{
    /**
     * get locked items & directories list.
     *
     * @param [type] $dirs
     */
    public function getLockList()
    {
        return response()->json($this->lockList());
    }

    /**
     * get data.
     *
     * @param [type] $path
     */
    public function lockList()
    {
        return [
            'locked' => $this->db->pluck('path'),
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
            'op' => 'lock',
        ]))->toOthers();

        return compact('result');
    }
}
