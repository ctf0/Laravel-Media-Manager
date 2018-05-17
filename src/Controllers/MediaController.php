<?php

namespace ctf0\MediaManager\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Flysystem\Plugin\ListWith;

class MediaController extends Controller
{
    use OpsTrait;

    protected $config;
    protected $baseUrl;
    protected $carbon;
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
    protected $zipCacheStore;

    public function __construct(Carbon $carbon)
    {
        $this->carbon          = $carbon;
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
        $this->zipCacheStore   = app('cache')->store('mediamanager');
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
     * get files in path.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function getFiles(Request $request)
    {
        $folder = $request->folder !== '/'
            ? $request->folder
            : '';

        if ($folder && !$this->storageDisk->exists($folder)) {
            return response()->json([
                'error' => trans('MediaManager::messages.error_doesnt_exist', ['attr' => $folder]),
            ]);
        }

        return response()->json([
            'locked' => $this->db->pluck('path'),
            'dirs'   => $this->getDirectoriesList($request->dirs),
            'files'  => [
                'path'  => $folder,
                'items' => $this->getData($folder),
            ],
        ]);
    }

    /**
     * get all directories in path.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function getFolders(Request $request)
    {
        $folderLocation = $request->folder_location;

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        return response()->json($this->getDirectoriesList($folderLocation));
    }

    /**
     * upload new files.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function upload(Request $request)
    {
        $upload_path = $request->upload_path;
        $random_name = $request->random_names;
        $result      = [];

        foreach ($request->file as $one) {
            $original    = $one->getClientOriginalName();
            $name_only   = pathinfo($original, PATHINFO_FILENAME);
            $ext_only    = pathinfo($original, PATHINFO_EXTENSION);
            $file_name   = $random_name
                ? $this->sanitizedText . ".$ext_only"
                : $this->cleanName($name_only, null) . ".$ext_only";

            $file_type   = $one->getMimeType();
            $destination = !$upload_path ? $file_name : $this->clearDblSlash("$upload_path/$file_name");

            try {
                // check for mime type
                if (str_contains($file_type, $this->unallowedMimes)) {
                    throw new Exception(
                        trans('MediaManager::messages.not_allowed_file_ext', ['attr' => $file_type])
                    );
                }

                // check existence
                if ($this->storageDisk->exists($destination)) {
                    throw new Exception(
                        trans('MediaManager::messages.error_already_exists')
                    );
                }

                // save file
                $saved_name = $this->storeFile($one, $upload_path, $file_name);

                // fire event
                event('MMFileUploaded', $this->getItemPath($saved_name));

                $result[] = [
                    'success' => true,
                    'message' => $file_name,
                ];
            } catch (Exception $e) {
                $result[] = [
                    'success' => false,
                    'message' => "\"$file_name\" " . $e->getMessage(),
                ];
            }
        }

        return response()->json($result);
    }

    /**
     * save cropped image.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function uploadEditedImage(Request $request)
    {
        $path        = $request->path;
        $data        = explode(',', $request->data)[1];
        $original    = $request->name;
        $name_only   = pathinfo($original, PATHINFO_FILENAME) . '_' . $this->sanitizedText;
        $ext_only    = pathinfo($original, PATHINFO_EXTENSION);
        $file_name   = "$name_only.$ext_only";
        $destination = !$path ? $file_name : $this->clearDblSlash("$path/$file_name");

        try {
            // check existence
            if ($this->storageDisk->exists($destination)) {
                throw new Exception(
                    trans('MediaManager::messages.error_already_exists')
                );
            }

            // save file
            $this->storageDisk->put($destination, base64_decode($data));

            // fire event
            event('MMFileSaved', $this->getItemPath($destination));

            $result = [
                'success' => true,
                'message' => $file_name,
            ];
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => "\"$file_name\" " . $e->getMessage(),
            ];
        }

        return response()->json($result);
    }

    /**
     * save image from link.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function uploadLink(Request $request)
    {
        $url         = $request->url;
        $path        = $request->path;
        $random_name = $request->random_names;

        $original    = substr($url, strrpos($url, '/') + 1);
        $name_only   = pathinfo($original, PATHINFO_FILENAME);
        $ext_only    = pathinfo($original, PATHINFO_EXTENSION);
        $file_name   = $random_name
            ? $this->sanitizedText . ".$ext_only"
            : $this->cleanName($name_only, null) . ".$ext_only";

        $destination = !$path ? $file_name : $this->clearDblSlash("$path/$file_name");
        $file_type   = image_type_to_mime_type(exif_imagetype($url));

        try {
            // check for mime type
            if (str_contains($file_type, $this->unallowedMimes)) {
                throw new Exception(
                    trans('MediaManager::messages.not_allowed_file_ext', ['attr' => $file_type])
                );
            }

            // check existence
            if ($this->storageDisk->exists($destination)) {
                throw new Exception(
                    trans('MediaManager::messages.error_already_exists')
                );
            }

            // save file
            $this->storageDisk->put($destination, file_get_contents($url));

            // fire event
            event('MMFileSaved', $this->getItemPath($destination));

            $result = [
                'success' => true,
                'message' => $file_name,
            ];
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => "\"$file_name\" " . $e->getMessage(),
            ];
        }

        return response()->json($result);
    }

    /**
     * create new folder.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function createNewFolder(Request $request)
    {
        $path            = $request->path;
        $new_folder_name = $this->cleanName($request->new_folder_name, true);
        $full_path       = !$path ? $new_folder_name : $this->clearDblSlash("$path/$new_folder_name");
        $success         = false;

        if ($this->storageDisk->exists($full_path)) {
            $message = trans('MediaManager::messages.error_already_exists');
        } elseif ($this->storageDisk->makeDirectory($full_path)) {
            $success = true;
            $message = '';
        } else {
            $message = trans('MediaManager::messages.error_creating_dir');
        }

        return compact('success', 'message', 'new_folder_name', 'full_path');
    }

    /**
     * rename item.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function renameItem(Request $request)
    {
        $path           = $request->path;
        $filename       = $request->filename;
        $new_filename   = $this->cleanName($request->new_filename);
        $success        = false;

        $old_path = !$path ? $filename : $this->clearDblSlash("$path/$filename");
        $new_path = !$path ? $new_filename : $this->clearDblSlash("$path/$new_filename");

        try {
            if (!$this->storageDisk->exists($new_path)) {
                if ($this->storageDisk->move($old_path, $new_path)) {
                    $success = true;

                    // fire event
                    event('MMFileRenamed', [
                        'old_path' => $this->getItemPath($old_path),
                        'new_path' => $this->getItemPath($new_path),
                    ]);
                } else {
                    throw new Exception(
                        trans('MediaManager::messages.error_moving')
                    );
                }
            } else {
                throw new Exception(
                    trans('MediaManager::messages.error_already_exists')
                );
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return compact('success', 'message', 'new_filename');
    }

    /**
     * move files/folders.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function moveItem(Request $request)
    {
        $path   = $request->path;
        $copy   = $request->use_copy;
        $result = [];

        foreach ($request->moved_files as $one) {
            $file_type    = $one['type'];
            $file_name    = $one['name'];
            $file_size    = $one['size'];
            $file_items   = isset($one['items']) ? $one['items'] : 0;

            $destination = "{$request->destination}/$file_name";
            $old_path    = !$path ? $file_name : $this->clearDblSlash("$path/$file_name");
            $new_path    = $destination == '../'
                            ? '/' . dirname($path) . '/' . str_replace('../', '', $destination)
                            : "$path/$destination";

            $pattern = [
                '/[[:alnum:]]+\/\.\.\/\//' => '',
                '/\/\//'                   => '/',
            ];
            $new_path = preg_replace(array_keys($pattern), array_values($pattern), $new_path);

            try {
                if (!file_exists($new_path)) {
                    // copy
                    if ($copy) {
                        // folders
                        if ($one['type'] == 'folder') {
                            $old = $this->getItemPath($old_path);
                            $new = $this->getItemPath($new_path);

                            if (app('files')->copyDirectory($old, $new)) {
                                $result[] = [
                                    'success' => true,
                                    'name'    => $one['name'],
                                    'items'   => $file_items,
                                    'type'    => $file_type,
                                    'size'    => $file_size,
                                ];
                            } else {
                                $exc = array_get($this->storageDiskInfo, 'root')
                                    ? trans('MediaManager::messages.error_moving')
                                    : trans('MediaManager::messages.error_moving_cloud');

                                throw new Exception($exc);
                            }
                        }

                        // files
                        else {
                            if ($this->storageDisk->copy($old_path, $new_path)) {
                                $result[] = [
                                    'success' => true,
                                    'name'    => $one['name'],
                                    'items'   => $file_items,
                                    'type'    => $file_type,
                                    'size'    => $file_size,
                                ];
                            } else {
                                throw new Exception(
                                    trans('MediaManager::messages.error_moving')
                                );
                            }
                        }
                    }

                    // move
                    else {
                        if ($this->storageDisk->move($old_path, $new_path)) {
                            $result[] = [
                                'success' => true,
                                'name'    => $one['name'],
                                'items'   => $file_items,
                                'type'    => $file_type,
                                'size'    => $file_size,
                            ];

                            // fire event
                            event('MMFileMoved', [
                                'old_path' => $this->getItemPath($old_path),
                                'new_path' => $this->getItemPath($new_path),
                            ]);
                        } else {
                            $exc = trans('MediaManager::messages.error_moving');

                            if ($one['type'] == 'folder' && !array_get($this->storageDiskInfo, 'root')) {
                                $exc = trans('MediaManager::messages.error_moving_cloud');
                            }

                            throw new Exception($exc);
                        }
                    }
                } else {
                    throw new Exception(
                        trans('MediaManager::messages.error_already_exists')
                    );
                }
            } catch (Exception $e) {
                $result[] = [
                    'success' => false,
                    'message' => "\"$old_path\" " . $e->getMessage(),
                ];
            }
        }

        return response()->json($result);
    }

    /**
     * delete files/folders.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function deleteItem(Request $request)
    {
        $path   = $request->path;
        $result = [];

        foreach ($request->deleted_files as $one) {
            $name = $one['name'];
            $type = $one['type'];
            $path = !$path ? $name : $this->clearDblSlash("$path/$name");

            // folder
            if ($type == 'folder') {
                if ($this->storageDisk->deleteDirectory($path)) {
                    $result[]  = [
                        'success' => true,
                        'name'    => $name,
                        'type'    => $type,
                    ];

                    // fire event
                    event('MMFileDeleted', [
                        'file_path' => $this->getItemPath($path),
                        'is_folder' => true,
                    ]);
                } else {
                    $result[] = [
                        'success' => false,
                        'name'    => $name,
                        'type'    => $type,
                        'message' => trans('MediaManager::messages.error_deleting_file'),
                    ];
                }
            }

            // file
            else {
                if ($this->storageDisk->delete($path)) {
                    $result[]  = [
                        'success' => true,
                        'name'    => $name,
                        'type'    => $type,
                        'path'    => $this->resolveUrl($path),
                    ];

                    // fire event
                    event('MMFileDeleted', [
                        'file_path' => $this->getItemPath($path),
                        'is_folder' => false,
                    ]);
                } else {
                    $result[] = [
                        'success' => false,
                        'name'    => $path,
                        'type'    => $type,
                        'message' => trans('MediaManager::messages.error_deleting_file'),
                    ];
                }
            }
        }

        return response()->json($result);
    }

    /**
     * change file visibility.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function changeItemVisibility(Request $request)
    {
        $path   = $request->path;
        $result = [];

        foreach ($request->list as $file) {
            $name      = $file['name'];
            $type      = $file['visibility'] == 'public' ? 'private' : 'public';
            $file_path = !$path ? $name : $this->clearDblSlash("$path/$name");

            if ($this->storageDisk->setVisibility($file_path, $type)) {
                $result[] = [
                    'success'    => true,
                    'name'       => $name,
                    'visibility' => $type,
                    'message'    => trans('MediaManager::messages.visibility_success', ['attr' => $name]),
                ];
            } else {
                $result[] = [
                    'success' => false,
                    'message' => trans('MediaManager::messages.visibility_error', ['attr' => $name]),
                ];
            }
        }

        return response()->json($result);
    }

    /**
     * lock/unlock files/folders.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function lockItem(Request $request)
    {
        $lockedList = $this->db->pluck('path')->toArray();
        $result     = [];
        $deletes    = [];

        foreach ($request->list as $item) {
            $url = $item['path'];

            if (in_array($url, $lockedList)) {
                // for some reason we cant delete the items one by one
                // probably related to sqlite
                $deletes[] = $url;
            } else {
                $this->db->insert(['path' => $url]);
            }

            $result[] = [
                'message' => trans('MediaManager::messages.lock_success', ['attr' => $item['name']]),
            ];
        }

        if ($deletes) {
            $this->db->whereIn('path', $deletes)->delete();
        }

        return response()->json($result);
    }

    /**
     * zip folder.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function downloadFolder(Request $request)
    {
        return $this->zipAndDownload(
            $request->name,
            $request->id,
            $this->storageDisk->allFiles("{$request->folders}/$request->name"),
            'folder'
        );
    }

    /**
     * zip files.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function downloadFiles(Request $request)
    {
        return $this->zipAndDownload(
            $request->name . '-files',
            $request->id,
            json_decode($request->list, true),
            'files'
        );
    }

    /**
     * zip progress update.
     */
    public function zipProgress(Request $request)
    {
        // stop execution
        $start        = time();
        $maxExecution = ini_get('max_execution_time');
        $sleep        = array_get($this->storageDiskInfo, 'root') ? 0.5 : 1.5;
        $close        = false;

        // params
        $id    = $request->header('last-event-id');
        $name  = "{$request->name}-{$request->id}";

        // get changes
        $store = $this->zipCacheStore;

        return response()->stream(function () use ($start, $maxExecution, $close, $sleep, $store, $name) {
            while (!$close) {
                // progress
                $this->SSE_msg($store->get("$name.progress"), 'progress');

                // warn
                if ($store->has("$name.warn")) {
                    $this->SSE_msg(
                        trans('MediaManager::messages.stream_error', ['attr' => $store->pull("$name.warn")]),
                        'warn'
                    );
                }

                // done
                if ($store->has("$name.done")) {
                    $close = true;
                    $this->SSE_msg(100, 'progress');
                    $this->SSE_msg('All Done', 'done');
                    $this->clearZipCache($store, $name);
                }

                // exit
                if (time() >= $start + $maxExecution) {
                    $close = true;
                    $this->SSE_msg(null, 'exit');
                    $this->clearZipCache($store, $name);
                }

                ob_flush();
                flush();

                // don't wait unnecessary
                if (!$close) {
                    sleep($sleep);
                }
            }
        }, 200, [
            'Content-Type'                     => 'text/event-stream', // needed for SSE to work
            'Cache-Control'                    => 'no-cache',          // dont cache this response
            'X-Accel-Buffering'                => 'no',                // needed for while loop to work
            'Access-Control-Allow-Origin'      => config('app.url'),   // for cors
            'Access-Control-Expose-Headers'    => '*',                 // for cors
            'Access-Control-Allow-Credentials' => true,                // for cors
        ]);
    }
}
