<?php
// statamic-asset-sync-pro/src/Commands/AssetsPull.php

namespace MikoMagni\RsyncCommands\Commands;
use Statamic\Console\RunsInPlease;
use function Laravel\Prompts\info;

class AssetsPull extends AbstractAssetsCommand
{
    use RunsInPlease;

    protected $signature = 'assets:pull
                        {--only-missing : Only pull missing files}
                        {--delete : Delete files that don\'t exist on the server}
                        {--dry-run : Perform a dry run without actual file transfer}
                        {--interactive : Enable interactive mode with prompts}';

    protected $description = 'Pull assets from remote server using rsync';

    public function handle(): int
    {
        $config = $this->initializeCommand('pull');

        if (is_int($config)) {
            return $config;
        }

        $remotePaths = $config['remotePaths'];
        $localPaths = $config['localPaths'];
        $user = $config['user'];
        $server = $config['server'];
        $baseRemoteDir = $config['baseRemoteDir'];

        $rsyncOptions = $this->getRsyncOptions();

        $result = $this->executeRsyncCommands($remotePaths, $localPaths, $user, $server, $baseRemoteDir, $rsyncOptions, 'pull');

        if ($result !== 0) {
            return $result;
        }

        if ($this->option('dry-run')) {
            info(trans('rsync::commands.rsync_pull_dry_run_complete'));
        } else {
            info(trans('rsync::commands.rsync_pull_complete'));

            if (config('rsync.clear_local_stache') || config('rsync.clear_local_cache') || config('rsync.clear_local_glide')) {
                if (config('rsync.clear_local_stache')) {
                    $this->call('stache:clear');
                }

                if (config('rsync.clear_local_cache')) {
                    $this->call('cache:clear');
                }

                if (config('rsync.clear_local_glide')) {
                    $this->call('glide:clear');
                }
            }
        }

        return 0;
    }

}
