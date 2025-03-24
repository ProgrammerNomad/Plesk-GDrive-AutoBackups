<?php
namespace PleskExt\GDriveAutoBackups;

class ApiController
{
    private $pm; // Plesk PM_Settings instance
    
    public function __construct()
    {
        $this->pm = new \pm_Settings();
    }
    
    /**
     * Save Google API credentials
     */
    public function saveCredentials($clientId, $clientSecret, $redirectUri)
    {
        // Validate inputs
        if (empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
            throw new \Exception('All API credential fields are required');
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
     * Create Google API client instance
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
        $client->setRedirectUri($credentials['redirectUri']);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setScopes([\Google\Service\Drive::DRIVE]);
        
        // Load previously authorized token, if it exists
        $token = $this->getToken();
        if (!empty($token)) {
            $client->setAccessToken($token);
            
            // Refresh token if needed
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $this->saveToken($client->getAccessToken());
            }
        }
        
        return $client;
    }
}