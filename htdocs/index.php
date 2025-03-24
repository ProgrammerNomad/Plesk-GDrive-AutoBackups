<!DOCTYPE html>
<html>
<head>
  <title>Plesk GDrive AutoBackups</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <h1>Plesk GDrive AutoBackups</h1>

  <div id="status"></div>

  <div class="setup-guide">
    <h2>Google Cloud Setup Guide <button id="toggleSetupGuide" class="toggle-button">Show</button></h2>
    
    <div id="setupGuideContent" class="setup-guide-content hidden">
      <ol>
        <li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
        <li>Create a new project or select an existing one</li>
        <li>Navigate to "APIs & Services" > "Library"</li>
        <li>Enable the "Google Drive API"</li>
        <li>Go to "APIs & Services" > "Credentials"</li>
        <li>Click "Create Credentials" > "OAuth Client ID"</li>
        <li>Choose "Web application" as the application type</li>
        <li>Set a name for your client (e.g., "Plesk GDrive AutoBackups")</li>
        <li>Under "Authorized redirect URIs", add this exact URL:
          <div class="code-box">
            <?php 
              $redirectUri = 'https://' . $_SERVER['HTTP_HOST'] . '/modules/plesk-gdrive-autobackups/oauth2callback.php';
              echo $redirectUri; 
            ?>
          </div>
        </li>
        <li>Click "Create" to generate your Client ID and Client Secret</li>
        <li>Copy the Client ID and Client Secret into the form below</li>
      </ol>
      <div class="note">
        <strong>Note:</strong> Make sure the redirect URI exactly matches what you enter in Google Cloud Console, 
        including the protocol (http/https) and any trailing slashes.
      </div>
    </div>
  </div>

  <form id="configForm">
    <h2>Google Drive API Configuration</h2>
    <div class="form-group">
      <label for="clientId">Client ID:</label>
      <input type="text" id="clientId" name="clientId" class="form-control" required>
    </div>
    
    <div class="form-group">
      <label for="clientSecret">Client Secret:</label>
      <input type="password" id="clientSecret" name="clientSecret" class="form-control" required>
    </div>
    
    <div class="form-group">
      <label for="redirectUri">Redirect URI:</label>
      <input type="text" id="redirectUri" name="redirectUri" class="form-control" 
             value="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . '/modules/plesk-gdrive-autobackups/oauth2callback.php'; ?>" readonly>
      <small class="form-text text-muted">Add this URL to your Google Developer Console as an authorized redirect URI</small>
    </div>

    <button type="button" id="saveApiCredentials">Save Credentials</button>
    <button type="button" id="connectBtn" disabled>Connect to Google Drive</button>

    <h2>Backup Settings</h2>
    <div class="form-group">
      <label for="backupDirs">Directories to Backup:</label>
      <select id="backupDirs" name="backupDirs[]" multiple class="form-control">
        <option value="/var/www/vhosts">All Domains</option>
        <option value="/var/lib/mysql">MySQL Databases</option>
      </select>
    </div>

    <div class="form-group">
      <label for="backupFreq">Backup Frequency:</label>
      <select id="backupFreq" name="backupFreq" class="form-control">
        <option value="daily">Daily</option>
        <option value="weekly">Weekly</option>
        <option value="monthly">Monthly</option>
      </select>
    </div>
    
    <div class="form-group">
      <label for="retentionCount">Number of Backups to Keep:</label>
      <input type="number" id="retentionCount" name="retentionCount" min="1" max="100" value="5" class="form-control">
    </div>

    <button type="submit" id="saveSettings">Save Settings</button>
  </form>

  <h2>Backup Logs</h2>
  <div id="logs"></div>

  <script src="js/scripts.js"></script>
</body>
</html>