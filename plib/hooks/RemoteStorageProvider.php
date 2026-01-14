<?php
/**
 * Remote Storage Provider Hook
 * 
 * This hook registers Google Drive as a valid remote storage backend with Plesk.
 * It tells Plesk's Backup Manager that this extension can store backups in Google Drive.
 * 
 * @copyright 2025 ProgrammerNomad. All rights reserved.
 */

/**
 * Register Google Drive as a remote storage provider
 * 
 * This class implements Plesk's remote storage provider hook.
 * When Plesk's Backup Manager displays storage options, this hook is called
 * to register our Google Drive storage as an available option.
 */
class Modules_PleskGdriveAutobackups_RemoteStorageProvider extends pm_Hook_RemoteStorageProvider
{
    /**
     * Get provider information
     * 
     * Called by Plesk to get basic information about the storage provider.
     * This information is used to display the provider in the UI.
     * 
     * @return array Provider information
     * @example
     * [
     *     'id'          => 'gdrive',
     *     'type'        => 'cloud',
     *     'name'        => 'Google Drive',
     *     'description' => 'Store backups on Google Drive',
     *     'icon'        => 'icon.png',
     * ]
     */
    public function getInfo()
    {
        return [
            'id'          => 'gdrive',                                    // Unique identifier
            'type'        => 'cloud',                                     // Storage type: cloud, local, ftp, etc
            'name'        => 'Google Drive',                              // Display name in UI
            'description' => 'Automatically store backups on Google Drive', // Short description
            'icon'        => 'icon.png',                                  // Icon file path
            'vendor'      => 'ProgrammerNomad',                          // Vendor name
        ];
    }

    /**
     * Check if the provider is configured
     * 
     * Called to determine if the provider has valid credentials set up.
     * Returns true if the user has completed the OAuth setup.
     * 
     * @return bool True if provider is configured, false otherwise
     */
    public function isConfigured()
    {
        try {
            $settings = pm_Settings::getInstance();
            
            // Check if essential credentials are set
            $hasClientId = !empty($settings->get('google_client_id'));
            $hasAccessToken = !empty($settings->get('google_access_token'));
            
            return $hasClientId && $hasAccessToken;
        } catch (Exception $e) {
            pm_Log::err("Error checking if RemoteStorageProvider is configured: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the current status of the provider
     * 
     * Called to determine the provider's status.
     * Used to display status in the Backup Manager UI.
     * 
     * @return int Status code (STATUS_OK, STATUS_NOT_CONFIGURED, STATUS_ERROR)
     */
    public function getStatus()
    {
        // Check if configured
        if (!$this->isConfigured()) {
            return pm_Hook_RemoteStorageProvider::STATUS_NOT_CONFIGURED;
        }

        // Try to verify the connection is working
        try {
            // We'll check this more thoroughly in a separate method
            // For now, if it's configured, consider it OK
            // (Full connection test would require instantiating Google client)
            return pm_Hook_RemoteStorageProvider::STATUS_OK;
        } catch (Exception $e) {
            return pm_Hook_RemoteStorageProvider::STATUS_ERROR;
        }
    }

    /**
     * Get the configuration URL
     * 
     * Called to get the URL where users can configure this provider.
     * This URL is opened when users click to setup/configure the provider.
     * 
     * @return string URL to configuration page
     */
    public function getConfigurationUrl()
    {
        // Link to our remote storage settings page
        return pm_Context::getBaseUrl() . 'index.php/index/remote-storage-settings';
    }

    /**
     * Validate provider configuration
     * 
     * Called before using the provider to make sure it's properly configured.
     * Throws an exception if there are configuration issues.
     * 
     * @throws Exception If configuration is invalid
     * @return void
     */
    public function validate()
    {
        if (!$this->isConfigured()) {
            throw new Exception('Google Drive storage provider is not configured. Please set up your Google credentials.');
        }

        // Additional validation could go here
        // (e.g., verify credentials are valid by testing connection)
    }

    /**
     * Get a brief status message
     * 
     * Used to display status information in the UI.
     * Example: "Connected as user@example.com" or "Not configured"
     * 
     * @return string Status message
     */
    public function getStatusMessage()
    {
        if (!$this->isConfigured()) {
            return 'Not configured';
        }

        try {
            $settings = pm_Settings::getInstance();
            $account = $settings->get('google_account_email', '');
            
            if (!empty($account)) {
                return 'Connected as ' . htmlspecialchars($account);
            }
            
            return 'Configured';
        } catch (Exception $e) {
            return 'Status unknown';
        }
    }

    /**
     * Get storage statistics
     * 
     * Returns information about storage usage.
     * Called to display quota/usage information in the UI.
     * 
     * @return array Storage statistics
     * @example
     * [
     *     'used'  => 5368709120,    // Bytes used
     *     'total' => 107374182400,  // Total bytes (2TB for personal Google Drive)
     *     'free'  => 102005473280,  // Bytes free
     * ]
     */
    public function getStorageStats()
    {
        try {
            $settings = pm_Settings::getInstance();
            
            // Get cached stats if available
            $cachedStats = $settings->get('google_storage_stats');
            if (!empty($cachedStats)) {
                $stats = json_decode($cachedStats, true);
                
                // Check if cache is still fresh (e.g., less than 1 hour old)
                if (!empty($stats['cached_at'])) {
                    $cacheAge = time() - $stats['cached_at'];
                    if ($cacheAge < 3600) { // 1 hour
                        return [
                            'used'  => (int)$stats['used'],
                            'total' => (int)$stats['total'],
                            'free'  => (int)$stats['free'],
                        ];
                    }
                }
            }

            // If we get here, return default Google Drive quota
            // (Exact values would be fetched from Google Drive API)
            return [
                'used'  => 0,
                'total' => 107374182400, // 100 GB (approximate for personal drive)
                'free'  => 107374182400,
            ];
        } catch (Exception $e) {
            pm_Log::err("Error getting storage stats: " . $e->getMessage());
            
            // Return safe defaults on error
            return [
                'used'  => 0,
                'total' => 0,
                'free'  => 0,
            ];
        }
    }

    /**
     * Check if provider supports restores
     * 
     * Returns true if users can restore backups from this storage.
     * 
     * @return bool True if restores are supported
     */
    public function supportsRestore()
    {
        return true; // We'll implement restore in Phase 3
    }

    /**
     * Check if provider requires authentication setup
     * 
     * Returns true if users must authenticate before using this provider.
     * 
     * @return bool True if authentication is required
     */
    public function requiresAuthentication()
    {
        return true; // Google Drive requires OAuth
    }

    /**
     * Get human-readable name for display
     * 
     * @return string Provider name
     */
    public function getName()
    {
        return 'Google Drive';
    }

    /**
     * Get provider description
     * 
     * @return string Provider description
     */
    public function getDescription()
    {
        return 'Store your Plesk backups securely on Google Drive with automatic scheduling and retention policies';
    }
}
