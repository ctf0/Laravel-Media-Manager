<?php

namespace ctf0\MediaManager\App\Controllers\Modules;

use ZipStream\ZipStream;
use Illuminate\Http\Request;
use ZipStream\Option\Archive;
use ctf0\MediaManager\App\Events\MediaZipProgress;

trait Download
{
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

    /**
     * zip ops.
     *
     * @param mixed $name
     * @param mixed $list
     * @param mixed $type
     */
    protected function zipAndDownload($name, $list)
    {
        return response()->stream(function () use ($name, $list) {
            // track changes
            $counter  = 100 / count($list);
            $progress = 0;
            broadcast(new MediaZipProgress(['progress' => $progress]));

            $zip = new ZipStream("$name.zip", $this->getZipOptions());

            foreach ($list as $file) {
                $name = $file['name'];
                $path = $file['storage_path'];
                $streamRead = $this->storageDisk->readStream($path);

                // add to zip
                if ($streamRead) {
                    $progress += $counter;
                    broadcast(new MediaZipProgress(['progress' => round($progress, 0)]));

                    $zip->addFileFromStream($name, $streamRead);
                } else {
                    broadcast(new MediaZipProgress([
                        'msg'  => $name,
                        'type' => 'warn',
                    ]));
                }
            }

            broadcast(new MediaZipProgress(['progress' => 100]));
            $zip->finish();
        });
    }

    protected function zipAndDownloadDir($name, $list)
    {
        return response()->stream(function () use ($name, $list) {
            // track changes
            $counter  = 100 / count($list);
            $progress = 0;
            broadcast(new MediaZipProgress(['progress' => $progress]));

            $zip = new ZipStream("$name.zip", $this->getZipOptions());

            foreach ($list as $file) {
                $dir_name   = pathinfo($file, PATHINFO_DIRNAME);
                $file_name  = pathinfo($file, PATHINFO_BASENAME);
                $full_name  = "$dir_name/$file_name";
                $streamRead = $this->storageDisk->readStream($file);

                // add to zip
                if ($streamRead) {
                    $progress += $counter;
                    broadcast(new MediaZipProgress(['progress' => round($progress, 0)]));

                    $zip->addFileFromStream($full_name, $streamRead);
                } else {
                    broadcast(new MediaZipProgress([
                        'msg'  => $full_name,
                        'type' => 'warn',
                    ]));
                }
            }

            broadcast(new MediaZipProgress(['progress' => 100]));
            $zip->finish();
        });
    }

    protected function getZipOptions()
    {
        $options = new Archive();
        // $options->setZeroHeader(true);
        // $options->setEnableZip64(false)
        $options->setContentType('application/octet-stream');
        $options->setSendHttpHeaders(true);
        $options->setHttpHeaderCallback('header');
        $options->setDeflateLevel(9);

        return $options;
    }
}
