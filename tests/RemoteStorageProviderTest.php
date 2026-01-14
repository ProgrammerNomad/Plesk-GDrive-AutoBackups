<?php
/**
 * Unit Tests for RemoteStorageProvider Hook
 * 
 * Basic tests to verify the RemoteStorageProvider hook works correctly
 * Can be run locally without needing a full Plesk installation
 */

// Mock Plesk classes for local testing
if (!class_exists('pm_Hook_RemoteStorageProvider')) {
    abstract class pm_Hook_RemoteStorageProvider
    {
        const STATUS_OK = 0;
        const STATUS_NOT_CONFIGURED = 1;
        const STATUS_ERROR = 2;
    }
}

if (!class_exists('pm_Settings')) {
    class pm_Settings
    {
        private static $instance;
        private $data = [];

        public static function getInstance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function set($key, $value)
        {
            $this->data[$key] = $value;
        }

        public function get($key, $default = '')
        {
            return isset($this->data[$key]) ? $this->data[$key] : $default;
        }
    }
}

if (!function_exists('pm_Log::err')) {
    class pm_Log
    {
        public static function err($message)
        {
            echo "[ERROR] $message\n";
        }
    }
}

// Load the RemoteStorageProvider class
require_once __DIR__ . '/../plib/hooks/RemoteStorageProvider.php';

/**
 * Test Suite for RemoteStorageProvider
 */
class RemoteStorageProviderTest
{
    private $provider;
    private $passed = 0;
    private $failed = 0;

    public function __construct()
    {
        $this->provider = new Modules_PleskGdriveAutobackups_RemoteStorageProvider();
    }

    /**
     * Run all tests
     */
    public function runAllTests()
    {
        echo "\n=== RemoteStorageProvider Unit Tests ===\n\n";

        $this->testGetInfo();
        $this->testIsConfigured_NotConfigured();
        $this->testIsConfigured_Configured();
        $this->testGetStatus_NotConfigured();
        $this->testGetStatus_Configured();
        $this->testGetStatusMessage();
        $this->testGetStorageStats();
        $this->testSupportsRestore();
        $this->testRequiresAuthentication();
        $this->testGetName();
        $this->testGetDescription();

        echo "\n=== Test Results ===\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Total: " . ($this->passed + $this->failed) . "\n\n";

        return $this->failed === 0;
    }

    /**
     * Test: getInfo returns required fields
     */
    private function testGetInfo()
    {
        $info = $this->provider->getInfo();

        $this->assert(isset($info['id']), 'Info has id field');
        $this->assert($info['id'] === 'gdrive', "Info id is 'gdrive'");
        $this->assert(isset($info['name']), 'Info has name field');
        $this->assert(isset($info['description']), 'Info has description field');
        $this->assert(isset($info['type']), 'Info has type field');
    }

    /**
     * Test: isConfigured returns false when not configured
     */
    private function testIsConfigured_NotConfigured()
    {
        // Clear settings
        pm_Settings::getInstance()->set('google_client_id', '');
        pm_Settings::getInstance()->set('google_access_token', '');

        $result = $this->provider->isConfigured();
        $this->assert($result === false, 'isConfigured returns false when not configured');
    }

    /**
     * Test: isConfigured returns true when configured
     */
    private function testIsConfigured_Configured()
    {
        // Set up fake credentials
        pm_Settings::getInstance()->set('google_client_id', 'test-client-id');
        pm_Settings::getInstance()->set('google_access_token', 'test-token');

        $result = $this->provider->isConfigured();
        $this->assert($result === true, 'isConfigured returns true when configured');
    }

    /**
     * Test: getStatus returns STATUS_NOT_CONFIGURED when not set up
     */
    private function testGetStatus_NotConfigured()
    {
        pm_Settings::getInstance()->set('google_client_id', '');
        pm_Settings::getInstance()->set('google_access_token', '');

        $status = $this->provider->getStatus();
        $expected = pm_Hook_RemoteStorageProvider::STATUS_NOT_CONFIGURED;
        $this->assert($status === $expected, "getStatus returns STATUS_NOT_CONFIGURED when not configured");
    }

    /**
     * Test: getStatus returns STATUS_OK when configured
     */
    private function testGetStatus_Configured()
    {
        pm_Settings::getInstance()->set('google_client_id', 'test-id');
        pm_Settings::getInstance()->set('google_access_token', 'test-token');

        $status = $this->provider->getStatus();
        $expected = pm_Hook_RemoteStorageProvider::STATUS_OK;
        $this->assert($status === $expected, 'getStatus returns STATUS_OK when configured');
    }

    /**
     * Test: getStatusMessage returns appropriate message
     */
    private function testGetStatusMessage()
    {
        // Test not configured
        pm_Settings::getInstance()->set('google_client_id', '');
        $msg = $this->provider->getStatusMessage();
        $this->assert($msg === 'Not configured', 'getStatusMessage returns "Not configured" when not set up');

        // Test configured
        pm_Settings::getInstance()->set('google_client_id', 'test-id');
        pm_Settings::getInstance()->set('google_access_token', 'test-token');
        pm_Settings::getInstance()->set('google_account_email', 'user@example.com');
        $msg = $this->provider->getStatusMessage();
        $this->assert(strpos($msg, 'user@example.com') !== false, 'getStatusMessage includes account email when configured');
    }

    /**
     * Test: getStorageStats returns array with required keys
     */
    private function testGetStorageStats()
    {
        $stats = $this->provider->getStorageStats();

        $this->assert(is_array($stats), 'getStorageStats returns array');
        $this->assert(isset($stats['used']), 'Stats has used key');
        $this->assert(isset($stats['total']), 'Stats has total key');
        $this->assert(isset($stats['free']), 'Stats has free key');
        $this->assert(is_int($stats['used']), 'Used is integer');
        $this->assert(is_int($stats['total']), 'Total is integer');
        $this->assert(is_int($stats['free']), 'Free is integer');
    }

    /**
     * Test: supportsRestore returns true
     */
    private function testSupportsRestore()
    {
        $result = $this->provider->supportsRestore();
        $this->assert($result === true, 'supportsRestore returns true');
    }

    /**
     * Test: requiresAuthentication returns true
     */
    private function testRequiresAuthentication()
    {
        $result = $this->provider->requiresAuthentication();
        $this->assert($result === true, 'requiresAuthentication returns true');
    }

    /**
     * Test: getName returns string
     */
    private function testGetName()
    {
        $name = $this->provider->getName();
        $this->assert(is_string($name), 'getName returns string');
        $this->assert(!empty($name), 'getName returns non-empty string');
    }

    /**
     * Test: getDescription returns string
     */
    private function testGetDescription()
    {
        $desc = $this->provider->getDescription();
        $this->assert(is_string($desc), 'getDescription returns string');
        $this->assert(!empty($desc), 'getDescription returns non-empty string');
    }

    /**
     * Helper: Assert condition and track results
     */
    private function assert($condition, $message)
    {
        if ($condition) {
            echo "✓ $message\n";
            $this->passed++;
        } else {
            echo "✗ $message\n";
            $this->failed++;
        }
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $test = new RemoteStorageProviderTest();
    $success = $test->runAllTests();
    exit($success ? 0 : 1);
}
