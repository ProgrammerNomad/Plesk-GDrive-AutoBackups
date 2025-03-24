<?php
require_once '../plib/vendor/autoload.php';

use PleskExt\GDriveAutoBackups\ApiController;

try {
    $apiController = new ApiController();
    $client = $apiController->createGoogleClient();
    
    // Handle the callback from Google
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);
        
        // Store the token for future use
        $apiController->saveToken($token);
        
        // Redirect back to the extension page using Plesk's base URL
        header('Location: ' . pm_Context::getBaseUrl() . 'index.php?auth=success');
        exit;
    } else {
        // No code provided, redirect to the extension page with an error
        header('Location: ' . pm_Context::getBaseUrl() . 'index.php?auth=error&message=' . urlencode('No authorization code was provided'));
        exit;
    }
} catch (Exception $e) {
    // Handle any exceptions that occur during the OAuth flow
    header('Location: ' . pm_Context::getBaseUrl() . 'index.php?auth=error&message=' . urlencode($e->getMessage()));
    exit;
}