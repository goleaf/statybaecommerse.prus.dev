<?php

declare(strict_types=1);

/**
 * Test Execution Script
 * 
 * Script for running tests by groups
 */

require_once __DIR__ . '/vendor/autoload.php';

use Tests\Feature\TestGroups;

class TestRunner
{
    private array $groups;
    private string $basePath;

    public function __construct()
    {
        $this->groups = TestGroups::getAllGroups();
        $this->basePath = __DIR__;
    }

    /**
     * Run tests by group
     */
    public function runGroup(string $groupName): int
    {
        if (!isset($this->groups[$groupName])) {
            echo "❌ Group '{$groupName}' not found.\n";
            echo "Available groups: " . implode(', ', array_keys($this->groups)) . "\n";
            return 1;
        }

        echo "🚀 Running {$groupName} tests...\n";
        
        $tests = $this->groups[$groupName];
        $success = 0;
        $failed = 0;

        foreach ($tests as $test) {
            $result = $this->runSingleTest($test);
            if ($result === 0) {
                $success++;
                echo "✅ {$test}\n";
            } else {
                $failed++;
                echo "❌ {$test}\n";
            }
        }

        echo "\n📊 Results for {$groupName}:\n";
        echo "✅ Passed: {$success}\n";
        echo "❌ Failed: {$failed}\n";
        echo "📈 Total: " . ($success + $failed) . "\n";

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Run all tests
     */
    public function runAll(): int
    {
        echo "🚀 Running all tests...\n";
        
        $totalSuccess = 0;
        $totalFailed = 0;

        foreach ($this->groups as $groupName => $tests) {
            echo "\n📁 Running {$groupName} group...\n";
            $result = $this->runGroup($groupName);
            
            if ($result === 0) {
                $totalSuccess++;
            } else {
                $totalFailed++;
            }
        }

        echo "\n🎯 Final Results:\n";
        echo "✅ Groups Passed: {$totalSuccess}\n";
        echo "❌ Groups Failed: {$totalFailed}\n";
        echo "📈 Total Groups: " . ($totalSuccess + $totalFailed) . "\n";

        return $totalFailed > 0 ? 1 : 0;
    }

    /**
     * Run a single test
     */
    private function runSingleTest(string $testName): int
    {
        $command = "cd {$this->basePath} && php artisan test tests/Feature/{$testName}.php --stop-on-failure 2>/dev/null";
        $output = [];
        $returnCode = 0;
        
        exec($command, $output, $returnCode);
        
        return $returnCode;
    }

    /**
     * List available groups
     */
    public function listGroups(): void
    {
        echo "📋 Available test groups:\n\n";
        
        foreach ($this->groups as $groupName => $tests) {
            echo "📁 {$groupName} (" . count($tests) . " tests)\n";
            foreach ($tests as $test) {
                echo "  - {$test}\n";
            }
            echo "\n";
        }
    }

    /**
     * Show help
     */
    public function showHelp(): void
    {
        echo "🧪 Test Runner\n\n";
        echo "Usage:\n";
        echo "  php run-tests.php [group]     Run specific group\n";
        echo "  php run-tests.php all         Run all groups\n";
        echo "  php run-tests.php list        List available groups\n";
        echo "  php run-tests.php help        Show this help\n\n";
        echo "Available groups: " . implode(', ', array_keys($this->groups)) . "\n";
    }
}

// Main execution
$runner = new TestRunner();

if ($argc < 2) {
    $runner->showHelp();
    exit(1);
}

$command = $argv[1];

switch ($command) {
    case 'help':
        $runner->showHelp();
        break;
        
    case 'list':
        $runner->listGroups();
        break;
        
    case 'all':
        exit($runner->runAll());
        
    default:
        if (isset($runner->groups[$command])) {
            exit($runner->runGroup($command));
        } else {
            echo "❌ Unknown command: {$command}\n";
            $runner->showHelp();
            exit(1);
        }
}
