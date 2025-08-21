<?php
// Copyright 2025. ProgrammerNomad. All rights reserved.

/**
 * GDrive AutoBackups Extension Library
 * 
 * This file initializes the extension and provides common functionality.
 */

// Ensure we have access to Plesk's extension API
if (!class_exists('pm_Settings')) {
    throw new Exception('This extension can only be run within Plesk environment');
}

// Load vendor dependencies
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main extension class
 */
class Modules_PleskGdriveAutobackups
{
    const MODULE_ID = 'plesk-gdrive-autobackups';
    
    /**
     * Get extension instance
     */
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
    
    /**
     * Get module ID
     */
    public static function getModuleId()
    {
        return self::MODULE_ID;
    }
    
    /**
     * Initialize extension
     */
    public function init()
    {
        // Initialize any extension-wide settings or hooks here
        $this->registerHooks();
    }
    
    /**
     * Register extension hooks
     */
    private function registerHooks()
    {
        // Register any necessary hooks for backup integration
        // This would be where you'd integrate with Plesk's backup system
    }
    
    /**
     * Get extension settings
     */
    public function getSettings()
    {
        $settings = pm_Settings::get(self::MODULE_ID, []);
        return $settings;
    }
    
    /**
     * Save extension settings
     */
    public function saveSettings($settings)
    {
        pm_Settings::set(self::MODULE_ID, $settings);
    }
}

// Initialize the extension
Modules_PleskGdriveAutobackups::getInstance()->init();