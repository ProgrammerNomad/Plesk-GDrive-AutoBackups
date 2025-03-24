// scripts.js

document.addEventListener('DOMContentLoaded', function() {
    const statusDiv = document.getElementById('status');
    const connectBtn = document.getElementById('connectBtn');
    const saveApiCredentialsBtn = document.getElementById('saveApiCredentials');
    const configForm = document.getElementById('configForm');
    const clientIdInput = document.getElementById('clientId');
    const clientSecretInput = document.getElementById('clientSecret');
    
    // Setup guide toggle functionality
    const toggleSetupGuideBtn = document.getElementById('toggleSetupGuide');
    const setupGuideContent = document.getElementById('setupGuideContent');
    
    if (toggleSetupGuideBtn) {
        toggleSetupGuideBtn.addEventListener('click', function() {
            if (setupGuideContent.classList.contains('hidden')) {
                setupGuideContent.classList.remove('hidden');
                toggleSetupGuideBtn.textContent = 'Hide';
            } else {
                setupGuideContent.classList.add('hidden');
                toggleSetupGuideBtn.textContent = 'Show';
            }
        });
    }
    
    // Check URL parameters for auth status
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('auth') === 'success') {
        statusDiv.textContent = "Successfully connected to Google Drive!";
        statusDiv.className = "status-success";
        loadBackupSettings();
        loadLogs();
    } else if (urlParams.get('auth') === 'error') {
        statusDiv.textContent = "Error connecting to Google Drive: " + urlParams.get('message');
        statusDiv.className = "status-error";
    }
    
    // Load existing API credentials if available
    loadApiCredentials();
    
    // Save API credentials
    saveApiCredentialsBtn.addEventListener('click', function() {
        const clientId = clientIdInput.value.trim();
        const clientSecret = clientSecretInput.value.trim();
        const redirectUri = document.getElementById('redirectUri').value.trim();
        
        if (!clientId || !clientSecret) {
            statusDiv.textContent = "Client ID and Client Secret are required";
            statusDiv.className = "status-error";
            return;
        }
        
        statusDiv.textContent = "Saving API credentials...";
        
        fetch(getBaseUrl() + 'api.php?action=saveCredentials', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                clientId: clientId,
                clientSecret: clientSecret,
                redirectUri: redirectUri
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                statusDiv.textContent = "API credentials saved successfully";
                statusDiv.className = "status-success";
                connectBtn.disabled = false;
            } else {
                statusDiv.textContent = "Error saving API credentials: " + (data.error || "Unknown error");
                statusDiv.className = "status-error";
            }
        })
        .catch(error => {
            statusDiv.textContent = "Error saving API credentials: " + error.message;
            statusDiv.className = "status-error";
        });
    });
    
    // Connect to Google Drive
    connectBtn.addEventListener('click', function() {
        statusDiv.textContent = "Connecting to Google Drive...";
        
        fetch(getBaseUrl() + 'api.php?action=getAuthUrl')
            .then(response => response.json())
            .then(data => {
                if (data.authUrl) {
                    // Open Google authorization page in a new window
                    window.open(data.authUrl, '_blank');
                    statusDiv.textContent = "A new window has opened. Please log in to your Google account and grant access.";
                } else {
                    statusDiv.textContent = "Error getting authorization URL: " + (data.error || "Unknown error");
                    statusDiv.className = "status-error";
                }
            })
            .catch(error => {
                statusDiv.textContent = "Error connecting to Google Drive: " + error.message;
                statusDiv.className = "status-error";
            });
    });
    
    // Save backup settings
    configForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const backupDirsSelect = document.getElementById('backupDirs');
        const backupFreqSelect = document.getElementById('backupFreq');
        const retentionCountInput = document.getElementById('retentionCount');
        
        // Get selected backup directories
        const backupDirs = Array.from(backupDirsSelect.selectedOptions).map(option => option.value);
        const backupFreq = backupFreqSelect.value;
        const retentionCount = retentionCountInput.value;
        
        if (backupDirs.length === 0) {
            statusDiv.textContent = "Please select at least one directory to backup";
            statusDiv.className = "status-error";
            return;
        }
        
        statusDiv.textContent = "Saving backup settings...";
        
        fetch(getBaseUrl() + 'api.php?action=saveSettings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                backupDirs: backupDirs,
                backupFreq: backupFreq,
                retentionCount: retentionCount
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.textContent = "Backup settings saved successfully";
                statusDiv.className = "status-success";
            } else {
                statusDiv.textContent = "Error saving backup settings: " + (data.error || "Unknown error");
                statusDiv.className = "status-error";
            }
        })
        .catch(error => {
            statusDiv.textContent = "Error saving backup settings: " + error.message;
            statusDiv.className = "status-error";
        });
    });
    
    // Load API credentials
    function loadApiCredentials() {
        fetch(getBaseUrl() + 'api.php?action=getCredentials')
            .then(response => response.json())
            .then(data => {
                if (data.credentials) {
                    if (data.credentials.clientId) {
                        clientIdInput.value = data.credentials.clientId;
                    }
                    if (data.credentials.clientSecret && data.credentials.clientSecret !== '••••••••') {
                        clientSecretInput.value = data.credentials.clientSecret;
                    } else if (data.credentials.clientSecret === '••••••••') {
                        clientSecretInput.placeholder = '••••••••';
                    }
                    
                    // Enable connect button if credentials are set
                    if (data.credentials.clientId && data.credentials.clientSecret) {
                        connectBtn.disabled = false;
                    }
                }
            })
            .catch(error => {
                console.error("Error loading API credentials:", error);
            });
    }
    
    // Load backup settings
    function loadBackupSettings() {
        fetch(getBaseUrl() + 'api.php?action=getSettings')
            .then(response => response.json())
            .then(data => {
                if (data.settings) {
                    const backupDirsSelect = document.getElementById('backupDirs');
                    const backupFreqSelect = document.getElementById('backupFreq');
                    const retentionCountInput = document.getElementById('retentionCount');
                    
                    // Set selected backup directories
                    if (data.settings.backupDirs && Array.isArray(data.settings.backupDirs)) {
                        for (let i = 0; i < backupDirsSelect.options.length; i++) {
                            backupDirsSelect.options[i].selected = 
                                data.settings.backupDirs.includes(backupDirsSelect.options[i].value);
                        }
                    }
                    
                    // Set backup frequency
                    if (data.settings.backupFreq) {
                        backupFreqSelect.value = data.settings.backupFreq;
                    }
                    
                    // Set retention count
                    if (data.settings.retentionCount) {
                        retentionCountInput.value = data.settings.retentionCount;
                    }
                }
            })
            .catch(error => {
                console.error("Error loading backup settings:", error);
            });
    }
    
    // Load backup logs
    function loadLogs() {
        const logsDiv = document.getElementById('logs');
        
        fetch(getBaseUrl() + 'api.php?action=getLogs')
            .then(response => response.json())
            .then(data => {
                if (data.logs && Array.isArray(data.logs)) {
                    if (data.logs.length === 0) {
                        logsDiv.innerHTML = '<p>No backup logs available yet.</p>';
                        return;
                    }
                    
                    let logsHtml = '<table class="logs-table">';
                    logsHtml += '<tr><th>Time</th><th>Level</th><th>Message</th></tr>';
                    
                    data.logs.forEach(log => {
                        const levelClass = log.level === 'ERROR' ? 'log-error' : 'log-info';
                        logsHtml += `<tr class="${levelClass}">`;
                        logsHtml += `<td>${log.timestamp}</td>`;
                        logsHtml += `<td>${log.level}</td>`;
                        logsHtml += `<td>${log.message}</td>`;
                        logsHtml += '</tr>';
                    });
                    
                    logsHtml += '</table>';
                    logsDiv.innerHTML = logsHtml;
                } else {
                    logsDiv.innerHTML = '<p>No backup logs available yet.</p>';
                }
            })
            .catch(error => {
                console.error("Error loading logs:", error);
                logsDiv.innerHTML = '<p>Error loading logs: ' + error.message + '</p>';
            });
    }
    
    // Add a Run Backup Now button
    const runBackupButton = document.createElement('button');
    runBackupButton.textContent = 'Run Backup Now';
    runBackupButton.type = 'button';
    runBackupButton.id = 'runBackupBtn';
    runBackupButton.className = 'action-button';
    configForm.appendChild(runBackupButton);
    
    runBackupButton.addEventListener('click', function() {
        statusDiv.textContent = "Starting backup...";
        statusDiv.className = "status-info";
        
        fetch(getBaseUrl() + 'api.php?action=runBackup', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.textContent = "Backup initiated successfully. Check logs for details.";
                statusDiv.className = "status-success";
                // Reload logs after a short delay to show new entries
                setTimeout(loadLogs, 2000);
            } else {
                statusDiv.textContent = "Error running backup: " + (data.error || "Unknown error");
                statusDiv.className = "status-error";
            }
        })
        .catch(error => {
            statusDiv.textContent = "Error running backup: " + error.message;
            statusDiv.className = "status-error";
        });
    });
    
    // Add some CSS styles for the logs table
    const style = document.createElement('style');
    style.textContent = `
        .status-success { color: green; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
        .status-info { color: blue; font-weight: bold; }
        
        .logs-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .logs-table th, .logs-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .logs-table th { background-color: #f2f2f2; }
        .log-error { background-color: #ffeeee; }
        .log-info { background-color: #f8f8f8; }
        
        .form-group { margin-bottom: 15px; }
        .form-control { width: 100%; padding: 8px; box-sizing: border-box; }
        .action-button { margin-left: 10px; }
    `;
    document.head.appendChild(style);
});

// Add this function to get the base URL of your extension
function getBaseUrl() {
    // Extract the base URL from the current path
    const path = window.location.pathname;
    const baseUrlMatch = path.match(/(.*\/)[^\/]+$/);
    return baseUrlMatch ? baseUrlMatch[1] : '/';
}