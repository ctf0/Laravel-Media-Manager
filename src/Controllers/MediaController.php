<?php

namespace ctf0\MediaManager\Controllers;

use Exception;
use ZipStream\ZipStream;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MediaController extends Controller
{
    use OpsTrait;

    // main
    protected $fileSystem;
    protected $storageDisk;
    protected $ignoreFiles;
    protected $fileChars;
    protected $folderChars;
    protected $sanitizedText;
    protected $unallowedMimes;
    protected $LMF;

    // extra
    protected $storageDiskInfo;
    protected $lockedList;
    protected $cacheStore;
    protected $db;

    public function __construct()
    {
        $config = config('mediaManager');

        $this->fileSystem     = array_get($config, 'storage_disk');
        $this->storageDisk    = app('filesystem')->disk($this->fileSystem);
        $this->ignoreFiles    = array_get($config, 'ignore_files');
        $this->fileChars      = array_get($config, 'allowed_fileNames_chars');
        $this->folderChars    = array_get($config, 'allowed_folderNames_chars');
        $this->sanitizedText  = array_get($config, 'sanitized_text');
        $this->unallowedMimes = array_get($config, 'unallowed_mimes');
        $this->LMF            = array_get($config, 'last_modified_format');

        $this->db              = app('db')->connection('mediamanager');
        $this->lockedList      = $this->db->table('locked')->pluck('path');
        $this->storageDiskInfo = config("filesystems.disks.{$this->fileSystem}");
        $this->cacheStore      = app('cache')->store('mediamanager');
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
    public function get_files(Request $request)
    {
        $folder = '/' !== $request->folder
            ? $request->folder
            : '';

        if ($folder && !$this->storageDisk->exists($folder)) {
            return response()->json(['error' => trans('MediaManager::messages.error_doesnt_exist', ['attr'=>$folder])]);
        }

        return response()->json([
            'locked' => $this->lockedList,
            'files'  => [
                'path'   => $folder,
                'items'  => $this->getData($folder),
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
    public function get_dirs(Request $request)
    {
        $folderLocation = $request->folder_location;

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        return response()->json(
            str_replace($folderLocation, '', $this->storageDisk->allDirectories($folderLocation))
        );
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
        $files       = $request->file;
        $random_name = $request->random_names;
        $result      = [];

        foreach ($files as $one) {
            $file_type   = $one->getMimeType();

            $original    = $one->getClientOriginalName();
            $get_name    = pathinfo($original, PATHINFO_FILENAME);
            $file_ext    = pathinfo($original, PATHINFO_EXTENSION);
            $file_name   = $random_name ? uniqid() . ".$file_ext" : $this->cleanName($get_name, null) . ".$file_ext";
            $destination = "$upload_path/$file_name";

            try {
                // check for mime type
                if (str_contains($file_type, $this->unallowedMimes)) {
                    throw new Exception(trans('MediaManager::messages.not_allowed_file_ext', ['attr'=>$file_type]));
                }

                // check existence
                if ($this->storageDisk->exists($destination)) {
                    throw new Exception(trans('MediaManager::messages.error_already_exists'));
                }

                // save file
                $saved_name = $one->storeAs($upload_path, $file_name, $this->fileSystem);

                // fire event
                event('MMFileUploaded', $this->getFilePath($saved_name));

                $result[] = [
                    'path'    => preg_replace('/^public\//', '', $saved_name),
                    'success' => true,
                    'message' => $file_name,
                ];
            } catch (Exception $e) {
                $result[] = [
                    'path'    => '',
                    'success' => false,
                    'message' => "\"$file_name\" " . $e->getMessage(),
                ];
            }
        }

        return response()->json(['data'=>$result]);
    }

    /**
     * create new folder.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function new_folder(Request $request)
    {
        $current_path    = $request->current_path;
        $new_folder_name = $this->cleanName($request->new_folder_name, true);
        $full_path       = "$current_path/$new_folder_name";
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
     * delete files/folders.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function delete_file(Request $request)
    {
        $folderLocation  = $request->folder_location;
        $result          = [];

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        foreach ($request->deleted_files as $one) {
            $file_name  = $one['name'];
            $type       = $one['type'];
            $result[]   = [
                'success' => true,
                'name'    => $file_name,
                'type'    => $type,
            ];

            $file_name = "$folderLocation/$file_name";

            if ('folder' == $type) {
                if (!$this->storageDisk->deleteDirectory($file_name)) {
                    $result[] = [
                        'success' => false,
                        'message' => trans('MediaManager::messages.error_deleting_file'),
                    ];
                } else {
                    // fire event
                    event('MMFileDeleted', [
                        'file_path' => $this->getFilePath($file_name),
                        'is_folder' => true,
                    ]);
                }
            } else {
                if (!$this->storageDisk->delete($file_name)) {
                    $result[] = [
                        'success' => false,
                        'message' => trans('MediaManager::messages.error_deleting_file'),
                    ];
                } else {
                    // fire event
                    event('MMFileDeleted', [
                        'file_path' => $this->getFilePath($file_name),
                        'is_folder' => false,
                    ]);
                }
            }
        }

        return response()->json(['data'=>$result]);
    }

    /**
     * move files/folders.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function move_file(Request $request)
    {
        $folderLocation  = $request->folder_location;
        $copy            = $request->use_copy;
        $result          = [];

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        foreach ($request->moved_files as $one) {
            $file_type    = $one['type'];
            $file_name    = $one['name'];
            $file_size    = $one['size'];
            $file_items   = isset($one['items']) ? $one['items'] : 0;

            $destination = "{$request->destination}/$file_name";
            $old_path    = "$folderLocation/$file_name";
            $new_path    = true == strpos($destination, '../')
                            ? '/' . dirname($folderLocation) . '/' . str_replace('../', '', $destination)
                            : "$folderLocation/$destination";

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
                        if ('folder' == $one['type']) {
                            $old = $this->getFilePath($old_path);
                            $new = $this->getFilePath($new_path);

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
                                throw new Exception(trans('MediaManager::messages.error_moving'));
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
                                'old_path' => $this->getFilePath($old_path),
                                'new_path' => $this->getFilePath($new_path),
                            ]);
                        } else {
                            $exc = trans('MediaManager::messages.error_moving');

                            if ('folder' == $one['type'] && !array_get($this->storageDiskInfo, 'root')) {
                                $exc = trans('MediaManager::messages.error_moving_cloud');
                            }

                            throw new Exception($exc);
                        }
                    }
                } else {
                    throw new Exception(trans('MediaManager::messages.error_already_exists'));
                }
            } catch (Exception $e) {
                $result[] = [
                    'success' => false,
                    'message' => "\"$old_path\" " . $e->getMessage(),
                ];
            }
        }

        return response()->json(['data'=>$result]);
    }

    /**
     * rename item.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function rename_file(Request $request)
    {
        $folderLocation = $request->folder_location;
        $filename       = $request->filename;
        $new_filename   = $this->cleanName($request->new_filename);
        $success        = false;

        if (is_array($folderLocation)) {
            $folderLocation = rtrim(implode('/', $folderLocation), '/');
        }

        $old_path = "$folderLocation/$filename";
        $new_path = "$folderLocation/$new_filename";

        try {
            if (!$this->storageDisk->exists($new_path)) {
                if ($this->storageDisk->move($old_path, $new_path)) {
                    $success = true;

                    // fire event
                    event('MMFileRenamed', [
                        'old_path' => $this->getFilePath($old_path),
                        'new_path' => $this->getFilePath($new_path),
                    ]);
                } else {
                    throw new Exception(trans('MediaManager::messages.error_moving'));
                }
            } else {
                throw new Exception(trans('MediaManager::messages.error_already_exists'));
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return compact('success', 'message', 'new_filename');
    }

    /**
     * lock/unlock files/folders.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function lock_file(Request $request)
    {
        $path  = $request->path;
        $state = $request->state;
        $db    = $this->db->table('locked');

        'locked' == $state
            ? $db->insert(['path'=>$path])
            : $db->where('path', $path)->delete();

        return response()->json(['message'=>"'$path' " . ucfirst($state)]);
    }

    /**
     * zip ops.
     */
    public function folder_download(Request $request)
    {
        return $this->download(
            $request->name,
            $this->storageDisk->allFiles("{$request->folders}/$request->name"),
            'folder'
        );
    }

    public function files_download(Request $request)
    {
        return $this->download(
            $request->name . '-files',
            json_decode($request->list, true),
            'files'
        );
    }

    protected function download($name, $list, $type)
    {
        // track changes
        $counter    = 100 / count($list);
        $store      = $this->cacheStore;
        $cache_name = $name;
        $store->forever("$cache_name.progress", 0);

        return response()->stream(function () use ($name, $list, $type, $counter, $store, $cache_name) {
            $zip = new ZipStream("$name.zip", [
                'content_type' => 'application/octet-stream',
            ]);

            foreach ($list as $file) {
                if ('folder' == $type) {
                    $file_name = pathinfo($file, PATHINFO_BASENAME);
                    $streamRead = $this->storageDisk->readStream($file);
                } else {
                    $file_name = $file['name'];
                    $streamRead = @fopen($file['path'], 'r');
                }

                if ($streamRead) {
                    $store->increment("$cache_name.progress", round($counter, 2));
                    $zip->addFileFromStream($file_name, $streamRead);
                } else {
                    $store->forever("$cache_name.abort", $file_name);
                    die();
                }
            }

            $store->forever("$cache_name.done", true);
            $zip->finish();
        });
    }

    /**
     * zip progress update.
     */
    public function zip_progress(Request $request)
    {
        // stop execution
        $start        = time();
        $maxExecution = ini_get('max_execution_time');
        $sleep        = array_get($this->storageDiskInfo, 'root') ? 0.5 : 1.5;
        $close        = false;

        // params
        $id    = $request->header('last-event-id');
        $name  = $request->name;

        // get changes
        $store      = $this->cacheStore;
        $cache_name = $name;

        return response()->stream(function () use ($start, $maxExecution, $close, $sleep, $store, $cache_name) {
            while (!$close) {
                // progress
                $this->es_msg($store->get("$cache_name.progress"), 'progress');

                // avoid server crash
                if (time() >= $start + $maxExecution) {
                    $close = true;
                    $this->es_msg(null, 'exit');
                    $this->clearZipCache($store, $cache_name);
                }

                // abort
                if ($store->has("$cache_name.abort")) {
                    $close = true;
                    $this->es_msg('Could not open "' . $store->get("$cache_name.abort") . '" stream for reading.', 'abort');
                    $this->clearZipCache($store, $cache_name);
                }

                // done
                if ($store->has("$cache_name.done")) {
                    $close = true;
                    $this->es_msg(100, 'progress');
                    $this->es_msg('All Done', 'done');
                    $this->clearZipCache($store, $cache_name);
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
            'Cache-Control'                    => 'no-cache',          // make sure we dont cache this response
            'X-Accel-Buffering'                => 'no',                // needed for while loop to work
            'Access-Control-Allow-Origin'      => config('app.url'),   // for cors
            'Access-Control-Expose-Headers'    => '*',                 // for cors
            'Access-Control-Allow-Credentials' => true,                // for cors
        ]);
    }

    protected function es_msg($data = null, $event = null)
    {
        echo $event ? "event: $event\n" : ':';
        echo $data ? 'data: ' . json_encode(['response' => $data]) . "\n\n" : ':';
    }

    protected function clearZipCache($store, $item)
    {
        $store->forget("$item.progress");
        $store->forget("$item.done");
        $store->forget("$item.abort");
    }
}
