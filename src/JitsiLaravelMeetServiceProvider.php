<?php

namespace Furqanamx\JitsiLaravelMeet;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Furqanamx\JitsiLaravelMeet\View\Components\JitsiEmbed;

class JitsiLaravelMeetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/jitsi.php',
            'jitsi'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'jitsi'
        );

        $this->publishes([
            __DIR__ . '/../config/jitsi.php' => config_path('jitsi.php'),
        ], 'jitsi-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/jitsi'),
        ], 'jitsi-views');

        $this->loadRoutesFrom(__DIR__ . '/../routes/jitsi.php');

        $this->registerBladeComponents();
    }

    protected function registerBladeComponents(): void
    {
        Blade::component('jitsi-embed', JitsiEmbed::class);
        Blade::component('embed', JitsiEmbed::class);
    }

    public static function routes(): void
    {
        require __DIR__ . '/../routes/jitsi.php';
    }
}
