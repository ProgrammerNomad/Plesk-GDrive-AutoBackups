<?php
/**
 * Content Include Hook
 * 
 * Provides UI content for the extension's configuration pages.
 * This hook is used to include custom HTML, CSS, and JavaScript in Plesk UI.
 * 
 * @copyright 2025 ProgrammerNomad. All rights reserved.
 */

/**
 * Include custom content in Plesk UI
 * 
 * This hook provides content that gets included in various Plesk pages.
 * It can be used to inject configuration UI, scripts, styles, etc.
 */
class Modules_PleskGdriveAutobackups_ContentInclude extends pm_Hook_ContentInclude
{
    /**
     * Get content for specific views
     * 
     * Called when Plesk is rendering pages where this extension should include content.
     * 
     * @return void Outputs content directly
     */
    public function onView()
    {
        // This is called during page rendering
        // Can output HTML, CSS, JavaScript as needed
        ?>
<!-- GDrive AutoBackups Extension Content -->
<script>
    // Extension-specific scripts can be added here
    console.log('GDrive AutoBackups extension loaded');
</script>
        <?php
    }
}
