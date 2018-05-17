<?php

namespace ctf0\MediaManager;

use Illuminate\Support\ServiceProvider;
use ctf0\PackageChangeLog\PackageChangeLogServiceProvider;

class MediaManagerServiceProvider extends ServiceProvider
{
    protected $file;

    public function boot()
    {
        $this->file = app('files');

        $this->packagePublish();

        // append extra data
        if (!app('cache')->store('file')->has('ct-mm')) {
            $this->autoReg();
        }
    }

    /**
     * publish package assets.
     *
     * @return [type] [description]
     */
    protected function packagePublish()
    {
        // config
        $this->publishes([
            __DIR__ . '/config' => config_path(),
        ], 'config');

        // database
        $this->publishes([
            __DIR__ . '/database' => storage_path('logs'),
        ], 'db');

        $this->extraConfigs();

        // public
        $this->publishes([
            __DIR__ . '/dist' => public_path('assets/vendor/MediaManager'),
        ], 'dist');

        // resources
        $this->publishes([
            __DIR__ . '/resources/assets' => resource_path('assets/vendor/MediaManager'),
        ], 'assets');

        // trans
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'MediaManager');
        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang/vendor/MediaManager'),
        ], 'trans');

        // views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'MediaManager');
        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/MediaManager'),
        ], 'view');

        if (app('files')->exists(public_path('assets/vendor/MediaManager/patterns'))) {
            $this->viewComp();
        }
    }

    protected function extraConfigs()
    {
        // database
        $db = storage_path('logs/MediaManager.sqlite');

        if ($this->file->exists($db)) {
            config(['database.connections.mediamanager' => [
                'driver'   => 'sqlite',
                'database' => $db,
            ]]);
        }

        // caching for zip-stream
        config(['cache.stores.mediamanager' => [
            'driver'     => 'database',
            'table'      => 'cache',
            'connection' => 'mediamanager',
        ]]);
    }

    /**
     * share data with view.
     *
     * @return [type] [description]
     */
    protected function viewComp()
    {
        $url = app('filesystem')
            ->disk(config('mediaManager.storage_disk'))
            ->url('/');

        $patterns = collect(
                    app('files')->allFiles(public_path('assets/vendor/MediaManager/patterns'))
                )->map(function ($item) {
                    return preg_replace('/.*\/patterns/', '/assets/vendor/MediaManager/patterns', $item->getPathName());
                });

        view()->composer('MediaManager::_manager', function ($view) use ($url, $patterns) {
            $view->with([
               'base_url' => preg_replace('/\/+$/', '/', $url),
               'patterns' => json_encode($patterns),
               'randId'   => uniqid(),
           ]);
        });
    }

    /**
     * autoReg package resources.
     *
     * @return [type] [description]
     */
    protected function autoReg()
    {
        // routes
        $route_file = base_path('routes/web.php');
        $search     = 'MediaManager';

        if ($this->checkExist($route_file, $search)) {
            $data = "\n// MediaManager\nctf0\MediaManager\MediaRoutes::routes();";

            $this->file->append($route_file, $data);
        }

        // mix
        $mix_file = base_path('webpack.mix.js');
        $search   = 'MediaManager';

        if ($this->checkExist($mix_file, $search)) {
            $data =
<<<EOT

// MediaManager
mix.js('resources/assets/vendor/MediaManager/js/manager.js', 'public/assets/vendor/MediaManager')
    .sass('resources/assets/vendor/MediaManager/sass/media.scss', 'public/assets/vendor/MediaManager/style.css')
    .version();
EOT;

            $this->file->append($mix_file, $data);
        }

        // run check once
        app('cache')->store('file')->rememberForever('ct-mm', function () {
            return 'added';
        });
    }

    /**
     * [checkExist description].
     *
     * @param [type] $file   [description]
     * @param [type] $search [description]
     *
     * @return [type] [description]
     */
    protected function checkExist($file, $search)
    {
        return $this->file->exists($file) && !str_contains($this->file->get($file), $search);
    }

    /**
     * extra functionality.
     *
     * @return [type] [description]
     */
    public function register()
    {
        $this->app->register(PackageChangeLogServiceProvider::class);
    }
}
