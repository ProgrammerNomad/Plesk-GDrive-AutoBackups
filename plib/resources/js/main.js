/**
 * GDrive AutoBackups Extension JavaScript
 * Integrates with Plesk's UI framework
 */

(function() {
    'use strict';
    
    // Extension namespace
    const GDriveAutoBackups = {
        
        // Initialize extension
        init: function() {
            this.bindEvents();
            this.loadInitialData();
        },
        
        // Bind event handlers
        bindEvents: function() {
            const self = this;
            
            // Setup guide toggle
            const toggleBtn = document.getElementById('toggleSetupGuide');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', this.toggleSetupGuide.bind(this));
            }
            
            // Save credentials button
            const saveCredentialsBtn = document.getElementById('saveApiCredentials');
            if (saveCredentialsBtn) {
                saveCredentialsBtn.addEventListener('click', this.saveCredentials.bind(this));
            }
            
            // Connect to Google Drive button
            const connectBtn = document.getElementById('connectBtn');
            if (connectBtn) {
                connectBtn.addEventListener('click', this.connectToGoogleDrive.bind(this));
            }
            
            // Save settings form
            const configForm = document.getElementById('configForm');
            if (configForm) {
                configForm.addEventListener('submit', this.saveSettings.bind(this));
            }
            
            // Run backup button
            const runBackupBtn = document.getElementById('runBackup');
            if (runBackupBtn) {
                runBackupBtn.addEventListener('click', this.runBackup.bind(this));
            }
            
            // Enable connect button if credentials are present
            this.checkCredentialsStatus();
        },
        
        // Load initial data
        loadInitialData: function() {
            this.loadCredentials();
            this.loadSettings();
            this.loadLogs();
        },
        
        // Toggle setup guide visibility
        toggleSetupGuide: function() {
            const content = document.getElementById('setupGuideContent');
            const button = document.getElementById('toggleSetupGuide');
            
            if (content && button) {
                if (content.classList.contains('hidden')) {
                    content.classList.remove('hidden');
                    button.textContent = 'Hide';
                } else {
                    content.classList.add('hidden');
                    button.textContent = 'Show';
                }
            }
        },
        
        // Save API credentials
        saveCredentials: function() {
            const clientId = document.getElementById('clientId').value.trim();
            const clientSecret = document.getElementById('clientSecret').value.trim();
            const redirectUri = document.getElementById('redirectUri').value.trim();
            
            if (!clientId || !clientSecret) {
                this.showStatus('Client ID and Client Secret are required', 'error');
                return;
            }
            
            this.showStatus('Saving API credentials...', 'info');
            
            // Use Plesk's AJAX pattern
            this.makeRequest('save-credentials', {
                clientId: clientId,
                clientSecret: clientSecret,
                redirectUri: redirectUri
            })
            .then(data => {
                if (data.success) {
                    this.showStatus('API credentials saved successfully', 'success');
                    this.enableConnectButton();
                } else {
                    this.showStatus('Error saving API credentials: ' + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                this.showStatus('Error saving API credentials: ' + error.message, 'error');
            });
        },
        
        // Connect to Google Drive
        connectToGoogleDrive: function() {
            this.showStatus('Connecting to Google Drive...', 'info');
            
            this.makeRequest('get-auth-url', {})
            .then(data => {
                if (data.success && data.authUrl) {
                    window.open(data.authUrl, '_blank');
                    this.showStatus('A new window has opened. Please log in to your Google account and grant access.', 'info');
                } else {
                    this.showStatus('Error getting authorization URL: ' + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                this.showStatus('Error getting authorization URL: ' + error.message, 'error');
            });
        },
        
        // Save backup settings
        saveSettings: function(event) {
            event.preventDefault();
            
            const backupDirs = Array.from(document.getElementById('backupDirs').selectedOptions)
                                   .map(option => option.value);
            const backupFreq = document.getElementById('backupFreq').value;
            const retentionCount = document.getElementById('retentionCount').value;
            
            if (backupDirs.length === 0) {
                this.showStatus('Please select at least one directory to backup', 'error');
                return;
            }
            
            this.showStatus('Saving backup settings...', 'info');
            
            this.makeRequest('save-settings', {
                backupDirs: backupDirs,
                backupFreq: backupFreq,
                retentionCount: retentionCount
            })
            .then(data => {
                if (data.success) {
                    this.showStatus('Backup settings saved successfully', 'success');
                } else {
                    this.showStatus('Error saving backup settings: ' + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                this.showStatus('Error saving backup settings: ' + error.message, 'error');
            });
        },
        
        // Run backup now
        runBackup: function() {
            this.showStatus('Starting backup...', 'info');
            
            this.makeRequest('run-backup', {})
            .then(data => {
                if (data.success) {
                    this.showStatus('Backup completed successfully', 'success');
                    this.loadLogs(); // Refresh logs
                } else {
                    this.showStatus('Backup failed: ' + (data.error || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                this.showStatus('Backup failed: ' + error.message, 'error');
            });
        },
        
        // Load credentials (without showing client secret)
        loadCredentials: function() {
            this.makeRequest('get-credentials', {}, 'GET')
            .then(data => {
                if (data.credentials) {
                    const clientIdInput = document.getElementById('clientId');
                    const clientSecretInput = document.getElementById('clientSecret');
                    
                    if (clientIdInput && data.credentials.clientId) {
                        clientIdInput.value = data.credentials.clientId;
                    }
                    
                    if (clientSecretInput && data.credentials.clientSecret && data.credentials.clientSecret !== '••••••••') {
                        // Don't show the actual secret for security
                        clientSecretInput.placeholder = '••••••••';
                    }
                    
                    // Enable connect button if credentials are set
                    if (data.credentials.clientId && data.credentials.clientSecret) {
                        this.enableConnectButton();
                    }
                }
            })
            .catch(error => {
                console.error('Error loading credentials:', error);
            });
        },
        
        // Load backup settings
        loadSettings: function() {
            this.makeRequest('get-settings', {}, 'GET')
            .then(data => {
                if (data.settings) {
                    // Load backup directories
                    const backupDirsSelect = document.getElementById('backupDirs');
                    if (backupDirsSelect && data.settings.backupDirs) {
                        for (let option of backupDirsSelect.options) {
                            option.selected = data.settings.backupDirs.includes(option.value);
                        }
                    }
                    
                    // Load backup frequency
                    const backupFreqSelect = document.getElementById('backupFreq');
                    if (backupFreqSelect && data.settings.backupFreq) {
                        backupFreqSelect.value = data.settings.backupFreq;
                    }
                    
                    // Load retention count
                    const retentionCountInput = document.getElementById('retentionCount');
                    if (retentionCountInput && data.settings.retentionCount) {
                        retentionCountInput.value = data.settings.retentionCount;
                    }
                }
            })
            .catch(error => {
                console.error('Error loading settings:', error);
            });
        },
        
        // Load backup logs
        loadLogs: function() {
            this.makeRequest('get-logs', {}, 'GET')
            .then(data => {
                const logsContainer = document.getElementById('logs');
                if (logsContainer && data.logs) {
                    if (data.logs.length > 0) {
                        logsContainer.innerHTML = '<div class="logs-container">' +
                            data.logs.map(log => '<div class="log-entry">' + this.escapeHtml(log) + '</div>').join('') +
                            '</div>';
                    } else {
                        logsContainer.innerHTML = '<div class="no-logs">No logs available</div>';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading logs:', error);
            });
        },
        
        // Check if credentials are present to enable connect button
        checkCredentialsStatus: function() {
            const clientId = document.getElementById('clientId');
            const clientSecret = document.getElementById('clientSecret');
            
            if (clientId && clientSecret) {
                clientId.addEventListener('input', this.updateConnectButtonState.bind(this));
                clientSecret.addEventListener('input', this.updateConnectButtonState.bind(this));
            }
        },
        
        // Update connect button state
        updateConnectButtonState: function() {
            const clientId = document.getElementById('clientId').value.trim();
            const clientSecret = document.getElementById('clientSecret').value.trim();
            const connectBtn = document.getElementById('connectBtn');
            
            if (connectBtn) {
                connectBtn.disabled = !clientId || !clientSecret;
            }
        },
        
        // Enable connect button
        enableConnectButton: function() {
            const connectBtn = document.getElementById('connectBtn');
            if (connectBtn) {
                connectBtn.disabled = false;
            }
        },
        
        // Show status message
        showStatus: function(message, type) {
            const statusDiv = document.getElementById('status');
            if (statusDiv) {
                statusDiv.innerHTML = '<div class="msg-' + type + '">' + this.escapeHtml(message) + '</div>';
            }
        },
        
        // Make AJAX request to Plesk extension
        makeRequest: function(action, data, method = 'POST') {
            const url = window.location.pathname;
            
            const options = {
                method: method,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };
            
            if (method === 'POST') {
                const params = new URLSearchParams();
                params.append('action', action);
                for (const key in data) {
                    if (Array.isArray(data[key])) {
                        data[key].forEach(value => params.append(key + '[]', value));
                    } else {
                        params.append(key, data[key]);
                    }
                }
                options.body = params;
            } else {
                // For GET requests, append action to URL
                const separator = url.includes('?') ? '&' : '?';
                url += separator + 'action=' + encodeURIComponent(action);
            }
            
            return fetch(url, options)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                });
        },
        
        // Escape HTML to prevent XSS
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            GDriveAutoBackups.init();
        });
    } else {
        GDriveAutoBackups.init();
    }
    
    // Export to global scope for debugging
    window.GDriveAutoBackups = GDriveAutoBackups;
    
})();
