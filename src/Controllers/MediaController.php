<?php

namespace ctf0\MediaManager\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MediaController extends Controller
{
    use OpsTrait;

    protected $fileSystem;
    protected $storageDisk;
    protected $ignoreFiles;
    protected $fileChars;
    protected $folderChars;
    protected $sanitizedText;
    protected $unallowed_mimes;
    protected $LMF;
    protected $fw;

    public function __construct()
    {
        $this->fileSystem      = config('mediaManager.storage_disk');
        $this->storageDisk     = app('filesystem')->disk($this->fileSystem);
        $this->ignoreFiles     = config('mediaManager.ignore_files');
        $this->fileChars       = config('mediaManager.allowed_fileNames_chars');
        $this->folderChars     = config('mediaManager.allowed_folderNames_chars');
        $this->sanitizedText   = config('mediaManager.sanitized_text');
        $this->unallowed_mimes = config('mediaManager.unallowed_mimes');
        $this->LMF             = config('mediaManager.last_modified_format');
    }

    /**
     * [index description].
     *
     * @return [type] [description]
     */
    public function index()
    {
        return view('MediaManager::media');
    }

    /**
     * [files description].
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
            'path'   => $folder,
            'items'  => $this->getData($folder),
        ]);
    }

    /**
     * [get_all_dirs description].
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
     * [upload description].
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function upload(Request $request)
    {
        $upload_path = $request->upload_path;
        $files       = $request->file;
        $result      = [];

        foreach ($files as $one) {
            try {
                // check for mime type
                $original  = $one->getClientOriginalName();
                $file_type = $one->getMimeType();
                $file_ext  = $one->getClientOriginalExtension();
                $get_name  = str_replace(".$file_ext", '', $original);

                if (str_contains($file_type, $this->unallowed_mimes)) {
                    throw new Exception(trans('MediaManager::messages.not_allowed_file_ext', ['attr'=>$file_type]));
                }

                $file_name   = $this->cleanName($get_name, null, $file_ext) . ".$file_ext";
                $destination = "$upload_path/$file_name";

                // check existence
                if ($this->storageDisk->exists($destination)) {
                    throw new Exception(trans('MediaManager::messages.error_already_exists'));
                }

                // save file
                $saved_name = $one->storeAs($upload_path, $file_name, $this->fileSystem);

                // fire event
                event('MMFileUploaded', $this->getFilePath($this->fileSystem, $saved_name));

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
     * [new_folder description].
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
            $message = trans('MediaManager::messages.folder_exists_already');
        } elseif ($this->storageDisk->makeDirectory($full_path)) {
            $success = true;
            $message = '';
        } else {
            $message = trans('MediaManager::messages.error_creating_dir');
        }

        return compact('success', 'message', 'new_folder_name', 'full_path');
    }

    /**
     * [delete_file description].
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
            ];

            $file_name = "$folderLocation/$file_name";

            if ('folder' == $type) {
                if (!$this->storageDisk->deleteDirectory($file_name)) {
                    $result[] = [
                        'success' => false,
                        'message' => trans('MediaManager::messages.error_deleting_folder'),
                    ];
                } else {
                    // fire event
                    event('MMFileDeleted', [
                        'file_path' => $this->getFilePath($this->fileSystem, $file_name),
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
                        'file_path' => $this->getFilePath($this->fileSystem, $file_name),
                        'is_folder' => false,
                    ]);
                }
            }
        }

        return response()->json(['data'=>$result]);
    }

    /**
     * [move_file description].
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function move_file(Request $request)
    {
        $folderLocation  = $request->folder_location;
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
                            'old_path' => $this->getFilePath($this->fileSystem, $old_path),
                            'new_path' => $this->getFilePath($this->fileSystem, $new_path),
                        ]);
                    } else {
                        throw new Exception(trans('MediaManager::messages.error_moving'));
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
     * [rename_file description].
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

        if (!$this->storageDisk->exists("$folderLocation/$new_filename")) {
            if ($this->storageDisk->move("$folderLocation/$filename", "$folderLocation/$new_filename")) {
                $message = '';
                $success = true;

                // fire event
                event('MMFileRenamed', [
                    'old_path' => $this->getFilePath($this->fileSystem, "$folderLocation/$filename"),
                    'new_path' => $this->getFilePath($this->fileSystem, "$folderLocation/$new_filename"),
                ]);
            } else {
                $message = trans('MediaManager::messages.error_moving');
            }
        } else {
            $message = trans('MediaManager::messages.error_already_exists');
        }

        return compact('success', 'message', 'new_filename');
    }
}
