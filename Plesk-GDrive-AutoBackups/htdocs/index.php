<!DOCTYPE html>
<html>
<head>
  <title>Plesk GDrive AutoBackups</title>
  <link rel="stylesheet" href="css/styles.css"> </head>
<body>
  <h1>Plesk GDrive AutoBackups</h1>

  <div id="status"></div> 

  <form id="configForm">
    <h2>Google Drive Connection</h2>
    <button id="connectBtn">Connect to Google Drive</button>

    <h2>Backup Settings</h2>
    <label for="backupDirs">Directories to Backup:</label>
    <select id="backupDirs" multiple>
      <option value="/var/www/vhosts">All Domains</option>
      <option value="/var/lib/mysql">MySQL Databases</option>
      </select>

    <label for="backupFreq">Backup Frequency:</label>
    <select id="backupFreq">
      <option value="daily">Daily</option>
      <option value="weekly">Weekly</option>
      <option value="monthly">Monthly</option>
    </select>

    <button type="submit">Save Settings</button>
  </form>

  <h2>Backup Logs</h2>
  <div id="logs"></div>

  <script src="js/scripts.js"></script>
</body>
</html>