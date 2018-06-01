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
    protected function getItemPath($path)
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

    protected function getItemTime($time)
    {
        return $this->carbon->createFromTimestamp($time)->{$this->LMF}();
    }

    /**
     * resolve url for "file/dir path" instead of laravel builtIn.
     * which needs to make extra call just to resolve the url.
     *
     * @param [type] $path [description]
     *
     * @return [type] [description]
     */
    protected function resolveUrl($path)
    {
        return $this->clearDblSlash("{$this->baseUrl}/{$path}");
    }

    protected function clearUrl($path)
    {
        return str_replace($this->baseUrl, '', $path);
    }

    protected function clearDblSlash($str)
    {
        $str = preg_replace('/\/+/', '/', $str);
        $str = str_replace(':/', '://', $str);

        return $str;
    }

    /**
     * save file to disk.
     *
     * @param [type] $item        [description]
     * @param [type] $upload_path [description]
     * @param [type] $file_name   [description]
     *
     * @return file path
     */
    protected function storeFile($item, $upload_path, $file_name)
    {
        return $item->storeAs($upload_path, $file_name, $this->fileSystem);
    }

    /**
     * allow/disallow user upload.
     *
     * @param [type] $file [raw uploaded file]
     *
     * @return [boolean] [description]
     */
    protected function allowUpload($file = null)
    {
        return true;
    }

    /**
     * zip ops.
     *
     * @param mixed $name
     * @param mixed $list
     * @param mixed $type
     * @param mixed $id
     */
    protected function zipAndDownload($name, $id, $list, $type)
    {
        return response()->stream(function () use ($name, $list, $type, $id) {
            // track changes
            $cacheName  = "$name-$id";
            $counter    = 100 / count($list);
            $store      = $this->zipCacheStore;
            $store->forever("$cacheName.progress", 0);

            // name duplication
            $names = [];
            $order = 0;

            $zip = new ZipStream("$name.zip", [
                'content_type' => 'application/octet-stream',
            ]);

            foreach ($list as $file) {
                if ($type == 'folder') {
                    $file_name = pathinfo($file, PATHINFO_BASENAME);
                    $streamRead = $this->storageDisk->readStream($file);

                    // check if file name was used b4
                    $name_only = pathinfo($file, PATHINFO_FILENAME);
                    $ext_only  = pathinfo($file, PATHINFO_EXTENSION);

                    if (in_array($file_name, $names)) {
                        ++$order;
                        $file_name = "{$name_only}_{$order}.{$ext_only}";
                    } else {
                        $names[] = $file_name;
                    }
                } else {
                    $file_name = $file['name'];
                    $streamRead = $this->storageDisk->readStream($this->clearUrl($file['path']));
                }

                // add to zip
                if ($streamRead) {
                    $store->increment("$cacheName.progress", round($counter, 2));
                    $zip->addFileFromStream($file_name, $streamRead);
                } else {
                    $store->forever("$cacheName.warn", $file_name);
                }
            }

            $store->forever("$cacheName.done", true);
            $zip->finish();
        });
    }

    protected function SSE_msg($data = null, $event = null)
    {
        echo $event
            ? "event: $event\n"
            : ':';

        echo $data
            ? 'data: ' . json_encode(['response' => $data]) . "\n\n"
            : ':';
    }

    protected function clearZipCache($store, $item)
    {
        $store->forget("$item.progress");
        $store->forget("$item.warn");
        $store->forget("$item.done");
    }
}
