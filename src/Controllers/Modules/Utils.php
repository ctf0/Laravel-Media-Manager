<?php

namespace ctf0\MediaManager\Controllers\Moduels;

use Carbon\Carbon;

trait Utils
{
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
        return Carbon::createFromTimestamp($time)->{$this->LMF}();
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
}
