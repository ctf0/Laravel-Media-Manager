<?php

namespace ctf0\MediaManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use ctf0\MediaManager\Commands\PackageSetup;
use ctf0\PackageChangeLog\PackageChangeLogServiceProvider;

class MediaManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->packagePublish();
        $this->extraConfigs();
        $this->socketRoute();
        $this->viewComp();
        $this->command();
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

        $this->publishes([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ], 'migration');

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

    protected function extraConfigs()
    {
        // database
        $db = storage_path('logs/MediaManager.sqlite');

        if ($this->file->exists($db)) {
            $this->app['config']->set('database.connections.mediamanager', [
                'driver'   => 'sqlite',
                'database' => $db,
            ]);
        }
    }

    protected function socketRoute()
    {
        Broadcast::channel('User.{id}.media', function ($user, $id) {
            return $user->id == $id;
        });
    }

    /**
     * share data with view.
     *
     * @return [type] [description]
     */
    protected function viewComp()
    {
        $data = [];

        // base url
        $config = $this->app['config']->get('mediaManager');
        $url    = $this->app['filesystem']
            ->disk(array_get($config, 'storage_disk'))
            ->url('/');

        $data['base_url'] = preg_replace('/\/+$/', '/', $url);

        // upload panel bg patterns
        $pattern_path = public_path('assets/vendor/MediaManager/patterns');

        if ($this->file->exists($pattern_path)) {
            $patterns = collect(
                $this->file->allFiles($pattern_path)
            )->map(function ($item) {
                return preg_replace('/.*\/patterns/', '/assets/vendor/MediaManager/patterns', $item->getPathName());
            });

            $data['patterns'] = json_encode($patterns);
        }

        // share
        view()->composer('MediaManager::_manager', function ($view) use ($data) {
            $view->with($data);
        });
    }

    /**
     * package commands.
     *
     * @return [type] [description]
     */
    protected function command()
    {
        $this->commands([
            PackageSetup::class,
        ]);
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
