<?php
namespace PleskExt\GDriveAutoBackups;

class BackupController
{
    private $pm; // Plesk PM_Settings instance
    private $apiController;
    
    public function __construct()
    {
        $this->pm = pm_Settings::getInstance();
        $this->apiController = new ApiController();
    }
    
    /**
     * Save backup settings
     */
    public function saveSettings($backupDirs, $backupFreq, $retentionCount)
    {
        // Validate inputs
        if (empty($backupDirs)) {
            throw new \Exception('At least one backup directory must be selected');
        }
        
        if (!in_array($backupFreq, ['daily', 'weekly', 'monthly'])) {
            throw new \Exception('Invalid backup frequency');
        }
        
        $retentionCount = (int)$retentionCount;
        if ($retentionCount < 1 || $retentionCount > 100) {
            throw new \Exception('Retention count must be between 1 and 100');
        }
        
        // Store settings
        $this->pm->set('backup_dirs', json_encode($backupDirs));
        $this->pm->set('backup_freq', $backupFreq);
        $this->pm->set('retention_count', $retentionCount);
        
        // Configure the cron job based on frequency
        $this->configureCronJob($backupFreq);
        
        return ['success' => true];
    }
    
    /**
     * Get stored backup settings
     */
    public function getSettings()
    {
        $backupDirs = $this->pm->get('backup_dirs', '[]');
        
        return [
            'backupDirs' => json_decode($backupDirs, true),
            'backupFreq' => $this->pm->get('backup_freq', 'daily'),
            'retentionCount' => (int)$this->pm->get('retention_count', 5)
        ];
    }
    
    /**
     * Configure cron job based on backup frequency
     */
    private function configureCronJob($frequency)
    {
        // Path to the backup script that will be executed by cron
        $scriptPath = \pm_Context::getPlibDir() . 'scripts/backup.php';
        
        // Get existing scheduled tasks
        $scheduledTasks = (new \pm_Scheduler())->listTasks();
        
        // Remove any existing backup tasks
        foreach ($scheduledTasks as $task) {
            if (strpos($task['command'], $scriptPath) !== false) {
                try {
                    (new \pm_Scheduler())->removeTask($task['id']);
                } catch (\Exception $e) {
                    \pm_Log::err("Failed to remove old task: " . $e->getMessage());
                }
            }
        }
        
        // Create new task with appropriate schedule
        $schedule = '';
        switch ($frequency) {
            case 'daily':
                $schedule = '0 2 * * *'; // Run at 2:00 AM every day
                break;
            case 'weekly':
                $schedule = '0 2 * * 0'; // Run at 2:00 AM every Sunday
                break;
            case 'monthly':
                $schedule = '0 2 1 * *'; // Run at 2:00 AM on the 1st of each month
                break;
        }
        
        try {
            (new \pm_Scheduler())->addCronJob($schedule, $scriptPath);
        } catch (\Exception $e) {
            \pm_Log::err("Failed to add cron job: " . $e->getMessage());
        }
        
        return true;
    }
    
    /**
     * Run a backup immediately
     */
    public function runBackup()
    {
        try {
            $settings = $this->getSettings();
            
            // Check if Google Drive is connected
            $client = $this->apiController->createGoogleClient();
            if (!$client->getAccessToken()) {
                throw new \Exception('Not connected to Google Drive');
            }
            
            // Log the start of the backup process
            $this->logBackupEvent('Starting backup process');
            
            // Create Drive service
            $driveService = new \Google\Service\Drive($client);
            
            // Create a timestamp for the backup
            $timestamp = date('Y-m-d_H-i-s');
            $backupName = "plesk_backup_{$timestamp}";
            
            // Temporary directory for backup files
            $tempDir = sys_get_temp_dir() . '/' . $backupName;
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Backup each selected directory
            foreach ($settings['backupDirs'] as $dir) {
                $this->logBackupEvent("Backing up directory: {$dir}");
                $tarFile = "{$tempDir}/" . basename($dir) . ".tar.gz";
                
                // Check if we're on Windows or Linux
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Windows - use 7zip or PowerShell compression
                    $dirName = basename($dir);
                    $tarFile = "{$tempDir}\\{$dirName}.zip";
                    
                    // Use PowerShell to create compressed archive
                    $powershellCmd = sprintf(
                        'powershell.exe -Command "Compress-Archive -Path %s -DestinationPath %s -Force"',
                        escapeshellarg($dir),
                        escapeshellarg($tarFile)
                    );
                    
                    exec($powershellCmd, $output, $returnVar);
                } else {
                    // Linux - use tar
                    $srcDir = escapeshellarg(dirname($dir));
                    $srcBase = escapeshellarg(basename($dir));
                    $tarFile = escapeshellarg($tarFile);

                    exec("tar -czf {$tarFile} -C {$srcDir} {$srcBase}", $output, $returnVar);
                }
                
                if ($returnVar !== 0) {
                    throw new \Exception("Failed to create backup archive for {$dir}");
                }
                
                // Upload to Google Drive
                $this->uploadFileToDrive($driveService, $tarFile, basename($tarFile));
                
                // Clean up local tar file
                unlink($tarFile);
            }
            
            // Clean up temp directory
            rmdir($tempDir);
            
            // Apply retention policy
            $this->applyRetentionPolicy($driveService, $settings['retentionCount']);
            
            $this->logBackupEvent('Backup completed successfully');
            
            return ['success' => true, 'message' => 'Backup completed successfully'];
        } catch (\Exception $e) {
            $this->logBackupEvent('Backup failed: ' . $e->getMessage(), 'ERROR');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Upload a file to Google Drive
     */
    private function uploadFileToDrive($driveService, $filePath, $fileName)
    {
        // Check if our app folder exists, create if not
        $folderId = $this->getOrCreateFolder($driveService, 'Plesk Backups');
        
        // Prepare file metadata
        $fileMetadata = new \Google\Service\Drive\DriveFile([
            'name' => $fileName,
            'parents' => [$folderId]
        ]);
        
        // For larger files, use chunked upload instead of loading entire file into memory
        if (filesize($filePath) > 10 * 1024 * 1024) { // If file is larger than 10MB
            $this->logBackupEvent("Large file detected, using chunked upload for: {$fileName}");
            
            // Use resumable upload
            $client = $driveService->getClient();
            $client->setDefer(true);
            
            $request = $driveService->files->create($fileMetadata);
            $media = new \Google\Http\MediaFileUpload(
                $client,
                $request,
                'application/gzip',
                null,
                true, // Enable resumable uploads
                1024 * 1024 // Chunk size in bytes (1MB)
            );
            $media->setFileSize(filesize($filePath));
            
            // Upload the file in chunks
            $status = false;
            $handle = fopen($filePath, "rb");
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, 1024 * 1024);
                $status = $media->nextChunk($chunk);
            }
            fclose($handle);
            
            $client->setDefer(false);
            
            if ($status) {
                $fileId = $status->getId();
                $this->logBackupEvent("Uploaded large file: {$fileName} with ID: {$fileId}");
                return $fileId;
            }
            
            throw new \Exception("Failed to upload large file: {$fileName}");
        } else {
            // Original code for smaller files
            $content = file_get_contents($filePath);
            $file = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'application/gzip',
                'uploadType' => 'multipart',
                'fields' => 'id'
            ]);
            
            $this->logBackupEvent("Uploaded file: {$fileName} with ID: {$file->id}");
            return $file->id;
        }
    }
    
    /**
     * Get or create a folder in Google Drive
     */
    private function getOrCreateFolder($driveService, $folderName)
    {
        // Check if folder already exists
        $response = $driveService->files->listFiles([
            'q' => "mimeType='application/vnd.google-apps.folder' and name='{$folderName}' and trashed=false",
            'spaces' => 'drive'
        ]);
        
        if (count($response->getFiles()) > 0) {
            // Folder exists, return its ID
            return $response->getFiles()[0]->getId();
        }
        
        // Folder doesn't exist, create it
        $folderMetadata = new \Google\Service\Drive\DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder'
        ]);
        
        $folder = $driveService->files->create($folderMetadata, ['fields' => 'id']);
        
        $this->logBackupEvent("Created folder: {$folderName} with ID: {$folder->id}");
        
        return $folder->getId();
    }
    
    /**
     * Apply retention policy by removing old backups
     */
    private function applyRetentionPolicy($driveService, $retentionCount)
    {
        // Get the backup folder ID
        $response = $driveService->files->listFiles([
            'q' => "mimeType='application/vnd.google-apps.folder' and name='Plesk Backups' and trashed=false",
            'spaces' => 'drive'
        ]);
        
        if (count($response->getFiles()) === 0) {
            // No backup folder found, nothing to clean up
            return;
        }
        
        $folderId = $response->getFiles()[0]->getId();
        
        // Get all backup files in the folder
        $response = $driveService->files->listFiles([
            'q' => "'{$folderId}' in parents and mimeType='application/gzip' and trashed=false",
            'orderBy' => 'createdTime',
            'spaces' => 'drive'
        ]);
        
        $files = $response->getFiles();
        
        // If we have more backups than our retention policy allows, delete the oldest ones
        $filesToDelete = count($files) - $retentionCount;
        if ($filesToDelete <= 0) {
            return;
        }
        
        $this->logBackupEvent("Applying retention policy: keeping {$retentionCount} backups, removing {$filesToDelete} old backups");
        
        // Delete the oldest files
        for ($i = 0; $i < $filesToDelete; $i++) {
            $fileId = $files[$i]->getId();
            $fileName = $files[$i]->getName();
            
            $driveService->files->delete($fileId);
            $this->logBackupEvent("Deleted old backup: {$fileName}");
        }
    }
    
    /**
     * Log a backup event
     */
    private function logBackupEvent($message, $level = 'INFO')
    {
        $logMessage = '[' . date('Y-m-d H:i:s') . '] ' . $level . ': ' . $message;
        \pm_Log::info($logMessage); // Use Plesk's logging
    }
    
    /**
     * Get backup logs
     */
    public function getLogs()
    {
        $logs = $this->pm->get('backup_logs', '[]');
        return json_decode($logs, true);
    }
}