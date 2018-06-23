<?php

namespace ctf0\MediaManager\Controllers\Moduels;

use Illuminate\Http\Request;
use ctf0\MediaManager\Events\MediaFileOpsNotifications;

trait Lock
{
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
        $removed  = [];
        $added    = [];

        foreach ($request->list as $item) {
            $url  = $item['path'];
            $type = $item['type'];
            $name = $item['name'];

            if (in_array($url, $lockedList)) {
                $toRemove[] = $url;
                $removed[]  = compact('url', 'type', 'name');
            } else {
                $toAdd[] = ['path'=>$url];
                $added[] = compact('url', 'type', 'name');
            }

            $result[] = [
                'message' => trans('MediaManager::messages.lock_success', ['attr' => $item['name']]),
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
            'removed' => $removed,
            'added'   => $added,
        ]))->toOthers();

        return response()->json(compact('result', 'removed', 'added'));
    }
}
