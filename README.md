## Plesk GDrive AutoBackups

**Automatically back up your Plesk server data to Google Drive.**

This Plesk extension simplifies server management by automating backups to your Google Drive. Secure your websites, databases, and email with ease.

**Features**

*   **Automated Backups:** Schedule backups to run daily, weekly, or monthly.
*   **Google Drive Integration:** Securely connect your Plesk server to your Google Drive account.
*   **Backup Selection:** Choose specific directories and databases to back up.
*   **Retention Policy:** Configure how many backups to keep in Google Drive.
*   **Easy Setup:** Simple configuration through the Plesk interface.

**Installation**

**Manually Upload the Plesk Extension**

1.  Download the latest Plesk extension from our website.
2.  Open `/usr/local/psa/admin/conf/panel.ini` for editing. If it doesn't exist, create it.
3.  Add the following content, and save the file:

```ini
[ext-catalog]
extensionUpload = true
```

4.  In Plesk, navigate to **Extensions** > **My Extensions**.
5.  Click the **Upload Extension** button, and upload the package that was downloaded in step 1.

**Configuration**

1.  Once installed, go to the "Plesk GDrive AutoBackups" extension in your Plesk panel.
2.  Connect your Google Drive account.
3.  Choose the directories and databases you want to back up.
4.  Set the backup frequency and retention policy.
5.  Save your settings.

**Support**

If you encounter any issues or have questions, please open an issue on the GitHub repository: [https://github.com/ProgrammerNomad/Plesk-GDrive-AutoBackups](https://github.com/ProgrammerNomad/Plesk-GDrive-AutoBackups)

**Contributing**

Contributions are welcome! Feel free to fork the repository and submit pull requests.

**License**

This extension is licensed under the MIT License.