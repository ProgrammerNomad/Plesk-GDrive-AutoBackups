#!/usr/bin/env php
<?php
// Make sure this is executable with the shebang at the top

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PleskExt\GDriveAutoBackups\BackupController;
use pm_Context;

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