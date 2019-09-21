<?php

namespace ctf0\MediaManager\Controllers\Moduels;

use Carbon\Carbon;

trait Utils
{
    /**
     * sanitize input.
     *
     * @return [type] [description]
     */
    protected function getRandomString()
    {
        return call_user_func($this->sanitizedText);
    }

    protected function cleanName($text, $folder = false)
    {
        $pattern = $this->filePattern($folder ? $this->folderChars : $this->fileChars);
        $text    = preg_replace($pattern, '', $text);

        return $text ?: $this->getRandomString();
    }

    protected function filePattern($item)
    {
        return '/(script.*?\/script)|[^(' . $item . ')a-zA-Z0-9]+/ius';
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
        $url  = $this->resolveUrl($path); // get the file url
        $root = $info['root'] ?? null;

        // for other disks without root ex."cloud"
        if (!$root) {
            return preg_replace('/(.*\/\/.*?)\//', '', $url); // get the full path
        }

        $dir = str_replace($info['url'], '', $url); // remove the uri

        return $root . $dir; // get the full path
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
