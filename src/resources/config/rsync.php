<?php
// statamic-asset-sync-pro/src/resources/config/rsync.php

return [
    'display_ascii_art' => env('RSYNC_DISPLAY_ASCII_ART', true),
    'display_progress_hints' => env('RSYNC_DISPLAY_PROGRESS_HINTS', false),
    'output_buffer' => env('RSYNC_DISPLAY_OUTPUT_BUFFER', false),
    'assets_log' => env('RSYNC_ASSETS_LOG', false),
    'server_user' => env('RSYNC_SERVER_USER'),
    'server_host' => env('RSYNC_SERVER_HOST'),
    'remote_app_dir' => rtrim(env('RSYNC_REMOTE_APPLICATION_DIR') ?? '', '/'),
    'remote_asset_paths' => array_filter(explode(',', env('RSYNC_REMOTE_ASSETS_PATHS', ''))),
    'local_asset_paths' => array_filter(explode(',', env('RSYNC_LOCAL_ASSETS_PATHS', ''))),
    'exclude_file_extensions' => array_filter(explode(',', env('RSYNC_EXCLUDE_FILE_EXTENSIONS') ?? '')),
    'max_file_size' => env('RSYNC_MAX_TRANSFER_SIZE'),
    'file_transfer_regex' => env('RSYNC_FILE_TRANSFER_REGEX', '/\s+[\d,]+\s+\d+%\s+[\d.]+[KMGT]?B\/s\s+\d+:\d+:\d+/'),
    'clear_local_stache' => env('RSYNC_CLEAR_LOCAL_STACHE', false),
    'clear_local_cache' => env('RSYNC_CLEAR_LOCAL_CACHE', false),
    'clear_local_glide' => env('RSYNC_CLEAR_LOCAL_GLIDE', false),
    'clear_remote_stache' => env('RSYNC_CLEAR_REMOTE_STACHE', false),
    'clear_remote_cache' => env('RSYNC_CLEAR_REMOTE_CACHE', false),
    'clear_remote_glide' => env('RSYNC_CLEAR_REMOTE_GLIDE', false),
];
