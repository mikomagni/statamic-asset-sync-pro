<?php
// statamic-asset-sync-pro/src/Commands/AssetsPush.php

namespace MikoMagni\RsyncCommands\Commands;
use Statamic\Console\RunsInPlease;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class AssetsPush extends AbstractAssetsCommand
{
    use RunsInPlease;

    protected $signature = 'assets:push
                        {--only-missing : Only push missing files}
                        {--delete : Delete files that don\'t exist locally}
                        {--dry-run : Perform a dry run without actual file transfer}
                        {--interactive : Enable interactive mode with prompts}';

    protected $description = 'Push assets to remote server using rsync';

    public function handle(): int
    {
        $config = $this->initializeCommand('push');

        if (is_int($config)) {
            return $config;
        }

        $remotePaths = $config['remotePaths'];
        $localPaths = $config['localPaths'];
        $user = $config['user'];
        $server = $config['server'];
        $baseRemoteDir = $config['baseRemoteDir'];

        $rsyncOptions = $this->getRsyncOptions();

        $result = $this->executeRsyncCommands($remotePaths, $localPaths, $user, $server, $baseRemoteDir, $rsyncOptions, 'push');

        if ($result !== 0) {
            return $result;
        }

        if ($this->option('dry-run')) {
            info(trans('rsync::commands.rsync_push_dry_run_complete'));
        } else {
            info(trans('rsync::commands.rsync_push_complete'));

            if (config('rsync.clear_remote_stache') || config('rsync.clear_remote_cache') || config('rsync.clear_remote_glide')) {
                $safeCacheCommands = [];
                $allowedCommands = [
                    'php please stache:clear',
                    'php please cache:clear',
                    'php please glide:clear'
                ];

                if (config('rsync.clear_remote_stache')) {
                    $safeCacheCommands[] = "php please stache:clear";
                }

                if (config('rsync.clear_remote_cache')) {
                    $safeCacheCommands[] = "php please cache:clear";
                }

                if (config('rsync.clear_remote_glide')) {
                    $safeCacheCommands[] = "php please glide:clear";
                }

                foreach ($safeCacheCommands as $command) {
                    if (!in_array($command, $allowedCommands, true)) {
                        error(trans('rsync::commands.rsync_invalid_cache_command', ['command' => $command]));
                        return 1;
                    }
                }

                if (!empty($safeCacheCommands)) {
                    info(trans('rsync::commands.rsync_clearing_remote_caches', [
                        'caches' => implode(', ', array_map(function($cmd) {
                            return trim(str_replace('php please ', '', $cmd));
                        }, $safeCacheCommands))
                    ]));

                    $remoteCommands = "cd " . escapeshellarg($baseRemoteDir) . " && " . implode(" && ", $safeCacheCommands);
                    $command = sprintf(
                        'LC_ALL=C LANG=C ssh -o ConnectTimeout=30 -o BatchMode=yes -o StrictHostKeyChecking=accept-new %s %s',
                        escapeshellarg("{$user}@{$server}"),
                        escapeshellarg($remoteCommands)
                    );

                    try {
                        $process = Process::fromShellCommandline($command);
                        $process->setTimeout(60);
                        $process->mustRun();

                        info(trans('rsync::commands.rsync_remote_caches_cleared'));
                    } catch (ProcessFailedException $exception) {
                        error(trans('rsync::commands.rsync_remote_command_failed', [
                            'remoteCommand' => implode(', ', $safeCacheCommands),
                            'error' => $exception->getMessage()
                        ]));
                    }
                }
            }
        }

        return 0;
    }

}
