<?php
namespace PleskExt\GDriveAutoBackups;

// Ensure we're using the proper Plesk namespace
use pm_Settings;
use pm_Context;

class ApiController
{
    private $pm; // Plesk PM_Settings instance
    
    public function __construct()
    {
        // Correct way to instantiate PM_Settings
        $this->pm = new pm_Settings();
    }
    
    /**
     * Save Google API credentials
     */
    public function saveCredentials($clientId, $clientSecret, $redirectUri)
    {
        // Validate inputs
        if (empty($clientId) || !is_string($clientId)) {
            throw new \Exception('Invalid Client ID');
        }
        if (empty($clientSecret) || !is_string($clientSecret)) {
            throw new \Exception('Invalid Client Secret');
        }
        if (empty($redirectUri) || !filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid Redirect URI');
        }
        
        // Store credentials securely
        $this->pm->set('google_client_id', $clientId);
        $this->pm->set('google_client_secret', $clientSecret);
        $this->pm->set('google_redirect_uri', $redirectUri);
        
        return ['success' => true];
    }
    
    /**
     * Get stored credentials
     */
    public function getCredentials()
    {
        return [
            'clientId' => $this->pm->get('google_client_id', ''),
            'clientSecret' => $this->pm->get('google_client_secret', ''),
            'redirectUri' => $this->pm->get('google_redirect_uri', '')
        ];
    }
    
    /**
     * Save access token received after OAuth
     */
    public function saveToken($token)
    {
        $this->pm->set('google_access_token', json_encode($token));
        return ['success' => true];
    }
    
    /**
     * Get stored access token
     */
    public function getToken()
    {
        $token = $this->pm->get('google_access_token', '');
        return empty($token) ? null : json_decode($token, true);
    }
    
    /**
     * Create Google Client instance
     */
    public function createGoogleClient()
    {
        $credentials = $this->getCredentials();
        
        if (empty($credentials['clientId']) || empty($credentials['clientSecret'])) {
            throw new \Exception('Google API credentials not configured');
        }
        
        $client = new \Google\Client();
        $client->setClientId($credentials['clientId']);
        $client->setClientSecret($credentials['clientSecret']);
        
        // Use Plesk's context to build the redirect URI properly
        $client->setRedirectUri(pm_Context::getBaseUrl() . 'oauth2callback.php');
        
        $client->addScope(\Google\Service\Drive::DRIVE);
        $client->setAccessType('offline');
        $client->setPrompt('consent'); // Force to refresh token
        
        // If we have a saved token, set it
        $token = $this->getToken();
        if (!empty($token)) {
            $client->setAccessToken($token);
            
            // If token is expired, try to refresh it
            if ($client->isAccessTokenExpired()) {
                try {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    $this->saveToken($client->getAccessToken());
                } catch (\Exception $e) {
                    // Failed to refresh token, user needs to reconnect
                    $this->clearToken();
                }
            }
        }
        
        return $client;
    }
}