<?php

namespace ctf0\MediaManager\Controllers;

use ZipStream\ZipStream;
use ctf0\MediaManager\Events\MediaZipProgress;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
     * @param (Symfony\Component\HttpFoundation\File\UploadedFile) $file
     * @param (string)                                             $upload_path [description]
     * @param (string)                                             $file_name   [description]
     *
     * @return file path
     */
    protected function storeFile(UploadedFile $file, $upload_path, $file_name)
    {
        return $file->storeAs($upload_path, $file_name, $this->fileSystem);
    }

    /**
     * allow/disallow user upload.
     *
     * @param (Symfony\Component\HttpFoundation\File\UploadedFile || null) $file
     *
     * @return [boolean]
     */
    protected function allowUpload($file = null)
    {
        return true;
    }

    /**
     * do something to file b4 its saved to the server.
     *
     * @param (Symfony\Component\HttpFoundation\File\UploadedFile) $file
     *
     * @return $file
     */
    protected function optimizeUpload(UploadedFile $file)
    {
        return $file;
    }

    /**
     * zip ops.
     *
     * @param mixed $name
     * @param mixed $list
     * @param mixed $type
     */
    protected function zipAndDownload($name, $list)
    {
        return response()->stream(function () use ($name, $list) {
            // track changes
            $counter = 100 / count($list);
            $progress = 0;
            broadcast(new MediaZipProgress(['progress'=>$progress]));

            $zip = new ZipStream("$name.zip", [
                'content_type' => 'application/octet-stream',
            ]);

            foreach ($list as $file) {
                $file_name = $file['name'];
                $streamRead = $this->storageDisk->readStream($this->clearUrl($file['path']));

                // add to zip
                if ($streamRead) {
                    $progress += $counter;
                    broadcast(new MediaZipProgress(['progress'=>round($progress, 0)]));
                    $zip->addFileFromStream($file_name, $streamRead);
                } else {
                    broadcast(new MediaZipProgress([
                        'msg' => $file_name,
                        'type'=> 'warn',
                    ]));
                }
            }

            broadcast(new MediaZipProgress(['progress'=>100]));
            $zip->finish();
        });
    }

    protected function zipAndDownloadDir($name, $list)
    {
        return response()->stream(function () use ($name, $list) {
            // track changes
            $counter = 100 / count($list);
            $progress = 0;
            broadcast(new MediaZipProgress(['progress'=>$progress]));

            $zip = new ZipStream("$name.zip", [
                'content_type' => 'application/octet-stream',
            ]);

            foreach ($list as $file) {
                $dir_name = pathinfo($file, PATHINFO_DIRNAME);
                $file_name = pathinfo($file, PATHINFO_BASENAME);
                $full_name = "$dir_name/$file_name";
                $streamRead = $this->storageDisk->readStream($file);

                // add to zip
                if ($streamRead) {
                    $progress += $counter;
                    broadcast(new MediaZipProgress(['progress'=>round($progress, 0)]));
                    $zip->addFileFromStream($full_name, $streamRead);
                } else {
                    broadcast(new MediaZipProgress([
                        'msg' => $full_name,
                        'type'=> 'warn',
                    ]));
                }
            }

            broadcast(new MediaZipProgress(['progress'=>100]));
            $zip->finish();
        });
    }
}
