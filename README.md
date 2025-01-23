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

## Configuration

1.  Once installed, go to the "Plesk GDrive AutoBackups" extension in your Plesk panel.
2.  Connect your Google Drive account.
3.  Choose the directories and databases you want to back up.
4.  Set the backup frequency and retention policy.
5.  Save your settings.

## Support

If you encounter any issues or have questions, please open an issue on the GitHub repository: [https://github.com/ProgrammerNomad/Plesk-GDrive-AutoBackups](https://github.com/ProgrammerNomad/Plesk-GDrive-AutoBackups)

## Contributing

Contributions are welcome! Feel free to fork the repository and submit pull requests.

## License

This extension is licensed under the MIT License.