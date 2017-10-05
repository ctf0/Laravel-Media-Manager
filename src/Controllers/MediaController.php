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
        $this->fw              = config('mediaManager.framework');
    }

    /**
     * [index description].
     *
     * @return [type] [description]
     */
    public function index()
    {
        return view("MediaManager::media-{$this->fw}");
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
        $folder = $request->folder !== '/'
        ? $request->folder
        : '';

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
        $result      = [];

        foreach ($request->file as $one) {
            $file_name   = $one->getClientOriginalName();
            $destination = "$upload_path/{$this->cleanName($file_name)}";
            $file_type   = $one->getMimeType();

            try {
                // check for mime type
                if (str_contains($file_type, $this->unallowed_mimes)) {
                    throw new Exception(trans('MediaManager::messages.not_allowed_file_ext', ['attr'=>$file_type]));
                }

                // check existence
                if ($this->storageDisk->exists($destination)) {
                    throw new Exception(trans('MediaManager::messages.error_may_exist'));
                }

                // check name
                // because dropzone automatically sanitize the file name
                if ($file_name == '.' . $one->getClientOriginalExtension()) {
                    $file_name = $this->sanitizedText . $file_name;
                }

                $path = $one->storeAs($upload_path, $this->cleanName($file_name), $this->fileSystem);

                $result[] = [
                    'path'    => preg_replace('/^public\//', '', $path),
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

            if ($type == 'folder') {
                if (!$this->storageDisk->deleteDirectory($file_name)) {
                    $result[] = [
                        'success' => false,
                        'message' => trans('MediaManager::messages.error_deleting_folder'),
                    ];
                }
            } else {
                if (!$this->storageDisk->delete($file_name)) {
                    $result[] = [
                        'success' => false,
                        'message' => trans('MediaManager::messages.error_deleting_file'),
                    ];
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
            $file_type   = $one['type'];
            $file_name   = $one['name'];
            $file_size   = $one['size'];

            $destination = "{$request->destination}/$file_name";
            $file_name   = "$folderLocation/$file_name";
            $destination = strpos($destination, '../') == true
                            ? '/' . dirname($folderLocation) . '/' . str_replace('../', '', $destination)
                            : "$folderLocation/$destination";

            try {
                if (!file_exists($destination)) {
                    if ($this->storageDisk->move($file_name, $destination)) {
                        $result[] = [
                            'success' => true,
                            'name'    => $one['name'],
                            'type'    => $file_type,
                            'size'    => $file_size,
                        ];
                    } else {
                        throw new Exception(trans('MediaManager::messages.error_moving'));
                    }
                } else {
                    throw new Exception(trans('MediaManager::messages.error_already_exists'));
                }
            } catch (Exception $e) {
                $result[] = [
                    'success' => false,
                    'message' => "\"$file_name\" " . $e->getMessage(),
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
            } else {
                $message = trans('MediaManager::messages.error_moving');
            }
        } else {
            $message = trans('MediaManager::messages.error_may_exist');
        }

        return compact('success', 'message', 'new_filename');
    }
}
