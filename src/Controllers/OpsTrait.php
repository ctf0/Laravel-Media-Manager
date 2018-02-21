<?php

namespace ctf0\MediaManager\Controllers;

use ZipStream\ZipStream;

trait OpsTrait
{
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
                    'last_modified_formated' => $this->getFileTime($time),
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
                    'last_modified_formated' => $this->getFileTime($time),
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
        return $this->storageDisk->listWith(['mimetype', 'visibility'], $folder, $rec);
    }

    protected function getFolderListByType($list, $type)
    {
        return array_filter($list, function ($item) use ($type) {
            return $item['type'] == $type;
        });
    }

    protected function getFolderInfo($folder)
    {
        $files = $this->getFolderContent($folder, true);
        $count = 0;
        $size  = 0;

        foreach ($files as $file) {
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

    protected function dirsList($location)
    {
        if (is_array($location)) {
            $location = rtrim(implode('/', $location), '/');
        }

        return str_replace($location, '', $this->storageDisk->allDirectories($location));
    }

    /**
     * sanitize input.
     *
     * @param [type]     $text   [description]
     * @param null|mixed $folder
     *
     * @return [type] [description]
     */
    protected function cleanName($text, $folder = null)
    {
        $pattern = $folder
            ? $this->filePattern($this->folderChars)
            : $this->filePattern($this->fileChars);

        $text = preg_replace($pattern, '', $text);

        return $text == '' ? $this->sanitizedText : $text;
    }

    protected function filePattern($item)
    {
        return '/(script.*?\/script)|[^(' . $item . ')a-zA-Z0-9]|\(|\)+/ius';
    }

    /**
     * get file path from storage.
     *
     * @param [type] $path [description]
     *
     * @return [type] [description]
     */
    protected function getFilePath($path)
    {
        $info = $this->storageDiskInfo;
        $url  = $this->resolveUrl($path);
        $root = array_get($info, 'root');

        // for other disks without root ex."cloud"
        if (!$root) {
            return preg_replace('/(.*\/\/.*?)\//', '', $url);
        }

        $dir = str_replace(array_get($info, 'url'), '', $url);

        return $root . $dir;
    }

    protected function getFileTime($time)
    {
        return $this->carbon->createFromTimestamp($time)->{$this->LMF}();
    }

    /**
     * resolve url for "file/dir path" instead of laravel builtIn.
     *
     * laravel builtIn needs to make extra call just to resolve the url
     *
     * @param [type] $path [description]
     *
     * @return [type] [description]
     */
    protected function resolveUrl($path)
    {
        return preg_replace('/\/+$/', '/', $this->baseUrl) . $path;
    }

    /**
     * save file to disk.
     *
     * @param [type] $item        [description]
     * @param [type] $upload_path [description]
     * @param [type] $file_name   [description]
     *
     * @return [type] [description]
     */
    protected function storeFile($item, $upload_path, $file_name)
    {
        return $item->storeAs($upload_path, $file_name, $this->fileSystem);
    }

    /**
     * zip ops.
     *
     * @param mixed $name
     * @param mixed $list
     * @param mixed $type
     */
    protected function download($name, $list, $type)
    {
        // track changes
        $counter    = 100 / count($list);
        $store      = $this->zipCacheStore;
        $store->forever("$name.progress", 0);

        return response()->stream(function () use ($name, $list, $type, $counter, $store) {
            $zip = new ZipStream("$name.zip", [
                'content_type' => 'application/octet-stream',
            ]);

            foreach ($list as $file) {
                if ($type == 'folder') {
                    $file_name = pathinfo($file, PATHINFO_BASENAME);
                    $streamRead = $this->storageDisk->readStream($file);
                } else {
                    $file_name = $file['name'];
                    $streamRead = @fopen($file['path'], 'r');
                }

                if ($streamRead) {
                    $store->increment("$name.progress", round($counter, 2));
                    $zip->addFileFromStream($file_name, $streamRead);
                } else {
                    $store->forever("$name.warn", $file_name);
                }
            }

            $store->forever("$name.done", true);
            $zip->finish();
        });
    }

    protected function SSE_msg($data = null, $event = null)
    {
        echo $event ? "event: $event\n" : ':';
        echo $data ? 'data: ' . json_encode(['response' => $data]) . "\n\n" : ':';
    }

    protected function clearZipCache($store, $item)
    {
        $store->forget("$item.progress");
        $store->forget("$item.done");
        $store->forget("$item.abort");
    }
}
