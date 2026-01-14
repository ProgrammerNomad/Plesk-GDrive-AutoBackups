# Installation Guide - Plesk GDrive AutoBackups Extension

This guide provides detailed instructions for installing and configuring the Plesk GDrive AutoBackups extension.

## Prerequisites

- Plesk Panel 18.0 or higher
- PHP 7.3 or higher
- Google Cloud Platform account
- Domain with SSL certificate (HTTPS required for OAuth)

## Google Cloud Project Setup

Before installing the extension, you need to set up a Google Cloud project:

### 1. Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Navigate to "APIs & Services" > "Library"
4. Search for and enable the "Google Drive API"

### 2. Configure OAuth Consent Screen

1. Go to "APIs & Services" > "OAuth consent screen"
2. Choose "External" user type
3. Fill in the required information:
   - App name: "Plesk GDrive AutoBackups"
   - User support email: Your email address
   - Developer contact information: Your email address
4. Click "Save and Continue" through the scopes and test users screens

### 3. Create OAuth Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth Client ID"
3. Choose "Web application" as the application type
4. Set a name for your client (e.g., "Plesk GDrive AutoBackups")
5. Under "Authorized redirect URIs", add this exact URL:
   ```
   https://your-plesk-domain.com/admin/extension/index/id/plesk-gdrive-autobackups/index/oauth2callback
   ```
   **Important**: Replace `your-plesk-domain.com` with your actual Plesk server domain name.
6. Click "Create" to generate your Client ID and Client Secret
7. Copy the Client ID and Client Secret for use in the extension

## Extension Installation

### Method 1: Manually Upload the Plesk Extension

1. **Download the Extension Package**:
   - Download the latest Plesk extension from our website or build it using the deployment scripts
   
2. **Enable Extension Uploads**:
   
   **Install Panel.ini Editor** (if not already installed):
   - In Plesk, go to **Extensions**
   - Search for "Panel.ini Editor" and install the extension
   
   **Configure panel.ini**:
   - Go to **Extensions** > **My Extensions** > **Panel.ini Editor** > **Open** > **Editor**
   - Paste this at the end of the file:
     ```ini
     [ext-catalog]
     extensionUpload = true
     ```
   - Click **Save** to save the changes to panel.ini

3. **Upload the Extension**:
   - In Plesk, navigate to **Extensions** > **My Extensions**
   - Click the **Upload Extension** button
   - Upload the package downloaded in step 1

### Method 2: Install via Plesk Extension Manager (Alternative)

1. **Package the Extension**:
   ```bash
   cd /path/to/Plesk-GDrive-AutoBackups
   zip -r plesk-gdrive-autobackups.zip . -x "*.git*" "google-drive-backup/*" "node_modules/*"
   ```

2. **Upload to Plesk**:
   - Log in to your Plesk Panel
   - Go to **Extensions** > **My Extensions**
   - Click **"Upload Extension"**
   - Select the `plesk-gdrive-autobackups.zip` file
   - Click **"Upload"** and wait for installation to complete

### Method 3: Manual Installation

1. **Upload Files**:
   ```bash
   # On your Plesk server
   cd /usr/local/psa/admin/plib/modules/
   sudo mkdir plesk-gdrive-autobackups
   sudo chown psaadm:psaadm plesk-gdrive-autobackups
   ```

2. **Copy Extension Files**:
   ```bash
   # Upload and extract extension files
   scp plesk-gdrive-autobackups.zip root@your-server:/tmp/
   ssh root@your-server
   cd /tmp
   unzip plesk-gdrive-autobackups.zip -d /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/
   chown -R psaadm:psaadm /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/
   ```

3. **Install Dependencies**:
   ```bash
   cd /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/
   composer install --no-dev --optimize-autoloader
   ```

4. **Register Extension**:
   ```bash
   plesk bin extension --register /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/
   ```

## Post-Installation Configuration

### 1. Access the Extension

1. Log in to your Plesk Panel
2. Go to **Extensions** > **My Extensions**
3. Click on **"GDrive AutoBackups"**

### 2. Configure Google API Credentials

1. In the extension interface, expand the **"Google Cloud Setup Guide"** section
2. Copy the displayed redirect URI
3. Go back to your Google Cloud Console and verify the redirect URI matches exactly
4. Enter your **Client ID** and **Client Secret** in the extension
5. Click **"Save Credentials"**

### 3. Connect to Google Drive

1. Click **"Connect to Google Drive"** button
2. A new window will open for Google authentication
3. Log in to your Google account
4. Grant the requested permissions
5. You'll be redirected back to the extension with a success message

### 4. Configure Backup Settings

1. Select directories to backup:
   - **All Domains**: `/var/www/vhosts` (includes all website files)
   - **MySQL Databases**: `/var/lib/mysql` (includes all databases)
2. Choose backup frequency:
   - **Daily**: Runs every day at 2:00 AM
   - **Weekly**: Runs every Sunday at 2:00 AM
   - **Monthly**: Runs on the 1st of each month at 2:00 AM
3. Set retention count (number of backups to keep in Google Drive)
4. Click **"Save Settings"**

## Verification

### Test Manual Backup

1. Click **"Run Backup Now"** to test the setup
2. Monitor the backup logs for any errors
3. Check your Google Drive for the "Plesk Backups" folder
4. Verify that backup files are created successfully

### Check Scheduled Backups

1. Verify cron job is created:
   ```bash
   crontab -u psaadm -l | grep backup.php
   ```
2. Check Plesk logs for backup activities:
   ```bash
   tail -f /var/log/plesk/panel.log | grep "GDrive"
   ```

## Troubleshooting

### Common Installation Issues

**1. Extension Not Showing in Plesk**
- Check file permissions: `chown -R psaadm:psaadm /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/`
- Verify meta.xml is valid: `xmllint --noout meta.xml`
- Restart Plesk services: `service psa restart`

**2. Composer Dependencies Missing**
```bash
cd /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/
composer install --no-dev
```

**3. OAuth Redirect URI Mismatch**
- Ensure the redirect URI in Google Cloud Console exactly matches the one shown in the extension
- Check for typos in domain name, protocol (https), and path
- Make sure your Plesk server is accessible via HTTPS

**4. Permission Errors**
```bash
# Fix file permissions
chmod 755 /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/
chmod -R 644 /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/*
chmod 755 /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/plib/scripts/backup.php
```

### Debug Mode

Enable debug mode for detailed error logging:
```bash
plesk bin extension --enable-debug plesk-gdrive-autobackups
tail -f /var/log/plesk/panel.log | grep "plesk-gdrive-autobackups"
```

## Uninstallation

### Via Plesk Panel
1. Go to **Extensions** > **My Extensions**
2. Find "GDrive AutoBackups"
3. Click **"Remove"**

### Manual Uninstallation
```bash
plesk bin extension --unregister plesk-gdrive-autobackups
rm -rf /usr/local/psa/admin/plib/modules/plesk-gdrive-autobackups/
```

## Security Considerations

1. **HTTPS Required**: OAuth 2.0 requires HTTPS for redirect URIs
2. **Token Storage**: Access tokens are stored securely in Plesk's settings database
3. **File Permissions**: Ensure proper file permissions to prevent unauthorized access
4. **API Quotas**: Monitor Google Drive API usage to avoid quota limits

## Support

If you encounter issues during installation:

1. Check the troubleshooting section above
2. Review Plesk panel logs: `/var/log/plesk/panel.log`
3. Verify Google Cloud Console configuration
4. Open an issue on the GitHub repository with detailed error messages

## Next Steps

After successful installation:
1. Configure backup schedules according to your needs
2. Monitor backup logs regularly
3. Test restore procedures periodically
4. Set up monitoring alerts for backup failures
