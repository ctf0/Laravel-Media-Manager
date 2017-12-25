<?php

namespace ctf0\MediaManager\Controllers;

use Carbon\Carbon;
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
        $files          = [];
        $storageFiles   = $this->storageDisk->files($dir);
        $storageFolders = $this->storageDisk->directories($dir);
        $pattern        = $this->ignoreFiles;

        foreach ($storageFolders as $folder) {
            if (!preg_grep($pattern, [$folder])) {
                $time    = $this->storageDisk->lastModified($folder);
                $files[] = [
                    'name'                   => pathinfo($folder, PATHINFO_BASENAME),
                    'type'                   => 'folder',
                    'path'                   => $this->storageDisk->url($folder),
                    'size'                   => $this->folderSize($folder),
                    'items'                  => $this->folderCount($folder),
                    'last_modified'          => $time,
                    'last_modified_formated' => Carbon::createFromTimestamp($time)->{$this->LMF}(),
                ];
            }
        }

        foreach ($storageFiles as $file) {
            if (!preg_grep($pattern, [$file])) {
                $time    = $this->storageDisk->lastModified($file);
                $files[] = [
                    'name'                   => pathinfo($file, PATHINFO_BASENAME),
                    'type'                   => $this->storageDisk->mimeType($file),
                    'path'                   => $this->storageDisk->url($file),
                    'size'                   => $this->storageDisk->size($file),
                    'last_modified'          => $time,
                    'last_modified_formated' => Carbon::createFromTimestamp($time)->{$this->LMF}(),
                ];
            }
        }

        return $files;
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
     * sanitize input.
     *
     * @param [type]     $text   [description]
     * @param null|mixed $folder
     *
     * @return [type] [description]
     */
    protected function cleanName($text, $folder = null)
    {
        $pattern = $this->filePattern($this->fileChars);

        if ($folder) {
            $pattern = $this->filePattern($this->folderChars);
        }

        $text = preg_replace($pattern, '', $text);

        return '' == $text ? $this->sanitizedText : $text;
    }

    protected function filePattern($item)
    {
        return '/(script.*?\/script)|[^(' . $item . ')a-zA-Z0-9]|\(|\)+/ius';
    }

    /**
     * helpers for folder ops.
     *
     * @param mixed $folder
     */
    protected function folderCount($folder)
    {
        return count($this->folderFiles($folder));
    }

    protected function folderSize($folder)
    {
        $file_size = 0;

        foreach ($this->folderFiles($folder) as $file) {
            $file_size += $this->storageDisk->size($file);
        }

        return $file_size;
    }

    protected function folderFiles($folder)
    {
        return $this->storageDisk->allFiles($folder);
    }

    /**
     * get file path from storage.
     *
     * @param [type] $disk [description]
     * @param [type] $name [description]
     *
     * @return [type] [description]
     */
    protected function getFilePath($name)
    {
        $info = $this->storageDiskInfo;
        $url  = $this->storageDisk->url($name);
        $dir  = str_replace(array_get($info, 'url'), '', $url);
        $root = array_get($info, 'root');

        // for other disks without root ex."cloud"
        if (!$root) {
            return preg_replace('/(.*\/\/.*?)\//', '', $url);
        }

        return $root . $dir;
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
        $store      = $this->cacheStore;
        $store->forever("$name.progress", 0);

        return response()->stream(function () use ($name, $list, $type, $counter, $store) {
            $zip = new ZipStream("$name.zip", [
                'content_type' => 'application/octet-stream',
            ]);

            foreach ($list as $file) {
                if ('folder' == $type) {
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
