<?php

namespace ctf0\MediaManager;

use Illuminate\Support\ServiceProvider;

class MediaManagerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
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

        // cmnds
        $this->commands([
            Commands\MMAppend::class,
        ]);
    }

    public function register()
    {
        $this->app->register(Tightenco\Ziggy\ZiggyServiceProvider::class);
    }
}
