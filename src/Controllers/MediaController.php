<?php

namespace ctf0\MediaManager\Controllers;

use App\Http\Controllers\Controller;
use League\Flysystem\Plugin\ListWith;
use ctf0\MediaManager\Controllers\Moduels\Lock;
use ctf0\MediaManager\Controllers\Moduels\Move;
use ctf0\MediaManager\Controllers\Moduels\Utils;
use ctf0\MediaManager\Controllers\Moduels\Delete;
use ctf0\MediaManager\Controllers\Moduels\Rename;
use ctf0\MediaManager\Controllers\Moduels\Upload;
use ctf0\MediaManager\Controllers\Moduels\Download;
use ctf0\MediaManager\Controllers\Moduels\NewFolder;
use ctf0\MediaManager\Controllers\Moduels\GetContent;
use ctf0\MediaManager\Controllers\Moduels\Visibility;

class MediaController extends Controller
{
    use Utils,
        GetContent,
        Delete,
        Download,
        Lock,
        Move,
        Rename,
        Upload,
        NewFolder,
        Visibility;

    protected $baseUrl;
    protected $db;
    protected $fileChars;
    protected $fileSystem;
    protected $folderChars;
    protected $ignoreFiles;
    protected $LMF;
    protected $GFI;
    protected $sanitizedText;
    protected $storageDisk;
    protected $storageDiskInfo;
    protected $unallowedMimes;

    public function __construct()
    {
        $config = app('config')->get('mediaManager');

        $this->fileSystem     = $config['storage_disk'];
        $this->ignoreFiles    = $config['ignore_files'];
        $this->fileChars      = $config['allowed_fileNames_chars'];
        $this->folderChars    = $config['allowed_folderNames_chars'];
        $this->sanitizedText  = $config['sanitized_text'];
        $this->unallowedMimes = $config['unallowed_mimes'];
        $this->LMF            = $config['last_modified_format'];
        $this->GFI            = $config['get_folder_info'] ?? true;

        $this->storageDisk     = app('filesystem')->disk($this->fileSystem);
        $this->storageDiskInfo = app('config')->get("filesystems.disks.{$this->fileSystem}");
        $this->baseUrl         = $this->storageDisk->url('/');
        $this->db              = app('db')->connection('mediamanager')->table('locked');

        $this->storageDisk->addPlugin(new ListWith());
    }

    /**
     * main view.
     *
     * @return [type] [description]
     */
    public function index()
    {
        return view('MediaManager::media');
    }

    public function globalSearch()
    {
        return collect($this->getFolderContent('/', true))->reject(function ($item) { // remove unwanted
            return preg_grep($this->ignoreFiles, [$item['path']]) || $item['type'] == 'dir';
        })->map(function ($file) {
            return $file = [
                'name'                   => $file['basename'],
                'type'                   => $file['mimetype'],
                'path'                   => $this->resolveUrl($file['path']),
                'dir'                    => $file['dirname'] != '' ? $file['dirname'] : '/',
                'last_modified_formated' => $this->getItemTime($file['timestamp']),
            ];
        })->values()->all();
    }
}
