<?php
// statamic-rsync-tools/src/resources/lang/en/commands.php

return [
    'rsync_push_warning' => '[!] This option will delete files from the remote server if they do not exist in your local environment. Are you sure you want to proceed? 🚨',
    'rsync_push_operation_canceled' => 'Operation canceled.',
    'rsync_push_complete' => '[✓] Assets push complete 🚀',
    'rsync_push_rsync_failed' => 'Rsync failed: :error',
    'rsync_push_dry_run_complete' => '[✓] Dry Run push complete',
    'rsync_pull_warning' => '[!] This option will delete files from your local environment if they do not exist on the remote server. Are you sure you want to proceed? 🚨',
    'rsync_pull_operation_canceled' => 'Operation canceled.',
    'rsync_pull_complete' => '[✓] Assets pull complete 🚀',
    'rsync_pull_rsync_failed' => 'Rsync failed: :error',
    'rsync_pull_dry_run_complete' => '[✓] Dry Run pull complete',
    'rsync_remote_command_failed' => '[!] Remote command \':remoteCommand\' failed: :error',
    'rsync_invalid_cache_command' => '[!] Invalid cache command \':command\' detected. Only predefined cache clearing commands are allowed.',
    'rsync_clearing_remote_caches' => '[⚡] Clearing remote caches: :caches',
    'rsync_remote_caches_cleared' => '[✓] Remote caches cleared successfully',
    'rsync_pull_directory_not_exist' => 'The directory \':path\' does not exist on the remote server.',
    'rsync_push_directory_not_exist' => 'The directory \':path\' does not exist on your local environment.',
    'rsync_ascii_not_found' => 'ASCII file not found.',
    'rsync_progress_starting' => 'Starting transfer...',
    'rsync_config_env_not_found' => 'No .env file found at: :path',
    'config_prompt_server_user' => 'What is your server username?',
    'config_prompt_server_user_placeholder' => 'e.g., forge',
    'config_prompt_server_host' => 'What is your server hostname or IP?',
    'config_prompt_server_host_placeholder' => 'e.g., example.com or 123.456.789.0',
    'config_validation_server_required' => 'Server hostname is required.',
    'config_validation_server_invalid' => 'Please enter a valid hostname or IP.',
    'config_prompt_remote_dir' => 'What is your remote application directory?',
    'config_prompt_remote_dir_placeholder' => 'e.g., /home/forge/example.com',
    'config_validation_remote_dir_required' => 'Remote application directory is required.',
    'config_validation_remote_dir_slash' => 'Path must start with /',
    'config_prompt_remote_paths' => 'What are your remote asset paths? (comma-separated)',
    'config_prompt_remote_paths_placeholder' => 'e.g., public/assets,public/img',
    'config_validation_remote_paths_required' => 'At least one remote asset path is required.',
    'config_validation_remote_paths_count' => 'Number of remote paths must match number of local paths (:count).',
    'config_prompt_local_paths' => 'What are your local asset paths? (comma-separated)',
    'config_prompt_local_paths_placeholder' => 'e.g., public/assets,public/img',
    'config_validation_local_paths_required' => 'At least one local asset path is required.',
    'config_validation_local_paths_count' => 'Number of local paths must match number of remote paths (:count).',
    'config_server_user_missing' => 'Missing RSYNC_SERVER_USER in .env file. Add your server username.',
    'config_server_host_missing' => 'Missing RSYNC_SERVER_HOST in .env file. Add your server hostname or IP address.',
    'config_remote_dir_missing' => 'Missing RSYNC_REMOTE_APPLICATION_DIR in .env file. Add your remote application directory path.',
    'config_remote_paths_missing' => 'Missing RSYNC_REMOTE_ASSETS_PATHS in .env file. Add comma-separated remote asset paths.',
    'config_local_paths_missing' => 'Missing RSYNC_LOCAL_ASSETS_PATHS in .env file. Add comma-separated local asset paths.',
    'config_multiple_missing' => 'Multiple configuration values are missing from your .env file:',
    'config_missing_suggestion' => 'Run with --interactive flag for guided configuration setup: php please :command --interactive',
    'config_paths_empty_suggestion' => 'Asset paths are not configured. Use --interactive mode for guided setup.',
    'config_paths_mismatch_suggestion' => 'Path count mismatch detected. Use --interactive mode to fix this issue.',

    // Info messages
    'config_adding_server_user' => 'Adding RSYNC_SERVER_USER=:user',
    'config_adding_server_host' => 'Adding RSYNC_SERVER_HOST=:host',
    'config_adding_remote_dir' => 'Adding RSYNC_REMOTE_APPLICATION_DIR=:dir',
    'config_adding_remote_paths' => 'Adding RSYNC_REMOTE_ASSETS_PATHS=:paths',
    'config_adding_local_paths' => 'Adding RSYNC_LOCAL_ASSETS_PATHS=:paths',
    'config_updating_remote_paths' => 'Updating RSYNC_REMOTE_ASSETS_PATHS=:paths',
    'config_updating_local_paths' => 'Updating RSYNC_LOCAL_ASSETS_PATHS=:paths',

    // Configuration summary
    'config_updated_summary' => '
Configuration updated:
Remote Paths (:remote_count):
- :remote_paths

Local Paths (:local_count):
- :local_paths
',

    // Option prompts
    'config_prompt_only_missing' => 'Do you want to only transfer missing files?',
    'config_prompt_delete_files' => 'Do you want to delete files that don\'t exist in the source?',

    // Path validation messages
    'config_paths_missing_warning' => '⚠ Missing asset paths configuration.

Remote Paths (:remote_count):
:remote_list

Local Paths (:local_count):
:local_list',
    'config_paths_mismatch_warning' => '⚠ Number of remote paths must match number of local paths.

Remote Paths (:remote_count):
- :remote_paths

Local Paths (:local_count):
- :local_paths',
    'config_none_configured' => 'None configured',

    // Path editing
    'config_path_mismatch_detected' => 'Path mismatch detected:',
    'config_remote_paths_info' => 'Remote paths: :paths',
    'config_local_paths_info' => 'Local paths: :paths',
    'config_select_paths_edit' => 'Which paths would you like to edit?',
    'config_edit_remote_paths' => 'Edit remote paths',
    'config_edit_local_paths' => 'Edit local paths',
    'config_reenter_remote_paths' => 'Please re-enter your remote asset paths (comma-separated)',
    'config_reenter_local_paths' => 'Please re-enter your local asset paths (comma-separated)',

    // Error messages
    'config_env_update_error' => 'Error updating .env file: :error',
    'config_path_mismatch_display' => '
Path mismatch detected:

Remote paths: :remote_paths

Local paths: :local_paths
',

    // SSH and connection errors
    'ssh_hostname_not_found' => '[!] Cannot connect to server \':hostname\'. Please check:

• The hostname or IP address is correct
• The server is online and accessible
• Your internet connection is working',
    'ssh_connection_refused' => '[!] Connection refused by \':hostname\'. Please check:

• The server is running SSH service (port 22)
• Firewall settings allow SSH connections
• The hostname/IP is correct',
    'ssh_authentication_failed' => '[!] Authentication failed for user \':user\' on \':hostname\'. Please check:

• Username is correct (current: :user)
• SSH keys are properly configured
• User has SSH access to the server',
    'ssh_permission_denied' => '[!] Permission denied for user \':user\' on \':hostname\'. Please check:

• Username is correct
• User account exists on the server
• SSH access is enabled for this user',
    'ssh_timeout' => '[!] Connection timeout to \':hostname\'. Please check:

• Server is online and reachable
• Network connectivity
• Firewall settings',
    'rsync_path_not_found' => '[!] Path \':path\' not found on remote server. Please check:

• The remote path exists
• User \':user\' has access to this directory
• The path is spelled correctly',
    'rsync_local_path_not_found' => '[!] Local path \':path\' not found. Please check:

• You have permission to access the parent directory
• The path is valid and properly formatted
• The parent directory exists and is writable',
    'rsync_permission_denied_path' => '[!] Permission denied accessing \':path\' on remote server. Please check:

• User \':user\' has read/write permissions
• Directory ownership and permissions
• Parent directory permissions',
    'rsync_local_permission_denied' => '[!] Permission denied writing to local path \':path\'. Please check:

• You have write permissions to the directory
• Parent directory permissions are correct
• Directory is not read-only or restricted by system policies
• Try: chmod 755 \':path\' or create in a different location',
    'rsync_connection_lost' => '[!] Connection lost during transfer. Please check:

• Network stability
• Server availability
• Try again with a smaller batch',
    'rsync_general_error' => '[!] Rsync operation failed with exit code :exit_code.

Command: :command

Error details:
:error',

    // Security validation errors
    'invalid_username' => '[!] Invalid username \':username\'. Usernames must contain only letters, numbers, dots, underscores, and hyphens (max 64 characters).',
    'invalid_remote_dir' => '[!] Invalid remote directory \':dir\'. Paths must not contain dangerous characters or patterns.',
    'invalid_remote_path' => '[!] Invalid remote path \':path\'. Paths must not contain dangerous characters or patterns.',
    'invalid_local_path' => '[!] Invalid local path \':path\'. Paths must not contain dangerous characters or patterns.',

    // System requirements
    'rsync_binary_not_found' => '[!] Rsync binary not found on this system. Please install rsync:

• macOS: brew install rsync
• Ubuntu/Debian: sudo apt-get install rsync
• Windows: Use WSL, Cygwin, or Git Bash
• More info: https://rsync.samba.org/',


];
