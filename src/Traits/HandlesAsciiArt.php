<?php
// statamic-asset-sync-pro/src/Traits/HandlesAsciiArt.php

namespace MikoMagni\RsyncCommands\Traits;
use Illuminate\Support\Facades\Log;

trait HandlesAsciiArt
{
    protected function displayAsciiArt(): void
    {
        if (!config('rsync.display_ascii_art', true)) {
            return;
        }

        $className = class_basename($this);
        $asciiFile = __DIR__ . '/../resources/ascii/' . strtolower(str_replace('Assets', '', $className)) . '.text';

        if (file_exists($asciiFile)) {
            echo file_get_contents($asciiFile);
        } else {
            if (config('rsync.assets_log') === true) {
                Log::warning(trans('rsync::commands.rsync_ascii_not_found'));
            }
        }

        $this->output->newLine();

    }
}
