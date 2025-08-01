# Changelog

All notable changes to Statamic Asset Sync Pro will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.3] - 01-08-2025
- Markdown fix for Statamic Marketplace

## [1.0.2] - 01-08-2025

### Changed
- **BREAKING**: Package name changed from `mikomagni/statamic-rsync-tools` to `mikomagni/statamic-asset-sync-pro`
- **BREAKING**: Plugin name changed from "Statamic Rsync Tools" to "Statamic Asset Sync Pro"
- Updated tagline to "Keep your assets in sync between environments, and keep your Git clean"
- Updated all documentation and file references to reflect new naming
- Updated repository URLs and support links

### Migration Notes
- If upgrading from v1.0.1 or earlier, update your composer.json to require `mikomagni/statamic-asset-sync-pro` instead of `mikomagni/statamic-rsync-tools`
- All functionality remains identical - only naming has changed
- No configuration changes required

## [1.0.1] - 25-07-2025

### Fixed
- Updated laravel/prompts constraint to ^0.3 for better compatibility with modern Laravel/Statamic installations

## [1.0.0] - 25-07-2025

### Added
- Initial release of Statamic Rsync Tools
- `assets:pull` command for downloading files from remote server
- `assets:push` command for uploading files to remote server
- Interactive configuration mode with guided prompts
- Real-time progress bar with percentage-based display (/100)
- Optional progress hints showing file names and transfer details (`RSYNC_DISPLAY_PROGRESS_HINTS`)
- Comprehensive logging and error handling with actionable error messages
- ASCII art for enhanced terminal experience (configurable via `RSYNC_DISPLAY_ASCII_ART`)
- Automatic cache clearing (Stache, Glide, application cache) with user feedback
- Smart path handling (accepts both `path` and `/path` formats automatically)
- Support for selective transfers (`--only-missing`, `--delete`)
- Dry run mode for previewing changes without actual transfer
- Automatic SSH host key management with `StrictHostKeyChecking=accept-new`
- Cross-platform compatibility (Windows, macOS, Linux)
- Intelligent error detection distinguishing local vs remote path issues
- Permission error handling for both local and remote scenarios
- Comprehensive security features with input validation and safe command execution

[1.0.2]: https://github.com/mikomagni/statamic-asset-sync-pro/releases/tag/v1.0.2
[1.0.1]: https://github.com/mikomagni/statamic-rsync-tools/releases/tag/v1.0.1
[1.0.0]: https://github.com/mikomagni/statamic-rsync-tools/releases/tag/v1.0.0
