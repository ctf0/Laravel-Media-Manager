<?php

namespace ctf0\MediaManager\App\Controllers\Modules;

trait GlobalSearch
{
    public function globalSearch()
    {
        return collect($this->getFolderContent('/', true))
                ->reject(function ($item) { // remove unwanted & dirs
                    return preg_grep($this->ignoreFiles, [$item['path']]) || $item['type'] == 'dir';
                })->map(function ($file) {
                    $path = $file['path'];
                    $time = $file['timestamp'];

                    return $file = [
                        'name'                   => $file['basename'],
                        'type'                   => $file['mimetype'],
                        'path'                   => $this->resolveUrl($path),
                        'dir_path'               => $file['dirname'] ?: '/',
                        'storage_path'           => $path,
                        'size'                   => $file['size'],
                        'last_modified'          => $time,
                        'last_modified_formated' => $this->getItemTime($time),
                    ];
                })->values()->all();
    }
}
