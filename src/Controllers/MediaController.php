<?php

namespace ctf0\MediaManager\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Flysystem\Plugin\ListWith;
use ctf0\MediaManager\Events\MediaFileOpsNotifications;

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
            if ($this->allowUpload($one)) {
                $one = $this->optimizeUpload($one);

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
            } else {
                $result[] = [
                    'success' => false,
                    'message' => trans('MediaManager::messages.error_cant_upload'),
                ];
            }
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'   => 'upload',
            'path' => $upload_path,
        ]))->toOthers();

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
        if ($this->allowUpload()) {
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

                // broadcast
                broadcast(new MediaFileOpsNotifications([
                    'op'      => 'upload',
                    'path'    => $path,
                ]))->toOthers();

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
        } else {
            $result = [
                'success' => false,
                'message' => trans('MediaManager::messages.error_cant_upload'),
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
        if ($this->allowUpload()) {
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

                // broadcast
                broadcast(new MediaFileOpsNotifications([
                    'op'      => 'upload',
                    'path'    => $path,
                ]))->toOthers();

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
        } else {
            $result = [
                'success' => false,
                'message' => trans('MediaManager::messages.error_cant_upload'),
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
            $file_name  = $one['name'];
            $file_type  = $one['type'];
            $file_size  = $one['size'];
            $file_items = isset($one['items']) ? $one['items'] : 0;
            $default    = [
                'name'  => $file_name,
                'type'  => $file_type,
                'size'  => $file_size,
                'items' => $file_items,
            ];

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
                                $result[] = array_merge($default, ['success' => true]);
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
                                $result[] = array_merge($default, ['success' => true]);
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
                            $result[] = array_merge($default, ['success' => true]);

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
        $path        = $request->path;
        $result      = [];
        $toBroadCast = [];

        foreach ($request->deleted_files as $one) {
            $name      = $one['name'];
            $type      = $one['type'];
            $item_path = !$path ? $name : $this->clearDblSlash("$path/$name");
            $default   = [
                'name' => $name,
                'type' => $type,
            ];

            // folder
            if ($type == 'folder') {
                if ($this->storageDisk->deleteDirectory($item_path)) {
                    $result[] = array_merge($default, ['success' => true]);

                    $toBroadCast[] = array_merge($default, [
                        'path' => $path,
                        'url'  => null,
                    ]);

                    // fire event
                    event('MMFileDeleted', [
                        'file_path' => $this->getItemPath($item_path),
                        'is_folder' => true,
                    ]);
                } else {
                    $result[] = array_merge($default, [
                        'success' => false,
                        'message' => trans('MediaManager::messages.error_deleting_file'),
                    ]);
                }
            }

            // file
            else {
                if ($this->storageDisk->delete($item_path)) {
                    $result[] = array_merge($default, [
                        'success' => true,
                        'url'     => $this->resolveUrl($item_path),
                    ]);

                    $toBroadCast[] = array_merge($default, [
                        'path' => $path,
                        'url'  => $this->resolveUrl($item_path),
                    ]);

                    // fire event
                    event('MMFileDeleted', [
                        'file_path' => $this->getItemPath($item_path),
                        'is_folder' => false,
                    ]);
                } else {
                    $result[] = [
                        'success' => false,
                        'name'    => $item_path,
                        'type'    => $type,
                        'message' => trans('MediaManager::messages.error_deleting_file'),
                    ];
                }
            }
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'      => 'delete',
            'items'   => $toBroadCast,
        ]))->toOthers();

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
        $path        = $request->path;
        $result      = [];
        $toBroadCast = [];

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

                $toBroadCast[] = [
                    'name'       => $name,
                    'visibility' => $type,
                ];
            } else {
                $result[] = [
                    'success' => false,
                    'message' => trans('MediaManager::messages.visibility_error', ['attr' => $name]),
                ];
            }
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'    => 'visibility',
            'path'  => $path,
            'items' => $toBroadCast,
        ]))->toOthers();

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
        $path       = $request->path;
        $lockedList = $this->db->pluck('path')->toArray();

        $result     = [];
        $removed    = [];
        $added      = [];

        foreach ($request->list as $item) {
            $url = $item['path'];

            if (in_array($url, $lockedList)) {
                // for some reason we cant delete the items one by one, probably related to sqlite
                $removed[] = $url;
            } else {
                $added[] = $url;
                $this->db->insert(['path' => $url]);
            }

            $result[] = [
                'message' => trans('MediaManager::messages.lock_success', ['attr' => $item['name']]),
            ];
        }

        if ($removed) {
            $this->db->whereIn('path', $removed)->delete();
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'      => 'lock',
            'path'    => $path,
            'removed' => $removed,
            'added'   => $added,
        ]))->toOthers();

        return response()->json(compact('result', 'removed', 'added'));
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
        return $this->zipAndDownloadDir(
            $request->name,
            $this->storageDisk->allFiles("{$request->folders}/$request->name")
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
            json_decode($request->list, true)
        );
    }
}
