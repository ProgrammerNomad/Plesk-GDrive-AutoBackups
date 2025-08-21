# Plesk GDrive AutoBackups Extension

**Automatically back up your Plesk server data to Google Drive.**

This Plesk extension provides seamless integration with Google Drive for automated server backups. Built following Plesk extension development best practices.

## Features

- **Automated Scheduled Backups** - Daily, weekly, or monthly backup schedules
- **Google Drive Integration** - Secure OAuth 2.0 connection to Google Drive
- **Selective Backups** - Choose specific directories and databases to back up
- **Retention Policies** - Automatically manage backup history and storage
- **Manual Backup Option** - Run on-demand backups with a single click
- **Comprehensive Logs** - Detailed activity tracking and error reporting
- **Plesk Integration** - Native Plesk UI and authentication system

## Project Structure

```
plesk-gdrive-autobackups/
├── meta.xml                           # Extension metadata
├── composer.json                      # PHP dependencies
├── plib/                             # Extension core files
│   ├── ui.php                        # Main UI entry point
│   ├── library.php                   # Extension initialization
│   ├── controllers/                  # MVC Controllers
│   │   └── IndexController.php       # Main extension controller
│   ├── views/                        # View templates
│   │   └── scripts/index/index.phtml # Main UI template
│   ├── library/                      # Core classes
│   │   ├── ApiController.php         # Google API integration
│   │   └── BackupController.php      # Backup operations
│   ├── resources/                    # Static resources
│   │   ├── css/styles.css           # Extension styles
│   │   ├── js/main.js               # Extension JavaScript
│   │   └── locales/en-US.xml        # Localization
│   ├── scripts/                      # CLI scripts
│   │   └── backup.php               # Automated backup script
│   ├── hooks/                        # Plesk hooks
│   │   └── ConfigDefaults.php       # Default configuration
│   └── vendor/                       # Composer dependencies
├── screenshots/                      # Extension screenshots
└── icon.png                         # Extension icon
```

## Installation

**For detailed installation instructions, see [INSTALLATION.md](INSTALLATION.md)**

Quick installation:
1. Download the latest release
2. Upload to Plesk Extensions Manager
3. Configure Google Cloud OAuth credentials
4. Connect to Google Drive and configure backup settings

## Google Cloud Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the "Google Drive API"
4. Create OAuth 2.0 credentials:
   - Application type: "Web application"
   - Authorized redirect URI: `https://your-server.com/admin/extension/index/id/plesk-gdrive-autobackups/index/oauth2callback`

## Configuration

1. Enter your Google Cloud OAuth credentials
2. Connect to Google Drive
3. Select backup directories and frequency
4. Configure retention policy

## Technical Details

### Architecture

- **MVC Pattern**: Uses Plesk's standard MVC architecture
- **Proper Routing**: All requests go through IndexController actions
- **Security**: OAuth 2.0 integration with proper token management
- **Localization**: Full i18n support with XML locale files
- **Responsive UI**: Mobile-friendly interface using Plesk UI framework

### Key Components

1. **IndexController**: Handles all web requests and API calls
2. **ApiController**: Manages Google API authentication and token storage
3. **BackupController**: Handles backup operations and scheduling
4. **ConfigDefaults**: Provides default extension configuration

### URL Structure

- Main page: `/admin/extension/index/id/plesk-gdrive-autobackups`
- OAuth callback: `/admin/extension/index/id/plesk-gdrive-autobackups/index/oauth2callback`
- AJAX actions: `/admin/extension/index/id/plesk-gdrive-autobackups/index/{action}`

## Development

### Requirements

- PHP 7.3+
- Plesk 18.0+
- Google API Client Library
- cURL extension

### Local Development

1. Clone the repository
2. Run `composer install` to install dependencies
3. Ensure proper Plesk extension structure
4. Test with Plesk extension manager

## Troubleshooting

### Common Issues

1. **OAuth Redirect Mismatch**: Ensure redirect URI exactly matches Google Cloud Console
2. **Permission Errors**: Check Plesk extension permissions in meta.xml
3. **Backup Failures**: Verify Google Drive API quotas and permissions

### Debug Mode

Enable Plesk debug mode to see detailed error logs:
```bash
plesk bin extension --enable-debug plesk-gdrive-autobackups
```

## Support

For issues and feature requests, please use the GitHub repository issue tracker.

## License

MIT License - see LICENSE file for details.