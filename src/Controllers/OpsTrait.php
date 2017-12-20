<?php

namespace ctf0\MediaManager\Controllers;

use Carbon\Carbon;

trait OpsTrait
{
    /**
     * [getFiles description].
     *
     * @param [type] $dir [description]
     *
     * @return [type] [description]
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
     * @param [type] $folder [description]
     *
     * @return [type] [description]
     */
    protected function folderCount($folder)
    {
        // files + directories count
        // return count($this->folderFiles($folder)) + count($this->storageDisk->allDirectories($folder));

        // files only
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
        $disks = $this->disks;
        $url   = $this->storageDisk->url($name);
        $dir   = str_replace(array_get($disks, 'url'), '', $url);
        $root  = array_get($disks, 'root');

        // for other disks without root ex."cloud"
        if (!$root) {
            return preg_replace('/(.*\/\/.*?)\//', '', $url);
        }

        return $root . $dir;
    }
}
