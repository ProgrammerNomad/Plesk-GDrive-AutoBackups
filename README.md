# Plesk GDrive AutoBackups

**Automatically back up your Plesk server data to Google Drive.**

This Plesk extension simplifies server management by automating backups to your Google Drive. Secure your websites, databases, and email with ease.

## Features

*   Automated Backups: Schedule backups to run daily, weekly, or monthly.
*   Google Drive Integration: Securely connect your Plesk server to your Google Drive account.
*   Backup Selection: Choose specific directories and databases to back up.
*   Retention Policy: Configure how many backups to keep in Google Drive.
*   Easy Setup: Simple configuration through the Plesk interface.

## Installation

**Manually Upload the Plesk Extension**

1.  Download the latest Plesk extension from our website.
2.  **Enable Extension Uploads:**
    *   **Install Panel.ini Editor (if not already installed):**
        *   In Plesk, go to **Extensions**.
        *   Search for "Panel.ini Editor" and install the extension.
    *   **Configure `panel.ini`:**
        *   Go to **Extensions** > **My Extensions** > **Panel.ini Editor** > **Open** >> **Editor**.
        *   Paste this at the end of the file:
        
            ```ini
            [ext-catalog]
            extensionUpload = true
            ```
        
        *   Click **Save** to save the changes to `panel.ini`.
3.  In Plesk, navigate to **Extensions** > **My Extensions**.
4.  Click the **Upload Extension** button and upload the package downloaded in step 1.

## Google Cloud Project Setup

Before you can use this extension, you need to set up a Google Cloud project:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Navigate to "APIs & Services" > "Library"
4. Search for and enable the "Google Drive API"
5. Go to "APIs & Services" > "Credentials"
6. Click "Create Credentials" > "OAuth Client ID"
7. If prompted, configure the OAuth consent screen:
   * Set User Type to "External"
   * Add a name for your app
   * Enter your email for the support email
   * Enter your email as the developer contact information
   * Click "Save and Continue" through the scopes and test users screens
8. Return to the "Create credentials" flow if needed
9. Choose "Web application" as the application type
10. Set a name for your client (e.g., "Plesk GDrive AutoBackups")
11. Under "Authorized redirect URIs", add this exact URL:
    ```
    https://your-plesk-domain.com/modules/plesk-gdrive-autobackups/oauth2callback.php
    ```
    Replace `your-plesk-domain.com` with your actual Plesk server domain name.
12. Click "Create" to generate your Client ID and Client Secret
13. Copy the Client ID and Client Secret for use in the extension

## Configuration

1.  Once installed, go to the "Plesk GDrive AutoBackups" extension in your Plesk panel.
2.  Enter your Google API credentials (Client ID and Client Secret)
3.  Connect your Google Drive account by clicking "Connect to Google Drive" and following the prompts.
4.  Choose the directories and databases you want to back up.
5.  Set the backup frequency and retention policy.
6.  Save your settings.

## Potential Issues

### Redirect URI Mismatch

If you encounter an error like "redirect_uri_mismatch" when connecting to Google Drive, ensure:

1. The redirect URI in your Google Cloud Console exactly matches the one shown in the extension
2. Check for typos, including protocol (http vs https) and trailing slashes
3. If your Plesk server has multiple domains, use the exact domain you access Plesk with

### Invalid Credentials

If you see "invalid_client" errors:
1. Double-check that you've entered the Client ID and Client Secret correctly
2. Make sure you're using credentials from an OAuth 2.0 Client ID (not API keys)

## Support

If you encounter any issues or have questions, please open an issue on the GitHub repository: [https://github.com/ProgrammerNomad/Plesk-GDrive-AutoBackups/issues](https://github.com/ProgrammerNomad/Plesk-GDrive-AutoBackups/issues)

## Contributing

Contributions are welcome! Feel free to fork the repository and submit pull requests.

## License

This extension is licensed under the MIT License.