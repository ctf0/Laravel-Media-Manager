<?php

namespace ctf0\MediaManager\Controllers;

use Illuminate\Http\Request;
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

    protected $config;
    protected $baseUrl;
    protected $db;
    protected $fileChars;
    protected $fileSystem;
    protected $folderChars;
    protected $ignoreFiles;
    protected $LMF;
    protected $sanitizedText;
    protected $storageDisk;
    protected $storageDiskInfo;
    protected $unallowedMimes;

    public function __construct()
    {
        $this->config          = config('mediaManager');
        $this->fileSystem      = array_get($this->config, 'storage_disk');
        $this->storageDisk     = app('filesystem')->disk($this->fileSystem);
        $this->baseUrl         = $this->storageDisk->url('/');
        $this->ignoreFiles     = array_get($this->config, 'ignore_files');
        $this->fileChars       = array_get($this->config, 'allowed_fileNames_chars');
        $this->folderChars     = array_get($this->config, 'allowed_folderNames_chars');
        $this->sanitizedText   = array_get($this->config, 'sanitized_text');
        $this->unallowedMimes  = array_get($this->config, 'unallowed_mimes');
        $this->LMF             = array_get($this->config, 'last_modified_format');
        $this->db              = app('db')->connection('mediamanager')->table('locked');
        $this->storageDiskInfo = config("filesystems.disks.{$this->fileSystem}");

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

    /**
     * globalSearch all the manager files
     * TODO "better use caching"
     * @return [type] [description]
     */
    public function globalSearch(Request $request)
    {
        return
    }
}
