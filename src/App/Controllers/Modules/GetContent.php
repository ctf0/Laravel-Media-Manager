<?php

namespace ctf0\MediaManager\App\Controllers\Moduels;

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
        $path = $request->path == '/' ? '' : $request->path;

        if ($path && !$this->storageDisk->exists($path)) {
            return response()->json([
                'error' => trans('MediaManager::messages.error.doesnt_exist', ['attr' => $path]),
            ]);
        }

        return response()->json(
            array_merge(
                $this->lockList(),
                [
                    'files'  => [
                        'path'  => $path,
                        'items' => $this->paginate($this->getData($path), $this->paginationAmount),
                    ],
                ]
            )
        );
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
        $storageFolders = $this->getFolderListByType($dirList, 'dir');
        $storageFiles   = $this->getFolderListByType($dirList, 'file');
        $pattern        = $this->ignoreFiles;

        // folders
        foreach ($storageFolders as $folder) {
            $path = $folder['path'];
            $time = $folder['timestamp'];

            if (!preg_grep($pattern, [$path])) {
                if ($this->GFI) {
                    $info = $this->getFolderInfoFromList($this->getFolderContent($path, true));
                }

                $list[] = [
                    'name'                   => $folder['basename'],
                    'type'                   => 'folder',
                    'path'                   => $this->resolveUrl($path),
                    'storage_path'           => $path,
                    'size'                   => isset($info) ? $info['size'] : 0,
                    'count'                  => isset($info) ? $info['count'] : 0,
                    'last_modified'          => $time,
                    'last_modified_formated' => $this->getItemTime($time),
                ];
            }
        }

        // files
        foreach ($storageFiles as $file) {
            $path = $file['path'];
            $time = $file['timestamp'];

            if (!preg_grep($pattern, [$path])) {
                $list[] = [
                    'name'                   => $file['basename'],
                    'type'                   => $file['mimetype'],
                    'path'                   => $this->resolveUrl($path),
                    'storage_path'           => $path,
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
     * get directory data.
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

    /**
     * filter directory data by type.
     *
     * @param [type] $list
     * @param [type] $type
     */
    protected function getFolderListByType($list, $type)
    {
        $list   = collect($list)->where('type', $type);
        $sortBy = $list->pluck('basename')->values()->all();
        $items  = $list->values()->all();

        array_multisort($sortBy, SORT_NATURAL, $items);

        return $items;
    }

    /**
     * get folder size.
     *
     * @param [type] $list
     */
    protected function getFolderInfoFromList($list)
    {
        $list = collect($list)->where('type', 'file');

        return [
            'count' => $list->count(),
            'size'  => $list->pluck('size')->sum(),
        ];
    }
}
