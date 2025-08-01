<?php
// statamic-asset-sync-pro/src/Commands/AbstractAssetsCommand.php

namespace MikoMagni\RsyncCommands\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use MikoMagni\RsyncCommands\Traits\HandlesPrompts;
use MikoMagni\RsyncCommands\Traits\HandlesProgressBar;
use MikoMagni\RsyncCommands\Traits\HandlesAsciiArt;
use function Laravel\Prompts\error;

abstract class AbstractAssetsCommand extends Command
{
    use HandlesPrompts, HandlesProgressBar, HandlesAsciiArt;
    protected function parseFileCountFromStats(string $output): int
    {
        $patterns = [
            '/Number of files:\s+(\d+)/',
            '/Number of regular files transferred:\s+(\d+)/',
            '/Total transferred file size:\s+[\d,]+\s+bytes\s+\((\d+)\s+files?\)/',
            '/sent\s+[\d,]+\s+bytes\s+received\s+[\d,]+\s+bytes.*\n.*\ntotal size is\s+[\d,]+\s+speedup is\s+[\d.]+\s+\((\d+)\s+files?\)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $output, $matches)) {
                return (int)$matches[1];
            }
        }

        $lines = explode("\n", $output);
        $fileCount = 0;
        foreach ($lines as $line) {
            if (preg_match('/^[>f].*\s+\d{4}\/\d{2}\/\d{2}\s+\d{2}:\d{2}:\d{2}/', trim($line))) {
                $fileCount++;
            }
        }

        return max($fileCount, 10);
    }

    protected function getTotalFileCount(array $paths): int
    {
        // Progress bar acts as a visual estimator rather than exact file count
        return 100;
    }

    protected function getRsyncOptions(): string
    {
        $options = [];

        if ($this->option('only-missing')) {
            $options[] = '--ignore-existing';
        }

        if ($this->option('delete')) {
            $options[] = '--delete';
        }

        if ($this->option('dry-run')) {
            $options[] = '--dry-run';
        }

        if ($maxSize = config('rsync.max_file_size')) {
            if (preg_match('/^[0-9]+[KMGT]?B?$/i', $maxSize)) {
                $options[] = "--max-size=" . escapeshellarg($maxSize);
            }
        }

        $excludeExtensions = config('rsync.exclude_file_extensions');
        if ($excludeExtensions) {
            foreach ($excludeExtensions as $extension) {
                if (preg_match('/^[a-zA-Z0-9_.-]+$/', $extension)) {
                    $options[] = "--exclude=" . escapeshellarg("*.$extension");
                }
            }
        }

        return implode(' ', $options);
    }


    protected function initializeCommand(string $commandName): array|int
    {
        if (config('rsync.display_ascii_art', true)) {
            $this->displayAsciiArt();
        }

        if ($this->option('interactive')) {
            $this->promptForMissingConfig();
            $this->promptForOptions();
        }

        if ($this->option('delete')) {
            $warningKey = $commandName === 'pull' ? 'rsync_pull_warning' : 'rsync_push_warning';
            $cancelKey = $commandName === 'pull' ? 'rsync_pull_operation_canceled' : 'rsync_push_operation_canceled';

            if (!$this->confirmDeletion(trans("rsync::commands.{$warningKey}"))) {
                error(trans("rsync::commands.{$cancelKey}"));
                return 0;
            }
        }

        $remotePaths = array_filter((array)config('rsync.remote_asset_paths', []));
        $localPaths = array_filter((array)config('rsync.local_asset_paths', []));

        if (!$this->validatePaths($remotePaths, $localPaths)) {
            return 1;
        }

        if (!$this->validateConfigurationWithDetails("assets:{$commandName}")) {
            return 1;
        }

        $user = config('rsync.server_user');
        $server = config('rsync.server_host');
        $baseRemoteDir = config('rsync.remote_app_dir');

        // Validate rsync binary availability
        if (!$this->validateRsyncAvailability()) {
            return 1;
        }

        if (!$this->validateSecurityConstraints($user, $server, $baseRemoteDir, $remotePaths, $localPaths)) {
            return 1;
        }

        return compact('remotePaths', 'localPaths', 'user', 'server', 'baseRemoteDir');
    }


    protected function executeRsyncCommands(array $remotePaths, array $localPaths, string $user, string $server, string $baseRemoteDir, string $rsyncOptions, string $direction): int
    {
        $outputBuffer = config('rsync.output_buffer');
        $buffers = [];

        $sshTestResult = $this->testSSHConnectivity($user, $server);
        if ($sshTestResult !== 0) {
            return $sshTestResult;
        }

        $totalFileCount = $this->getTotalFileCount($remotePaths);
        $this->initializeProgressBar($totalFileCount);

        if (config('rsync.assets_log') === true) {
            Log::info("Starting rsync {$direction} operation", [
                'paths' => $direction === 'pull' ? $remotePaths : $localPaths,
                'user' => $user,
                'server' => $server,
                'options' => $rsyncOptions,
                'direction' => $direction
            ]);
        }

        $pathsToIterate = $direction === 'pull' ? $remotePaths : $localPaths;

        $sshOptions = 'ssh -o ConnectTimeout=30 -o BatchMode=yes -o StrictHostKeyChecking=accept-new';

        foreach ($pathsToIterate as $index => $path) {
            if ($direction === 'pull') {
                $localPath = base_path($localPaths[$index]);
                $remotePath = $remotePaths[$index];
                $fullRemotePath = rtrim($baseRemoteDir, '/') . '/' . ltrim($remotePath, '/');

                $localDestination = is_dir($localPath) ? "{$localPath}/" : $localPath;

                if (config('rsync.assets_log') === true) {
                    Log::debug('Pull path construction', [
                        'original_local_path' => $localPaths[$index],
                        'computed_local_path' => $localPath,
                        'directory_exists' => is_dir($localPath),
                        'final_destination' => $localDestination
                    ]);
                }

                $command = sprintf(
                    'LC_ALL=C LANG=C rsync -avz -v --progress -e %s %s %s %s',
                    escapeshellarg($sshOptions),
                    $rsyncOptions,
                    escapeshellarg("{$user}@{$server}:{$fullRemotePath}/"),
                    escapeshellarg($localDestination)
                );
            } else {
                $localPath = base_path($localPaths[$index]);
                $fullRemotePath = rtrim($baseRemoteDir, '/') . '/' . ltrim($remotePaths[$index], '/');

                $command = sprintf(
                    'LC_ALL=C LANG=C rsync -avz -v --progress -e %s %s %s %s',
                    escapeshellarg($sshOptions),
                    $rsyncOptions,
                    escapeshellarg("{$localPath}/"),
                    escapeshellarg("{$user}@{$server}:{$fullRemotePath}/")
                );
            }

            $process = Process::fromShellCommandline($command);
            $process->setTimeout(null);

            if (config('rsync.assets_log') === true) {
                Log::info("Executing rsync command", [
                    'command' => $command,
                    'path_index' => $index + 1,
                    'total_paths' => count($pathsToIterate)
                ]);
            }

            try {
                $process->mustRun(function ($type, $buffer) use ($outputBuffer, &$buffers, $index) {
                    if ($outputBuffer) {
                        $buffers[] = $buffer;
                    }

                    if (config('rsync.assets_log') === true && !empty(trim($buffer))) {
                        Log::info("Rsync output", [
                            'path_index' => $index + 1,
                            'type' => $type === Process::OUT ? 'stdout' : 'stderr',
                            'output' => trim($buffer)
                        ]);
                    }

                    $this->updateProgressBar($buffer);
                });

                if (config('rsync.assets_log') === true) {
                    Log::info("Rsync command completed successfully", [
                        'path_index' => $index + 1,
                        'exit_code' => $process->getExitCode(),
                        'full_output' => $process->getOutput()
                    ]);
                }
            } catch (ProcessFailedException $exception) {
                $errorOutput = $exception->getProcess()->getErrorOutput();
                $standardOutput = $exception->getProcess()->getOutput();
                $exitCode = $exception->getProcess()->getExitCode();

                if (config('rsync.assets_log') === true) {
                    Log::error('Rsync failed', [
                        'error' => $exception->getMessage(),
                        'errorOutput' => $errorOutput,
                        'standardOutput' => $standardOutput,
                        'exitCode' => $exitCode,
                        'command' => $command
                    ]);
                }

                // Clear the progress bar before showing error
                $this->clearProgressBar();
                $this->handleRsyncError($errorOutput, $standardOutput, $exitCode, $command, $user, $server, $fullRemotePath, $direction, $localPath);
                return 1;
            }
        }

        if ($outputBuffer) {
            foreach ($buffers as $buffer) {
                $this->output->write($buffer);
            }
        }

        if (config('rsync.assets_log') === true) {
            Log::info("Rsync {$direction} operation completed successfully", [
                'total_paths_processed' => count($pathsToIterate),
                'direction' => $direction
            ]);
        }

        $this->finishProgressBar();
        return 0;
    }


    protected function handleRsyncError(string $errorOutput, string $standardOutput, int $exitCode, string $command, string $user, string $server, string $path, string $direction, string $localPath = ''): void
    {
        $allOutput = $errorOutput . "\n" . $standardOutput;

        if (str_contains($allOutput, 'ssh:') ||
            str_contains($allOutput, 'Could not resolve hostname') ||
            str_contains($allOutput, 'Connection refused') ||
            str_contains($allOutput, 'Permission denied') ||
            str_contains($allOutput, 'Authentication failed') ||
            str_contains($allOutput, 'Connection timed out')) {
            $this->handleSSHConnectionError($allOutput, $user, $server);
            return;
        }

        if (str_contains($allOutput, 'No such file or directory')) {
            if ($direction === 'push') {
                // for push check if it's a local source file issue vs remote destination issue
                if (str_contains($allOutput, 'rsync: change_dir') &&
                    !str_contains($allOutput, "{$user}@{$server}")) {
                    // local path doesn't exist
                    error(trans('rsync::commands.rsync_local_path_not_found', ['path' => $localPath]));
                } else {
                    // remote path doesn't exist
                    error(trans('rsync::commands.rsync_path_not_found', ['path' => $path, 'user' => $user]));
                }
            } else {
                // for pull check if it's a remote source file issue vs local destination issue
                if (str_contains($allOutput, 'rsync: change_dir') ||
                    str_contains($allOutput, 'rsync: link_stat') ||
                    str_contains($allOutput, "{$user}@{$server}")) {
                    // remote path doesn't exist
                    error(trans('rsync::commands.rsync_path_not_found', ['path' => $path, 'user' => $user]));
                } else {
                    // local path doesn't exist
                    error(trans('rsync::commands.rsync_local_path_not_found', ['path' => $localPath]));
                }
            }
            return;
        }

        if (str_contains($allOutput, 'failed: Permission denied') || str_contains($allOutput, 'permission denied')) {
            error(trans('rsync::commands.rsync_permission_denied_path', ['path' => $path, 'user' => $user]));
            return;
        }

        if (str_contains($allOutput, 'Operation not permitted') && str_contains($allOutput, '[receiver]')) {
            error(trans('rsync::commands.rsync_local_permission_denied', ['path' => $localPath]));
            return;
        }

        if (str_contains($allOutput, 'connection unexpectedly closed') ||
            str_contains($allOutput, 'broken pipe') ||
            str_contains($allOutput, 'Connection reset by peer')) {
            error(trans('rsync::commands.rsync_connection_lost'));
            return;
        }

        error(trans('rsync::commands.rsync_general_error', [
            'exit_code' => $exitCode,
            'command' => $command,
            'error' => trim($errorOutput ?: $standardOutput ?: 'Unknown error')
        ]));
    }


    protected function testSSHConnectivity(string $user, string $server): int
    {
        if (config('rsync.assets_log') === true) {
            Log::debug('SSH connectivity test starting', [
                'user' => $user,
                'server' => $server
            ]);
        }

        $testCommand = sprintf(
            'LC_ALL=C LANG=C ssh -o ConnectTimeout=10 -o BatchMode=yes -o StrictHostKeyChecking=accept-new %s %s 2>&1',
            escapeshellarg("{$user}@{$server}"),
            escapeshellarg('echo test')
        );

        $process = Process::fromShellCommandline($testCommand);
        $process->setTimeout(15);

        try {
            $process->run();

            if ($process->isSuccessful()) {
                if (config('rsync.assets_log') === true) {
                    Log::debug('SSH connectivity test successful', [
                        'user' => $user,
                        'server' => $server,
                        'command' => $testCommand
                    ]);
                }
                return 0;
            }

            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();
            $allOutput = $output . "\n" . $errorOutput;

            $this->handleSSHTestError($allOutput, $user, $server);
            return 1;

        } catch (ProcessFailedException $exception) {
            $errorOutput = $exception->getProcess()->getErrorOutput();
            $standardOutput = $exception->getProcess()->getOutput();
            $allOutput = $errorOutput . "\n" . $standardOutput;

            $this->handleSSHTestError($allOutput, $user, $server);
            return 1;
        }
    }

    protected function handleSSHTestError(string $output, string $user, string $server): void
    {
        $this->handleSSHConnectionError($output, $user, $server);
    }

    protected function isValidUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9._-]+$/', $username) && strlen($username) <= 64;
    }

    protected function isValidPath(string $path): bool
    {
        $normalizedPath = trim($path, '/');

        if (empty($normalizedPath)) {
            return false;
        }

        $dangerousPatterns = [
            '/\.\.\//',  // Directory traversal
            '/\$/',      // Variable expansion
            '/`/',       // Command substitution
            '/;/',       // Command separator
            '/\|/',      // Pipe
            '/&/',       // Background process
            '/>/',       // Redirection
            '/</',       // Redirection
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $path)) {
                return false;
            }
        }

        return preg_match('/^[a-zA-Z0-9._\/-]+$/', $path) && strlen($path) <= 255;
    }

    protected function validateSecurityConstraints(string $user, string $server, string $baseRemoteDir, array $remotePaths, array $localPaths): bool
    {
        if (!$this->isValidUsername($user)) {
            error(trans('rsync::commands.invalid_username', ['username' => $user]));
            return false;
        }

        if (!$this->isValidHostname($server)) {
            error(trans('rsync::commands.ssh_hostname_not_found', ['hostname' => $server]));
            return false;
        }

        if (!$this->isValidPath($baseRemoteDir)) {
            error(trans('rsync::commands.invalid_remote_dir', ['dir' => $baseRemoteDir]));
            return false;
        }

        foreach ($remotePaths as $remotePath) {
            if (!$this->isValidPath($remotePath)) {
                error(trans('rsync::commands.invalid_remote_path', ['path' => $remotePath]));
                return false;
            }
        }

        foreach ($localPaths as $localPath) {
            if (!$this->isValidPath($localPath)) {
                error(trans('rsync::commands.invalid_local_path', ['path' => $localPath]));
                return false;
            }
        }

        return true;
    }

    protected function validateRsyncAvailability(): bool
    {
        $process = Process::fromShellCommandline('which rsync 2>/dev/null || command -v rsync 2>/dev/null');
        $process->setTimeout(5);

        try {
            $process->run();

            if ($process->isSuccessful() && !empty(trim($process->getOutput()))) {
                if (config('rsync.assets_log') === true) {
                    Log::debug('Rsync binary found', [
                        'path' => trim($process->getOutput()),
                        'exit_code' => $process->getExitCode()
                    ]);
                }
                return true;
            }

            if (config('rsync.assets_log') === true) {
                Log::debug('Rsync binary check failed', [
                    'exit_code' => $process->getExitCode(),
                    'output' => $process->getOutput(),
                    'error_output' => $process->getErrorOutput()
                ]);
            }

        } catch (ProcessFailedException $exception) {
            if (config('rsync.assets_log') === true) {
                Log::warning('Rsync availability check threw exception', [
                    'error' => $exception->getMessage(),
                    'command' => $exception->getProcess()->getCommandLine()
                ]);
            }
        }

        error(trans('rsync::commands.rsync_binary_not_found'));
        return false;
    }

}
