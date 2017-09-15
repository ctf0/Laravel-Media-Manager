<?php

namespace ctf0\MediaManager;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class MediaManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->packagePublish();

        // append extra data
        if (!app('cache')->store('file')->has('ct-mm')) {
            $this->autoReg();
        }
    }

    /**
     * [packagePublish description].
     *
     * @return [type] [description]
     */
    protected function packagePublish()
    {
        // config
        $this->mergeConfigFrom(
            __DIR__ . '/config/ziggy.php', 'ziggy'
        );
        $this->publishes([
            __DIR__ . '/config' => config_path(),
        ], 'config');

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
    }

    /**
     * [autoReg description].
     *
     * @return [type] [description]
     */
    protected function autoReg()
    {
        // routes
        $route_file = base_path('routes/web.php');
        $search     = 'MediaManager';

        if ($this->checkExist($route_file, $search)) {
            $data = "\n// Media-Manager\nctf0\MediaManager\MediaRoutes::routes();";

            File::append($route_file, $data);
        }

        // mix
        $mix_file = base_path('webpack.mix.js');
        $search   = 'MediaManager';

        if ($this->checkExist($mix_file, $search)) {
            $data = "\n// Media-Manager\nrequire('dotenv').config()\nmix.sass('resources/assets/vendor/MediaManager/sass/' + process.env.MIX_MM_FRAMEWORK + '/media.scss', 'public/assets/vendor/MediaManager/style.css').version();";

            File::append($mix_file, $data);
        }

        // fw
        $env_file = base_path('.env');
        $search   = 'MIX_MM_FRAMEWORK';

        if ($this->checkExist($env_file, $search)) {
            $data = "\nMIX_MM_FRAMEWORK=bulma";

            File::append($env_file, $data);
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
        return File::exists($file) && !str_contains(File::get($file), $search);
    }

    /**
     * [register description].
     *
     * @return [type] [description]
     */
    public function register()
    {
        $this->app->register(\Tightenco\Ziggy\ZiggyServiceProvider::class);
        $this->app->register(\ctf0\PackageChangeLog\PackageChangeLogServiceProvider::class);
    }
}
