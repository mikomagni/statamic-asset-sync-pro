# Statamic Rsync Tools

A Statamic addon that provides rsync-based file synchronisation commands to transfer any files between local and remote environments.

## Features

- **Fast file transfers** using rsync
- **Push files** from local to remote server
- **Pull files** from remote to local environment
- **Selective transfers** with `--only-missing` and `--delete` options
- **Dry run mode** to preview changes without transferring
- **Interactive mode** with guided configuration setup
- **Real-time progress bar** with transfer feedback
- **Comprehensive logging** for detailed operation tracking
- **ASCII art** for enhanced terminal experience
- **Automatic cache clearing** for Stache, Glide, and application cache
- **Built-in security** with input validation and safe command execution
- **Language Support** for all command messages, prompts, and error handling

## Important Safety Warning

**Rsync is a powerful synchronisation tool that can permanently delete or overwrite files. Please read these warnings carefully:**

🚨 **ALWAYS use `--dry-run` first** to preview changes before executing any command:
```bash
php please assets:pull --dry-run
php please assets:push --dry-run
```

**The `--delete` option is destructive** and will permanently remove files:
- `assets:pull --delete` removes local files not on the remote server
- `assets:push --delete` removes remote files not in local directories
- **There is no undo** - deleted files are gone forever

**Test in development first** before using on production:
- Verify your paths are correct
- Ensure you have backups of important data
- Double-check remote and local path configurations match

**Common scenarios that cause data loss:**
- Wrong path configuration (syncing to wrong directories)
- Mismatched local/remote path counts
- Using `--delete` without understanding its impact
- Not testing with `--dry-run` first

> 💡 **Best Practice**: Always run commands with `--dry-run` first, review the output carefully, then run without `--dry-run` only when you're certain the changes are correct.

## Requirements

- Statamic 5
- PHP 8.1+
- rsync installed on your system (GPL-licensed, installed separately)
- SSH access to remote server

## Installation

### Via Composer

1. Install the addon:

```bash
composer require mikomagni/statamic-rsync-tools
```

2. Install rsync on your system (required dependency, GPL-licensed):
   - **macOS**: `brew install rsync`
   - **Ubuntu/Debian**: `sudo apt-get install rsync`
   - **Windows**: Use WSL, Cygwin, or Git Bash
   - **More info**: https://rsync.samba.org/

3. Clear configuration cache:

```bash
php please config:clear
```

4. Verify installation:

```bash
php please help assets:pull
```


## Configuration

Add the following variables to your `.env` file:

```bash
# Server Configuration
RSYNC_SERVER_USER=your_username
RSYNC_SERVER_HOST=your_server_ip
RSYNC_REMOTE_APPLICATION_DIR=/path/to/remote/app

# Asset Paths (comma-separated)
RSYNC_REMOTE_ASSETS_PATHS=public/images,public/assets,public/files
RSYNC_LOCAL_ASSETS_PATHS=public/images,public/assets,public/files

# Transfer Options
RSYNC_MAX_TRANSFER_SIZE=100M
RSYNC_EXCLUDE_FILE_EXTENSIONS=txt,log,tmp

# Display & Logging
RSYNC_DISPLAY_OUTPUT_BUFFER=false
RSYNC_DISPLAY_PROGRESS_HINTS=false
RSYNC_ASSETS_LOG=false
RSYNC_DISPLAY_ASCII_ART=true

# Cache Clearing - Remote (after push)
RSYNC_CLEAR_REMOTE_STACHE=false
RSYNC_CLEAR_REMOTE_GLIDE=false
RSYNC_CLEAR_REMOTE_CACHE=false

# Cache Clearing - Local (after pull)
RSYNC_CLEAR_LOCAL_STACHE=false
RSYNC_CLEAR_LOCAL_GLIDE=false
RSYNC_CLEAR_LOCAL_CACHE=false

```

### Example Configuration

```bash
RSYNC_SERVER_USER=forge
RSYNC_SERVER_HOST=123.456.789.0
RSYNC_REMOTE_APPLICATION_DIR=/home/forge/myapp.com
RSYNC_REMOTE_ASSETS_PATHS=public/images,public/assets
RSYNC_LOCAL_ASSETS_PATHS=public/images,public/assets
RSYNC_EXCLUDE_FILE_EXTENSIONS=txt,log
RSYNC_MAX_TRANSFER_SIZE=100M
RSYNC_DISPLAY_ASCII_ART=true
RSYNC_ASSETS_LOG=true
```

## Usage

### Interactive Mode (Recommended for First Setup)

```bash
php please assets:pull --interactive
php please assets:push --interactive
```

Interactive mode will guide you through configuration setup and provide options for transfer preferences.

### Pull Assets (Remote → Local)

Download assets from remote server to local environment:

```bash
# Pull all assets
php please assets:pull

# Pull only missing files
php please assets:pull --only-missing

# Delete local files not on remote (🚨 DESTRUCTIVE - test with --dry-run first!)
php please assets:pull --delete

# Preview changes without transferring
php please assets:pull --dry-run

# Combine options
php please assets:pull --only-missing --dry-run
```

### Push Assets (Local → Remote)

Upload assets from local environment to remote server:

```bash
# Push all assets
php please assets:push

# Push only missing files
php please assets:push --only-missing

# Delete remote files not in local (🚨 DESTRUCTIVE - test with --dry-run first!)
php please assets:push --delete

# Preview changes without transferring
php please assets:push --dry-run

# Combine options
php please assets:push --only-missing --dry-run
```

## Command Options

| Option | Description |
|--------|-------------|
| `--interactive` | Enable interactive mode with guided prompts |
| `--only-missing` | Transfer only missing files |
| `--delete` | 🚨 **DESTRUCTIVE**: Delete files that don't exist in source |
| `--dry-run` | **RECOMMENDED**: Preview changes without actual transfer |

> 🚨 **CRITICAL WARNING**: The `--delete` option will **permanently remove files** with no recovery possible. **ALWAYS** use `--dry-run` first to preview changes, especially with `--delete`.

## Configuration Reference

### Server Settings
- `RSYNC_SERVER_USER`: SSH username for remote server
- `RSYNC_SERVER_HOST`: Remote server hostname or IP address
- `RSYNC_REMOTE_APPLICATION_DIR`: Full path to remote application directory

### Asset Paths
- `RSYNC_REMOTE_ASSETS_PATHS`: Comma-separated remote asset directories (relative to remote application directory)
- `RSYNC_LOCAL_ASSETS_PATHS`: Comma-separated local asset directories (relative to project root, must match remote paths order)

> 💡 **Path Format**: Both `public/images` and `/public/images` work identically - leading slashes are automatically normalised.

### Transfer Options
- `RSYNC_MAX_TRANSFER_SIZE`: Maximum file size limit (e.g., `100M`, `1G`)
- `RSYNC_EXCLUDE_FILE_EXTENSIONS`: File extensions to exclude from sync
- `RSYNC_FILE_TRANSFER_REGEX`: Custom regex for progress bar tracking

### Display & Logging
- `RSYNC_DISPLAY_OUTPUT_BUFFER`: Show rsync output in real-time (`true`/`false`)
- `RSYNC_DISPLAY_PROGRESS_HINTS`: Show file names and transfer details below progress bar (`true`/`false`)
- `RSYNC_ASSETS_LOG`: Enable detailed logging to `storage/logs` (`true`/`false`)
- `RSYNC_DISPLAY_ASCII_ART`: Show ASCII art during operations (`true`/`false`)

### Cache Management
- `RSYNC_CLEAR_REMOTE_*`: Clear remote caches after push operations
- `RSYNC_CLEAR_LOCAL_*`: Clear local caches after pull operations
- Available cache types: `STACHE`, `GLIDE`, `CACHE`


## Logging

When `RSYNC_ASSETS_LOG=true`, detailed logs are written to `storage/logs/laravel.log` including:

- Operation start/completion
- Individual rsync commands executed
- Real-time transfer output
- File-by-file transfer details
- Error messages and debugging info

## Customisation

### Custom ASCII Art

Create custom ASCII art files:
- `resources/ascii/pull.txt` - Displayed during pull operations
- `resources/ascii/push.txt` - Displayed during push operations

### Language File Customisation

The addon includes comprehensive language support for all command messages, prompts, and error handling. You can customise or translate these messages by publishing and modifying the language file.

**Language File Location:**
```
src/resources/lang/en/commands.php
```

**Key Translation Categories:**
- **Operation Messages**: Success, warning, and completion messages
- **Interactive Prompts**: Configuration setup questions and validation
- **Error Handling**: SSH connection errors, path validation, and rsync failures
- **Progress Feedback**: Transfer status and cache clearing messages

**Example Translations:**
```php
<?php
return [
    // Operation completion messages
    'rsync_pull_complete' => '[✓] Assets pull complete 🚀',
    'rsync_push_complete' => '[✓] Assets push complete 🚀',
    
    // Interactive configuration prompts
    'config_prompt_server_user' => 'What is your server username?',
    'config_prompt_server_host' => 'What is your server hostname or IP?',
    
    // Warning messages
    'rsync_pull_warning' => '[!] This option will delete files from your local environment if they do not exist on the remote server. Are you sure you want to proceed? 🚨',
    
    // Error messages
    'ssh_authentication_failed' => '[!] Authentication failed for user \':user\' on \':hostname\'. Please check: • Username is correct • SSH keys are properly configured',
];
```

**Customisation Notes:**
- Use `:placeholder` syntax for dynamic values (e.g., `:user`, `:hostname`, `:path`)

### Progress Bar Customisation

The progress bar uses a regex pattern to parse rsync output and track transfer progress. If the progress bar isn't working correctly, you can customise the regex pattern.

**Standard Pattern (default):**
```bash
RSYNC_FILE_TRANSFER_REGEX="/\s+[\d,]+\s+\d+%\s+[\d.]+[KMGT]?B\/s\s+\d+:\d+:\d+/"
```

This pattern matches rsync progress output like:
```
    1,024,576  45%  2.5MB/s    0:00:15
   12,345,678  78%  8.1KB/s    0:01:23
```

**Custom Pattern:**
```bash
RSYNC_FILE_TRANSFER_REGEX="/your-custom-regex-pattern/"
```

> 💡 **Tip**: If you're experiencing progress bar issues, first try the standard pattern above. The regex must match the numerical progress percentage in rsync's output.

## Troubleshooting

### Common Issues

**Progress bar not moving?**
- Ensure rsync is properly installed with progress support
- Verify `RSYNC_FILE_TRANSFER_REGEX` matches your rsync output format
- Try the standard regex pattern: `/\s+[\d,]+\s+\d+%\s+[\d.]+[KMGT]?B\/s\s+\d+:\d+:\d+/`
- Enable logging with `RSYNC_ASSETS_LOG=true` and check `storage/logs/laravel.log` for transfer details
- Test with `--dry-run` to see if rsync produces expected progress output

**SSH connection issues?**
- Verify SSH key authentication is set up
- Test manual SSH connection: `ssh user@host`
- Check `RSYNC_SERVER_USER` and `RSYNC_SERVER_HOST` values

**Path configuration problems?**
- Use `--interactive` mode for guided setup
- Ensure remote and local path counts match
- Verify paths exist on both servers

### Data Safety & Recovery

**🚨 Accidentally deleted files with `--delete`?**
- **Important**: There is no built-in undo functionality
- Check your backups immediately
- For remote files: Look for server backups or snapshots
- For local files: Check system trash/recycle bin or Time Machine/File History

**🔒 Preventing data loss:**
```bash
# ALWAYS test first with dry-run
php please assets:pull --delete --dry-run

# Review the output carefully - look for files marked for deletion
# Only proceed if you're absolutely certain

# Enable logging for detailed records
RSYNC_ASSETS_LOG=true
```

**📋 Pre-flight checklist:**
- [ ] Verified paths in `.env` are correct
- [ ] Tested with `--dry-run` first
- [ ] Reviewed dry-run output for unexpected deletions
- [ ] Have recent backups of important data
- [ ] Understand what `--delete` will remove

### Security

This addon prioritises security with comprehensive built-in safeguards:

### SSH Connection Security
- **Host Key Verification**: Uses `StrictHostKeyChecking=accept-new` to detect host key changes on subsequent connections
- **MITM Protection**: Prevents man-in-the-middle attacks after initial host key acceptance
- **Connection Timeouts**: Prevents hanging operations with reasonable timeout values (10-30 seconds)
- **Batch Mode**: Non-interactive SSH connections for automation safety

### Input Validation & Sanitisation
- **Username Validation**: Only safe characters allowed (letters, numbers, dots, underscores, hyphens)
- **Path Security**: Comprehensive checks for directory traversal (`../`), command injection, and shell metacharacters
- **Command Allowlisting**: Remote cache commands are strictly validated against predefined safe commands
- **Regex Validation**: User-supplied regex patterns are validated to prevent ReDoS attacks

### Command Protection
- **Shell Argument Escaping**: All arguments properly escaped using `escapeshellarg()` to prevent injection
- **Locale Forcing**: SSH and rsync use English locale (`LC_ALL=C LANG=C`) for consistent parsing
- **File Extension Validation**: Extensions and size limits validated before rsync execution
- **Process Isolation**: Commands executed in isolated processes with proper error handling

### Error Handling
- Comprehensive error detection with user-friendly messages
- No sensitive information exposed in error output
- Graceful fallbacks when operations fail

## Getting Help

```bash
# View command help
php please help assets:pull
php please help assets:push

# Test with dry run
php please assets:pull --dry-run --interactive
```

## License

This addon is commercial software. Usage requires a valid license purchased through the [Statamic Marketplace](https://statamic.com/marketplace). See [LICENSE](LICENSE) for full terms.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
