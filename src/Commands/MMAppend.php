<?php

namespace ctf0\MediaManager\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MMAppend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mm:append';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Append routes to 'routes/web.php', Append assets compiling to 'webpack.mix.js'";

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // routes
        $route_file = base_path('routes/web.php');
        $search     = 'MediaManager';
        if (File::exists($route_file) && !str_contains(File::get($route_file), $search)) {
            $data = "\n// Media-Manager\nctf0\MediaManager\MediaRoutes::routes();";

            File::append($route_file, $data);
            $this->comment("['ctf0\MediaManager\MediaRoutes::routes();'] added to [web.php]");
        }

        // mix
        $mix_file = base_path('webpack.mix.js');
        $search   = 'MediaManager';
        if (File::exists($mix_file) && !str_contains(File::get($mix_file), $search)) {
            $data = "\n// Media-Manager\nmix.sass('resources/assets/vendor/MediaManager/sass/' + process.env.MIX_MM_FRAMEWORK + '/media.scss', 'public/assets/vendor/MediaManager/style.css')\n\t.version();";

            File::append($mix_file, $data);
            $this->comment("['mix.sass(..).version()'] added to [webpack.mix.js]");
        }

        // fw
        $env_file = base_path('.env');
        $search   = 'MIX_MM_FRAMEWORK';
        if (File::exists($env_file) && !str_contains(File::get($env_file), $search)) {
            $data = "\nMIX_MM_FRAMEWORK=bulma";

            File::append($env_file, $data);
            $this->comment("['MIX_MM_FRAMEWORK=bulma'] added to [.env]");
        }
    }
}
