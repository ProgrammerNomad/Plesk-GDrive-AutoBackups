<?php
require_once '../plib/vendor/autoload.php';

use PleskExt\GDriveAutoBackups\ApiController;
use PleskExt\GDriveAutoBackups\BackupController;
use pm_Context;

header('Content-Type: application/json');

// Basic security check - require POST for mutation operations
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !in_array($_GET['action'], ['getCredentials', 'getSettings', 'getLogs'])) {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $apiController = new ApiController();
    $backupController = new BackupController();
    
    switch ($_GET['action']) {
        case 'saveCredentials':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $apiController->saveCredentials(
                $data['clientId'] ?? '',
                $data['clientSecret'] ?? '',
                $data['redirectUri'] ?? ''
            );
            echo json_encode($result);
            break;
            
        case 'getCredentials':
            $credentials = $apiController->getCredentials();
            // For security, don't return the actual client secret
            if (!empty($credentials['clientSecret'])) {
                $credentials['clientSecret'] = '••••••••';
            }
            echo json_encode(['credentials' => $credentials]);
            break;
            
        case 'getAuthUrl':
            $client = $apiController->createGoogleClient();
            $authUrl = $client->createAuthUrl();
            echo json_encode(['authUrl' => $authUrl]);
            break;
            
        case 'saveSettings':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $backupController->saveSettings(
                $data['backupDirs'] ?? [],
                $data['backupFreq'] ?? 'daily',
                $data['retentionCount'] ?? 5
            );
            echo json_encode($result);
            break;
            
        case 'getSettings':
            $settings = $backupController->getSettings();
            echo json_encode(['settings' => $settings]);
            break;
            
        case 'runBackup':
            $result = $backupController->runBackup();
            echo json_encode($result);
            break;
            
        case 'getLogs':
            $logs = $backupController->getLogs();
            echo json_encode(['logs' => $logs]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}