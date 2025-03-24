<?php
//#!/usr/bin/env php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PleskExt\GDriveAutoBackups\BackupController;

try {
    $backupController = new BackupController();
    $result = $backupController->runBackup();
    
    if (!$result['success']) {
        echo "Backup failed: " . $result['error'] . "\n";
        exit(1);
    }
    
    echo "Backup completed successfully\n";
    exit(0);
} catch (Exception $e) {
    echo "Backup script error: " . $e->getMessage() . "\n";
    exit(1);
}