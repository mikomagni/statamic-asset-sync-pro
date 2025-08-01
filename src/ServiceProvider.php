<?php
// statamic-asset-sync-pro/src/ServiceProvider.php

namespace MikoMagni\RsyncCommands;
use MikoMagni\RsyncCommands\Commands\AssetsPull;
use MikoMagni\RsyncCommands\Commands\AssetsPush;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $commands = [
        AssetsPull::class,
        AssetsPush::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'rsync');

        $this->publishes([
            __DIR__ . '/resources/config/rsync.php' => config_path('rsync.php'),
        ], 'config');
    }
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/resources/config/rsync.php',
            'rsync'
        );
    }
}
