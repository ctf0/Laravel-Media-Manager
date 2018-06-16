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
        $folder = $request->folder !== '/' ? $request->folder : '';

        if ($folder && !$this->storageDisk->exists($folder)) {
            return response()->json([
                'error' => trans('MediaManager::messages.error_doesnt_exist', ['attr' => $folder]),
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
        $folderLocation = $request->folder_location;

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        return response()->json($this->getDirectoriesList($folderLocation));
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
            $name = $folder['basename'];

            if (!preg_grep($pattern, [$path])) {
                $info = $this->getFolderInfo($path);

                $list[] = [
                    'name'                   => $name,
                    'type'                   => 'folder',
                    'path'                   => $this->resolveUrl($path),
                    'size'                   => $info['files_size'],
                    'items'                  => $info['files_count'],
                    'last_modified'          => $time,
                    'last_modified_formated' => $this->getItemTime($time),
                ];
            }
        }

        foreach ($storageFiles as $file) {
            $path       = $file['path'];
            $size       = $file['size'];
            $time       = $file['timestamp'];
            $name       = $file['basename'];
            $visibility = $file['visibility'];

            if (!preg_grep($pattern, [$path])) {
                $mime = $file['mimetype'];

                $list[] = [
                    'name'                   => $name,
                    'type'                   => $mime,
                    'path'                   => $this->resolveUrl($path),
                    'size'                   => $size,
                    'visibility'             => $visibility,
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
            ['mimetype', 'visibility'],
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
        $count = 0;
        $size  = 0;

        foreach ($list as $file) {
            if ($file['type'] == 'file') {
                ++$count;
                $size += $file['size'];
            }
        }

        return [
            'files_count'=> $count, // count($files) == files + folders
            'files_size' => $size,
        ];
    }

    protected function getFolderListByType($list, $type)
    {
        return array_filter($list, function ($item) use ($type) {
            return $item['type'] == $type;
        });
    }

    protected function getDirectoriesList($location)
    {
        if (is_array($location)) {
            $location = rtrim(implode('/', $location), '/');
        }

        return str_replace($location, '', $this->storageDisk->allDirectories($location));
    }
}
