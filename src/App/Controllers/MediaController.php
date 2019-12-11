<?php

namespace ctf0\MediaManager\App\Controllers;

use App\Http\Controllers\Controller;
use Jhofm\FlysystemIterator\Plugin\IteratorPlugin;
use ctf0\MediaManager\App\Controllers\Moduels\Lock;
use ctf0\MediaManager\App\Controllers\Moduels\Move;
use ctf0\MediaManager\App\Controllers\Moduels\Utils;
use ctf0\MediaManager\App\Controllers\Moduels\Delete;
use ctf0\MediaManager\App\Controllers\Moduels\Rename;
use ctf0\MediaManager\App\Controllers\Moduels\Upload;
use ctf0\MediaManager\App\Controllers\Moduels\Download;
use ctf0\MediaManager\App\Controllers\Moduels\NewFolder;
use ctf0\MediaManager\App\Controllers\Moduels\GetContent;
use ctf0\MediaManager\App\Controllers\Moduels\Visibility;
use ctf0\MediaManager\App\Controllers\Moduels\GlobalSearch;

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
        Visibility,
        GlobalSearch;

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

        $this->fileSystem           = $config['storage_disk'];
        $this->ignoreFiles          = $config['ignore_files'];
        $this->fileChars            = $config['allowed_fileNames_chars'];
        $this->folderChars          = $config['allowed_folderNames_chars'];
        $this->sanitizedText        = $config['sanitized_text'];
        $this->unallowedMimes       = $config['unallowed_mimes'];
        $this->LMF                  = $config['last_modified_format'];
        $this->GFI                  = $config['get_folder_info']   ?? true;
        $this->paginationAmount     = $config['pagination_amount'] ?? 50;

        $this->storageDisk     = app('filesystem')->disk($this->fileSystem);
        $this->storageDiskInfo = app('config')->get("filesystems.disks.{$this->fileSystem}");
        $this->baseUrl         = $this->storageDisk->url('/');
        $this->db              = app('db')
                                    ->connection($config['database_connection'] ?? 'mediamanager')
                                    ->table($config['table_locked'] ?? 'locked');

        $this->storageDisk->addPlugin(new IteratorPlugin());
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
}
