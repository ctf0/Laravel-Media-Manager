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

        $result     = [];
        $removed    = [];
        $added      = [];

        foreach ($request->list as $item) {
            $url = $item['path'];

            if (in_array($url, $lockedList)) {
                // for some reason we cant delete the items one by one, probably related to sqlite
                $removed[] = $url;
            } else {
                $added[] = $url;
                $this->db->insert(['path' => $url]);
            }

            $result[] = [
                'message' => trans('MediaManager::messages.lock_success', ['attr' => $item['name']]),
            ];
        }

        if ($removed) {
            $this->db->whereIn('path', $removed)->delete();
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
