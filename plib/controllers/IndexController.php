<?php
// Copyright 2025. ProgrammerNomad. All rights reserved.

class IndexController extends pm_Controller_Action
{
    public function init()
    {
        parent::init();
        
        // Set page title
        $this->view->pageTitle = $this->lmsg('page_title');
        
        // Add CSS and JS resources using proper Plesk paths
        $baseUrl = pm_Context::getBaseUrl();
        $this->view->headLink()->appendStylesheet($baseUrl . '../resources/css/styles.css');
        $this->view->headScript()->appendFile($baseUrl . '../resources/js/main.js');
    }
    
    public function indexAction()
    {
        // Get current settings
        $apiController = new PleskExt\GDriveAutoBackups\ApiController();
        $backupController = new PleskExt\GDriveAutoBackups\BackupController();
        
        // Prepare data for the view
        $this->view->credentials = $apiController->getCredentials();
        $this->view->settings = $backupController->getSettings();
        $this->view->logs = $backupController->getLogs();
        
        // Build proper OAuth redirect URI using Plesk's URL system
        $this->view->redirectUri = $this->view->url(['action' => 'oauth2callback'], null, true);
        
        // Check if we have an auth status from URL params
        $authStatus = $this->getParam('auth');
        $authMessage = $this->getParam('message');
        
        $this->view->authStatus = $authStatus;
        $this->view->authMessage = $authMessage;
    }
    
    public function saveCredentialsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        if (!$this->getRequest()->isPost()) {
            $this->getResponse()->setHttpResponseCode(405);
            echo json_encode(['success' => false, 'error' => 'Only POST method is allowed']);
            return;
        }
        
        try {
            $apiController = new PleskExt\GDriveAutoBackups\ApiController();
            
            $clientId = $this->getParam('clientId');
            $clientSecret = $this->getParam('clientSecret');
            $redirectUri = $this->getParam('redirectUri');
            
            $result = $apiController->saveCredentials($clientId, $clientSecret, $redirectUri);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    public function getAuthUrlAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        try {
            $apiController = new PleskExt\GDriveAutoBackups\ApiController();
            $client = $apiController->createGoogleClient();
            $authUrl = $client->createAuthUrl();
            
            echo json_encode(['success' => true, 'authUrl' => $authUrl]);
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    public function saveSettingsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        if (!$this->getRequest()->isPost()) {
            $this->getResponse()->setHttpResponseCode(405);
            echo json_encode(['success' => false, 'error' => 'Only POST method is allowed']);
            return;
        }
        
        try {
            $backupController = new PleskExt\GDriveAutoBackups\BackupController();
            
            $backupDirs = $this->getParam('backupDirs', []);
            $backupFreq = $this->getParam('backupFreq', 'daily');
            $retentionCount = $this->getParam('retentionCount', 5);
            
            $result = $backupController->saveSettings($backupDirs, $backupFreq, $retentionCount);
            
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    public function runBackupAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        try {
            $backupController = new PleskExt\GDriveAutoBackups\BackupController();
            $result = $backupController->runBackup();
            
            echo json_encode($result);
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    public function oauth2callbackAction()
    {
        try {
            $apiController = new PleskExt\GDriveAutoBackups\ApiController();
            $client = $apiController->createGoogleClient();
            
            if ($this->getParam('code')) {
                $token = $client->fetchAccessTokenWithAuthCode($this->getParam('code'));
                $client->setAccessToken($token);
                
                $apiController->saveToken($token);
                
                // Redirect back to main page with success status
                $this->_helper->redirector('index', null, null, ['auth' => 'success']);
            } else {
                // Redirect back with error
                $message = $this->getParam('error_description', 'No authorization code provided');
                $this->_helper->redirector('index', null, null, ['auth' => 'error', 'message' => $message]);
            }
        } catch (Exception $e) {
            $this->_helper->redirector('index', null, null, ['auth' => 'error', 'message' => $e->getMessage()]);
        }
    }
    
    public function getCredentialsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        try {
            $apiController = new PleskExt\GDriveAutoBackups\ApiController();
            $credentials = $apiController->getCredentials();
            
            // For security, don't return the actual client secret
            if (!empty($credentials['clientSecret'])) {
                $credentials['clientSecret'] = '••••••••';
            }
            
            echo json_encode(['success' => true, 'credentials' => $credentials]);
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    public function getSettingsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        try {
            $backupController = new PleskExt\GDriveAutoBackups\BackupController();
            $settings = $backupController->getSettings();
            
            echo json_encode(['success' => true, 'settings' => $settings]);
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
    
    public function getLogsAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        try {
            $backupController = new PleskExt\GDriveAutoBackups\BackupController();
            $logs = $backupController->getLogs();
            
            echo json_encode(['success' => true, 'logs' => $logs]);
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
