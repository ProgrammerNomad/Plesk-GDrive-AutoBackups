<?php
// Copyright 2025. ProgrammerNomad. All rights reserved.

/**
 * Extension Configuration Defaults Hook
 * This hook provides default configuration for the extension
 */

class Modules_PleskGdriveAutobackups_ConfigDefaults extends pm_Hook_ConfigDefaults
{
    public function getDefaults()
    {
        return [
            'google_client_id' => '',
            'google_client_secret' => '',
            'google_redirect_uri' => '',
            'google_access_token' => '',
            'backup_dirs' => json_encode([]),
            'backup_freq' => 'daily',
            'retention_count' => 5,
            'backup_logs' => json_encode([])
        ];
    }
}
