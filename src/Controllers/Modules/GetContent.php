<?php

namespace ctf0\MediaManager\Controllers\Moduels;

use Illuminate\Http\Request;

trait GetContent
{
    /**
     * get files in path.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function getFiles(Request $request)
    {
        $folder = $request->folder != '/' ? $request->folder : '';

        if ($folder && !$this->storageDisk->exists($folder)) {
            return response()->json([
                'error' => trans('MediaManager::messages.error.doesnt_exist', ['attr' => $folder]),
            ]);
        }

        return response()->json([
            'locked' => $this->db->pluck('path'),
            'dirs'   => $this->getDirectoriesList($request->dirs),
            'files'  => [
                'path'  => $folder,
                'items' => $this->getData($folder),
            ],
        ]);
    }

    /**
     * get all directories in path.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function getFolders(Request $request)
    {
        return response()->json($this->getDirectoriesList($request->folder_location));
    }

    /**
     * get files list.
     *
     * @param mixed $dir
     */
    protected function getData($dir)
    {
        $list           = [];
        $dirList        = $this->getFolderContent($dir);
        $storageFiles   = $this->getFolderListByType($dirList, 'file');
        $storageFolders = $this->getFolderListByType($dirList, 'dir');
        $pattern        = $this->ignoreFiles;

        foreach ($storageFolders as $folder) {
            $path = $folder['path'];
            $time = $folder['timestamp'];

            if (!preg_grep($pattern, [$path])) {
                if ($this->GFI) {
                    $info = $this->getFolderInfo($path);
                }

                $list[] = [
                    'name'                   => $folder['basename'],
                    'type'                   => 'folder',
                    'path'                   => $this->resolveUrl($path),
                    'size'                   => isset($info) ? $info['size'] : 0,
                    'count'                  => isset($info) ? $info['count'] : 0,
                    'last_modified'          => $time,
                    'last_modified_formated' => $this->getItemTime($time),
                ];
            }
        }

        foreach ($storageFiles as $file) {
            $path = $file['path'];
            $time = $file['timestamp'];

            if (!preg_grep($pattern, [$path])) {
                $list[] = [
                    'name'                   => $file['basename'],
                    'type'                   => $file['mimetype'],
                    'path'                   => $this->resolveUrl($path),
                    'size'                   => $file['size'],
                    'visibility'             => $file['visibility'],
                    'last_modified'          => $time,
                    'last_modified_formated' => $this->getItemTime($time),
                ];
            }
        }

        return $list;
    }

    /**
     * helpers for folder ops.
     *
     * @param mixed $folder
     * @param mixed $rec
     */
    protected function getFolderContent($folder, $rec = false)
    {
        return $this->storageDisk->listWith(
            ['mimetype', 'visibility', 'timestamp', 'size'],
            $folder,
            $rec
        );
    }

    protected function getFolderInfo($folder)
    {
        return $this->getFolderInfoFromList(
            $this->getFolderContent($folder, true)
        );
    }

    protected function getFolderInfoFromList($list)
    {
        $list = collect($list)->where('type', 'file');

        return [
            'count' => $list->count(),
            'size'  => $list->pluck('size')->sum(),
        ];
    }

    protected function getFolderListByType($list, $type)
    {
        $list   = collect($list)->where('type', $type);
        $sortBy = $list->pluck('basename')->values()->all();
        $items  = $list->values()->all();

        array_multisort($sortBy, SORT_NATURAL, $items);

        return $items;
    }

    protected function getDirectoriesList($location)
    {
        if (is_array($location)) {
            $location = rtrim(implode('/', $location), '/');
        }

        return str_replace($location, '', $this->storageDisk->allDirectories($location));
    }
}
