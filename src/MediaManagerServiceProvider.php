<?php

namespace ctf0\MediaManager;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class MediaManagerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        // config
        $this->publishes([
            __DIR__.'/config' => config_path(),
        ], 'config');

        // resources
        $this->publishes([
            __DIR__.'/resources/assets' => resource_path('assets/vendor/MediaManager'),
        ], 'assets');

        // trans
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'MediaManager');
        $this->publishes([
            __DIR__.'/resources/lang' => resource_path('lang/vendor/MediaManager'),
        ], 'trans');

        // views
        $this->loadViewsFrom(__DIR__.'/resources/views', 'MediaManager');
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/MediaManager'),
        ], 'view');

        // mix
        $mix_file = base_path('webpack.mix.js');
        $search   = 'MediaManager';
        if (File::exists($mix_file) && !str_contains(File::get($mix_file), $search)) {
            $data = "\n// Media-Manager\nmix.js('resources/assets/vendor/MediaManager/js/media.js', 'public/assets/vendor/MediaManager/script.js')\n\t.sass('resources/assets/vendor/MediaManager/sass/' + process.env.MIX_MM_FRAMEWORK + '/media.scss', 'public/assets/vendor/MediaManager/style.css')\n\t.version();";

            File::append(base_path('webpack.mix.js'), $data);
        }
    }
}
