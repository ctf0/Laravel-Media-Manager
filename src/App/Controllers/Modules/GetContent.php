<?php

namespace ctf0\MediaManager\App\Controllers\Modules;

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
                    'files' => [
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
        $storageFolders = array_filter($this->getFolderListByType($dirList, 'dir'), [$this, 'ignoreFiles']);
        $storageFiles   = array_filter($this->getFolderListByType($dirList, 'file'), [$this, 'ignoreFiles']);

        // folders
        foreach ($storageFolders as $folder) {
            $path = $folder['path'];
            $time = $folder['timestamp'] ?? null;
            $info = $this->GFI ? $this->getFolderInfoFromList($this->getFolderContent($path, true)) : [];

            $list[] = [
                'name'                   => $folder['basename'],
                'type'                   => 'folder',
                'size'                   => $info['size'] ?? 0,
                'count'                  => $info['count'] ?? 0,
                'path'                   => $this->resolveUrl($path),
                'storage_path'           => $path,
                'last_modified'          => $time,
                'last_modified_formated' => $this->getItemTime($time),
            ];
        }

        // files
        foreach ($storageFiles as $file) {
            $path = $file['path'];
            $time = $file['timestamp'] ?? null;

            $list[] = [
                'name'                   => $file['basename'],
                'type'                   => $file['mimetype'],
                'size'                   => $file['size'],
                'visibility'             => $file['visibility'],
                'path'                   => $this->resolveUrl($path),
                'storage_path'           => $path,
                'last_modified'          => $time,
                'last_modified_formated' => $this->getItemTime($time),
            ];
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
        return $this->storageDisk->listContents($folder, $rec)->map(function ($attributes) {
            $path = $attributes->path();
            $name = substr($path, strrpos($path, '/'));
            $extension = substr($name, strrpos($name, '.') + 1);
            return [
                'path' => $path,
                'dirname' => str_replace("/$name", '', $path),
                'basename' => $name,
                'extension' => $extension,
                'filename' => substr($name, 0, strrpos($name, '.')),
                'timestamp' => $attributes->lastModified(),
                'size' => $attributes->isFile() ? $attributes->fileSize() : 0,
                'type' => $attributes->isFile() ? 'file' : 'dir',
                'mimetype' => $attributes->isFile() ? ($this->storageDisk->mimeType($path)) : 'folder',
                'visibility' => $attributes->visibility()
            ];
        })->toArray();
    }

    protected function ignoreFiles($item)
    {
        return !preg_grep($this->ignoreFiles, [$item['basename']]);
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
