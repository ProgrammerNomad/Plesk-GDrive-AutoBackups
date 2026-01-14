<?php
/**
 * Custom Info Hook
 * 
 * Provides custom information display in Plesk panels.
 * 
 * @copyright 2025 ProgrammerNomad. All rights reserved.
 */

/**
 * Display custom information in Plesk UI
 * 
 * This hook is called to display additional information or status
 * related to the extension in various Plesk pages.
 */
class Modules_PleskGdriveAutobackups_CustomInfo extends pm_Hook_CustomInfo
{
    /**
     * Get custom info to display
     * 
     * @return array Information to display
     */
    public function getInfo()
    {
        try {
            $info = [];

            // Add Google Drive status info
            $status = new Modules_PleskGdriveAutobackups_Backup_Transport();
            
            $info[] = [
                'title' => 'Google Drive Backup Status',
                'content' => $status->getStatusMessage(),
            ];

            // Add quota info if configured
            if ($status->isConfigured()) {
                $quota = $status->getQuota();
                if ($quota['total'] > 0) {
                    $usedPercent = round(($quota['used'] / $quota['total']) * 100, 1);
                    $info[] = [
                        'title' => 'Google Drive Storage',
                        'content' => sprintf(
                            '%s / %s (%s%%)',
                            $this->formatBytes($quota['used']),
                            $this->formatBytes($quota['total']),
                            $usedPercent
                        ),
                    ];
                }
            }

            return $info;
        } catch (Exception $e) {
            pm_Log::err(__CLASS__ . '::getInfo() - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format bytes to human readable format
     * 
     * @param int $bytes Number of bytes
     * @return string Formatted size
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
