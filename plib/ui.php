<?php
// Copyright 2025. ProgrammerNomad. All rights reserved.

/**
 * Main UI entry point for GDrive AutoBackups extension
 * This file is automatically loaded by Plesk when the extension is accessed
 */

// Ensure we're in Plesk environment
if (!class_exists('pm_Context')) {
    die('This extension can only be run within Plesk environment');
}

// Load extension library
require_once __DIR__ . '/library.php';

// Route to IndexController
$application = new Zend_Application('', []);
$application->bootstrap();

$front = Zend_Controller_Front::getInstance();
$front->setControllerDirectory(__DIR__ . '/controllers');
$front->setParam('useDefaultControllerAlways', false);

// Set default module name based on extension ID
$front->setDefaultModule('plesk-gdrive-autobackups');
$front->setDefaultController('index');
$front->setDefaultAction('index');

$front->dispatch();