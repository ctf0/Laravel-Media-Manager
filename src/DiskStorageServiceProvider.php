<?php

namespace ctf0\MediaManager;

use Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Plugin\ListWith;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Adapter;

/**
 * create a dynamic filesystem disk with cache capabilities.
 *
 * 1- install: composer require league/flysystem-cached-adapter
 * 2- register: DiskStorageServiceProvider
 * 3- usage: app('filesystem')->disk('ctf0-media').
 */
class DiskStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $mm           = config('mediaManager.storage_disk');
        $fs           = config('filesystems.disks.' . $mm);
        $fs['driver'] = 'ctf0';
        config(['filesystems.disks.ctf0-media' => $fs]);

        Storage::extend('ctf0', function ($app, $config) use ($mm) {
            $userDisk = $app['filesystem']->disk($mm)->getDriver()->getAdapter();
            $local    = new Local(storage_path('framework/cache/mediamanager'));
            $cache    = new Adapter($local, 'file', 300); // 5 mins
            $adapter  = new CachedAdapter($userDisk, $cache);

            return (new Filesystem($adapter))->addPlugin(new ListWith());
        });
    }
}
