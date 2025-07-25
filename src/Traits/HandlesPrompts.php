<?php
// statamic-rsync-tools/src/Traits/HandlesPrompts.php

namespace MikoMagni\RsyncCommands\Traits;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\select;

trait HandlesPrompts
{
    protected function promptForMissingConfig(): bool
    {
        try {
            $envPath = base_path('.env');
            if (!File::exists($envPath)) {
                error(trans('rsync::commands.rsync_config_env_not_found', ['path' => $envPath]));
                return false;
            }

            $envContent = File::get($envPath);
            $updated = false;

            if (empty(config('rsync.server_user'))) {
                $user = text(
                    label: trans('rsync::commands.config_prompt_server_user'),
                    placeholder: trans('rsync::commands.config_prompt_server_user_placeholder')
                );

                info(trans('rsync::commands.config_adding_server_user', ['user' => $user]));

                if (!str_contains($envContent, 'RSYNC_SERVER_USER=')) {
                    $envContent .= PHP_EOL . "RSYNC_SERVER_USER={$user}";
                } else {
                    $envContent = preg_replace(
                        '/RSYNC_SERVER_USER=.*/',
                        "RSYNC_SERVER_USER={$user}",
                        $envContent
                    );
                }
                $updated = true;
                config(['rsync.server_user' => $user]);
            }

            if (empty(config('rsync.server_host'))) {
                $host = text(
                    label: trans('rsync::commands.config_prompt_server_host'),
                    placeholder: trans('rsync::commands.config_prompt_server_host_placeholder'),
                    validate: fn (string $value) => match (true) {
                        empty($value) => trans('rsync::commands.config_validation_server_required'),
                        !$this->isValidHostname($value) => trans('rsync::commands.config_validation_server_invalid'),
                        default => null
                    }
                );

                info(trans('rsync::commands.config_adding_server_host', ['host' => $host]));

                if (!str_contains($envContent, 'RSYNC_SERVER_HOST=')) {
                    $envContent .= PHP_EOL . "RSYNC_SERVER_HOST={$host}";
                } else {
                    $envContent = preg_replace(
                        '/RSYNC_SERVER_HOST=.*/',
                        "RSYNC_SERVER_HOST={$host}",
                        $envContent
                    );
                }
                $updated = true;
                config(['rsync.server_host' => $host]);
            }

            if (empty(config('rsync.remote_app_dir'))) {
                $remoteDir = text(
                    label: trans('rsync::commands.config_prompt_remote_dir'),
                    placeholder: trans('rsync::commands.config_prompt_remote_dir_placeholder'),
                    validate: fn (string $value) => match (true) {
                        empty($value) => trans('rsync::commands.config_validation_remote_dir_required'),
                        !str_starts_with($value, '/') => trans('rsync::commands.config_validation_remote_dir_slash'),
                        default => null
                    }
                );

                info(trans('rsync::commands.config_adding_remote_dir', ['dir' => $remoteDir]));

                if (!str_contains($envContent, 'RSYNC_REMOTE_APPLICATION_DIR=')) {
                    $envContent .= PHP_EOL . "RSYNC_REMOTE_APPLICATION_DIR={$remoteDir}";
                } else {
                    $envContent = preg_replace(
                        '/RSYNC_REMOTE_APPLICATION_DIR=.*/',
                        "RSYNC_REMOTE_APPLICATION_DIR={$remoteDir}",
                        $envContent
                    );
                }
                $updated = true;
                config(['rsync.remote_app_dir' => $remoteDir]);
            }

            if (empty(config('rsync.remote_asset_paths')) || empty(array_filter((array)config('rsync.remote_asset_paths')))) {
                $localPaths = array_filter((array)config('rsync.local_asset_paths', []));
                $remotePaths = text(
                    label: trans('rsync::commands.config_prompt_remote_paths'),
                    placeholder: trans('rsync::commands.config_prompt_remote_paths_placeholder'),
                    validate: fn (string $value) => match (true) {
                        empty($value) => trans('rsync::commands.config_validation_remote_paths_required'),
                        !empty($localPaths) && count(array_filter(explode(',', str_replace(' ', '', $value)))) !== count($localPaths) =>
                            trans('rsync::commands.config_validation_remote_paths_count', ['count' => count($localPaths)]),
                        default => null
                    }
                );

                info(trans('rsync::commands.config_adding_remote_paths', ['paths' => $remotePaths]));

                if (!str_contains($envContent, 'RSYNC_REMOTE_ASSETS_PATHS=')) {
                    $envContent .= PHP_EOL . "RSYNC_REMOTE_ASSETS_PATHS={$remotePaths}";
                } else {
                    $envContent = preg_replace(
                        '/RSYNC_REMOTE_ASSETS_PATHS=.*/',
                        "RSYNC_REMOTE_ASSETS_PATHS={$remotePaths}",
                        $envContent
                    );
                }
                $updated = true;
                config(['rsync.remote_asset_paths' => array_filter(explode(',', str_replace(' ', '', $remotePaths)))]);
            }

            if (empty(config('rsync.local_asset_paths')) || empty(array_filter((array)config('rsync.local_asset_paths')))) {
                $remotePaths = config('rsync.remote_asset_paths', []);
                $localPaths = text(
                    label: trans('rsync::commands.config_prompt_local_paths'),
                    placeholder: trans('rsync::commands.config_prompt_local_paths_placeholder'),
                    validate: fn (string $value) => match (true) {
                        empty($value) => trans('rsync::commands.config_validation_local_paths_required'),
                        count(array_filter(explode(',', str_replace(' ', '', $value)))) !== count($remotePaths) =>
                            trans('rsync::commands.config_validation_local_paths_count', ['count' => count($remotePaths)]),
                        default => null
                    }
                );

                info(trans('rsync::commands.config_adding_local_paths', ['paths' => $localPaths]));

                if (!str_contains($envContent, 'RSYNC_LOCAL_ASSETS_PATHS=')) {
                    $envContent .= PHP_EOL . "RSYNC_LOCAL_ASSETS_PATHS={$localPaths}";
                } else {
                    $envContent = preg_replace(
                        '/RSYNC_LOCAL_ASSETS_PATHS=.*/',
                        "RSYNC_LOCAL_ASSETS_PATHS={$localPaths}",
                        $envContent
                    );
                }
                $updated = true;
                config(['rsync.local_asset_paths' => array_filter(explode(',', str_replace(' ', '', $localPaths)))]);
            }

            $remotePaths = array_filter((array)config('rsync.remote_asset_paths', []));
            $localPaths = array_filter((array)config('rsync.local_asset_paths', []));

            if (empty($remotePaths) || empty($localPaths)) {
                $remoteList = empty($remotePaths) ? trans('rsync::commands.config_none_configured') : '- ' . implode("\n- ", $remotePaths);
                $localList = empty($localPaths) ? trans('rsync::commands.config_none_configured') : '- ' . implode("\n- ", $localPaths);

                $message = trans('rsync::commands.config_paths_missing_warning', [
                    'remote_count' => count($remotePaths),
                    'remote_list' => $remoteList,
                    'local_count' => count($localPaths),
                    'local_list' => $localList
                ]);

                error($message);
                return false;
            }

            if (count($remotePaths) !== count($localPaths)) {
                error(trans('rsync::commands.config_path_mismatch_detected'));
                info(trans('rsync::commands.config_remote_paths_info', ['paths' => implode(',', $remotePaths)]));
                info(trans('rsync::commands.config_local_paths_info', ['paths' => implode(',', $localPaths)]));

                $pathToEdit = select(
                    label: trans('rsync::commands.config_select_paths_edit'),
                    options: [
                        'remote' => trans('rsync::commands.config_edit_remote_paths'),
                        'local' => trans('rsync::commands.config_edit_local_paths')
                    ]
                );

                if ($pathToEdit === 'remote') {
                    $remotePaths = text(
                        label: trans('rsync::commands.config_reenter_remote_paths'),
                        placeholder: implode(',', $localPaths),
                        validate: fn (string $value) => match (true) {
                            empty($value) => trans('rsync::commands.config_validation_remote_paths_required'),
                            count(array_filter(explode(',', str_replace(' ', '', $value)))) !== count($localPaths) =>
                                trans('rsync::commands.config_validation_remote_paths_count', ['count' => count($localPaths)]),
                            default => null
                        }
                    );

                    info(trans('rsync::commands.config_updating_remote_paths', ['paths' => $remotePaths]));
                    $envContent = preg_replace(
                        '/RSYNC_REMOTE_ASSETS_PATHS=.*/',
                        "RSYNC_REMOTE_ASSETS_PATHS={$remotePaths}",
                        $envContent
                    );
                    config(['rsync.remote_asset_paths' => array_filter(explode(',', str_replace(' ', '', $remotePaths)))]);
                } else {
                    $localPaths = text(
                        label: trans('rsync::commands.config_reenter_local_paths'),
                        placeholder: implode(',', $remotePaths),
                        validate: fn (string $value) => match (true) {
                            empty($value) => trans('rsync::commands.config_validation_local_paths_required'),
                            count(array_filter(explode(',', str_replace(' ', '', $value)))) !== count($remotePaths) =>
                                trans('rsync::commands.config_validation_local_paths_count', ['count' => count($remotePaths)]),
                            default => null
                        }
                    );

                    info(trans('rsync::commands.config_updating_local_paths', ['paths' => $localPaths]));
                    $envContent = preg_replace(
                        '/RSYNC_LOCAL_ASSETS_PATHS=.*/',
                        "RSYNC_LOCAL_ASSETS_PATHS={$localPaths}",
                        $envContent
                    );

                    $localPathsArray = array_filter(explode(',', str_replace(' ', '', $localPaths)));
                    config(['rsync.local_asset_paths' => $localPathsArray]);

                    $localPaths = $localPathsArray;
                }

                $updated = true;
            }

            if ($updated) {
                File::put($envPath, $envContent);

                $remotePathsArray = is_array($remotePaths) ? $remotePaths : array_filter(explode(',', str_replace(' ', '', $remotePaths)));
                $localPathsArray = is_array($localPaths) ? $localPaths : array_filter(explode(',', str_replace(' ', '', $localPaths)));

                $message = trans('rsync::commands.config_updated_summary', [
                    'remote_count' => count($remotePathsArray),
                    'remote_paths' => implode("\n- ", $remotePathsArray),
                    'local_count' => count($localPathsArray),
                    'local_paths' => implode("\n- ", $localPathsArray)
                ]);

                info($message);
            }
        } catch (\Exception $e) {
            error(trans('rsync::commands.config_env_update_error', ['error' => $e->getMessage()]));
            Log::error("Error updating .env file: " . $e->getMessage());
        }

        return $updated;
    }

    protected function promptForOptions()
    {
        if (!$this->option('only-missing') && !$this->option('delete')) {
            if (confirm(
                label: trans('rsync::commands.config_prompt_only_missing'),
                default: false
            )) {
                $this->input->setOption('only-missing', true);
            } elseif (confirm(
                label: trans('rsync::commands.config_prompt_delete_files'),
                default: false
            )) {
                $this->input->setOption('delete', true);
            }
        }
    }

    protected function confirmDeletion($message)
    {
        return confirm(
            label: $message,
            default: false
        );
    }

    protected function validatePaths($remotePaths, $localPaths): bool
    {
        if (empty($remotePaths) || empty($localPaths)) {
            $remoteList = empty($remotePaths) ? trans('rsync::commands.config_none_configured') : '- ' . implode("\n- ", $remotePaths);
            $localList = empty($localPaths) ? trans('rsync::commands.config_none_configured') : '- ' . implode("\n- ", $localPaths);

            $message = trans('rsync::commands.config_paths_missing_warning', [
                'remote_count' => count($remotePaths),
                'remote_list' => $remoteList,
                'local_count' => count($localPaths),
                'local_list' => $localList
            ]);

            error($message);
            error(trans('rsync::commands.config_paths_empty_suggestion'));
            return false;
        }

        if (count($remotePaths) !== count($localPaths)) {
            $message = trans('rsync::commands.config_paths_mismatch_warning', [
                'remote_count' => count($remotePaths),
                'remote_paths' => implode("\n- ", $remotePaths),
                'local_count' => count($localPaths),
                'local_paths' => implode("\n- ", $localPaths)
            ]);

            error($message);
            error(trans('rsync::commands.config_paths_mismatch_suggestion'));
            return false;
        }

        return true;
    }

    protected function updateEnvVariable(string $key, string $value, array $remotePaths = [], array $localPaths = []): bool
    {
        try {
            $envPath = base_path('.env');
            $envContent = File::get($envPath);

            if (!str_contains($envContent, "{$key}=")) {
                $envContent .= PHP_EOL . "{$key}={$value}";
            } else {
                $envContent = preg_replace(
                    "/{$key}=.*/",
                    "{$key}={$value}",
                    $envContent
                );
            }

            File::put($envPath, $envContent);

            if (!empty($remotePaths) && !empty($localPaths)) {
                $message = trans('rsync::commands.config_updated_summary', [
                    'remote_count' => count($remotePaths),
                    'remote_paths' => implode("\n- ", $remotePaths),
                    'local_count' => count($localPaths),
                    'local_paths' => implode("\n- ", $localPaths)
                ]);

                info($message);
            }

            return true;
        } catch (\Exception $e) {
            error(trans('rsync::commands.config_env_update_error', ['error' => $e->getMessage()]));
            return false;
        }
    }

    protected function displayPathMismatch(array $remotePaths, array $localPaths): void
    {
        $message = trans('rsync::commands.config_path_mismatch_display', [
            'remote_paths' => implode(',', $remotePaths),
            'local_paths' => implode(',', $localPaths)
        ]);

        info($message);
    }

    protected function validateConfigurationWithDetails(string $commandName = 'assets:pull'): bool
    {
        $user = config('rsync.server_user');
        $server = config('rsync.server_host');
        $baseRemoteDir = config('rsync.remote_app_dir');
        $remotePathsRaw = config('rsync.remote_asset_paths');
        $localPaths = array_filter((array)config('rsync.local_asset_paths', []));

        $missingConfigs = [];

        if (!$user) {
            $missingConfigs[] = trans('rsync::commands.config_server_user_missing');
        }

        if (!$server) {
            $missingConfigs[] = trans('rsync::commands.config_server_host_missing');
        }

        if (!$baseRemoteDir) {
            $missingConfigs[] = trans('rsync::commands.config_remote_dir_missing');
        }

        if (empty($remotePathsRaw)) {
            $missingConfigs[] = trans('rsync::commands.config_remote_paths_missing');
        }

        if (empty($localPaths)) {
            $missingConfigs[] = trans('rsync::commands.config_local_paths_missing');
        }

        if (!empty($missingConfigs)) {
            if (count($missingConfigs) === 1) {
                error($missingConfigs[0]);
            } else {
                error(trans('rsync::commands.config_multiple_missing'));
                foreach ($missingConfigs as $config) {
                    error("• " . $config);
                }
            }

            error("\n" . trans('rsync::commands.config_missing_suggestion', ['command' => $commandName]));
            return false;
        }

        return true;
    }


    protected function isValidHostname(string $value): bool
    {
        $value = trim($value);

        if (empty($value)) {
            return false;
        }

        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return true;
        }

        if (preg_match('/^[\d.]+$/', $value)) {
            return false;
        }

        if (strtolower($value) === 'localhost') {
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            if (preg_match('/[a-zA-Z]/', $value)) {
                return true;
            }
        }

        $pattern = '/^(?=.{1,253}$)(?:(?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)*[A-Za-z0-9-]{1,63}(?<!-)$/';
        if (preg_match($pattern, $value) && preg_match('/[a-zA-Z]/', $value)) {
            return true;
        }

        return false;
    }

    protected function handleSSHConnectionError(string $output, string $user, string $server): void
    {
        // hostname
        if (str_contains($output, 'Could not resolve hostname') || str_contains($output, 'nodename nor servname provided')) {
            error(trans('rsync::commands.ssh_hostname_not_found', ['hostname' => $server]));
            return;
        }

        // connection refused
        if (str_contains($output, 'Connection refused') || str_contains($output, 'ssh: connect to host')) {
            error(trans('rsync::commands.ssh_connection_refused', ['hostname' => $server]));
            return;
        }

        // authentication failures
        if (str_contains($output, 'Permission denied (publickey)') ||
            str_contains($output, 'Authentication failed') ||
            str_contains($output, 'Access denied')) {
            error(trans('rsync::commands.ssh_authentication_failed', ['user' => $user, 'hostname' => $server]));
            return;
        }

        // permission denied
        if (str_contains($output, 'Permission denied') && !str_contains($output, 'publickey')) {
            error(trans('rsync::commands.ssh_permission_denied', ['user' => $user, 'hostname' => $server]));
            return;
        }

        // timeout
        if (str_contains($output, 'Connection timed out') || (str_contains($output, 'ssh: connect to host') && str_contains($output, 'timed out'))) {
            error(trans('rsync::commands.ssh_timeout', ['hostname' => $server]));
            return;
        }

        // generic connection error
        error(trans('rsync::commands.ssh_connection_refused', ['hostname' => $server]));
    }
}
