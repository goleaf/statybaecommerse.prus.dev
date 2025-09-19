<?php

/**
 * Test Runner by Category
 *
 * This script allows running tests by category and provides
 * comprehensive test execution options.
 */
class TestRunner
{
    private array $categories = [
        'admin' => [
            'resources' => 'tests/admin/resources/',
            'widgets' => 'tests/admin/widgets/',
        ],
        'frontend' => [
            'features' => 'tests/frontend/features/',
            'admin' => 'tests/frontend/admin/',
        ],
        'api' => [
            'endpoints' => 'tests/api/endpoints/',
        ],
        'models' => [
            'core' => 'tests/models/core/',
            'business' => 'tests/models/business/',
            'relationships' => 'tests/models/relationships/',
        ],
        'services' => [
            'business' => 'tests/services/business/',
        ],
        'browser' => [
            'admin' => 'tests/browser/admin/',
            'frontend' => 'tests/browser/frontend/',
            'integration' => 'tests/browser/integration/',
        ],
    ];

    public function runTests(string $category = null, string $subcategory = null, array $options = []): void
    {
        echo "🧪 Laravel E-commerce Test Runner\n";
        echo "================================\n\n";

        if ($category === null) {
            $this->showHelp();
            return;
        }

        if (!isset($this->categories[$category])) {
            echo "❌ Invalid category: $category\n";
            $this->showAvailableCategories();
            return;
        }

        if ($subcategory === null) {
            $this->runCategoryTests($category, $options);
        } else {
            $this->runSubcategoryTests($category, $subcategory, $options);
        }
    }

    private function runCategoryTests(string $category, array $options): void
    {
        echo "📁 Running all tests in category: $category\n\n";

        foreach ($this->categories[$category] as $subcategory => $path) {
            if (is_dir($path)) {
                echo "🔍 Running $category/$subcategory tests...\n";
                $this->executeTestCommand($path, $options);
            }
        }
    }

    private function runSubcategoryTests(string $category, string $subcategory, array $options): void
    {
        if (!isset($this->categories[$category][$subcategory])) {
            echo "❌ Invalid subcategory: $subcategory for category: $category\n";
            return;
        }

        $path = $this->categories[$category][$subcategory];

        if (!is_dir($path)) {
            echo "❌ Directory does not exist: $path\n";
            return;
        }

        echo "🔍 Running $category/$subcategory tests...\n";
        $this->executeTestCommand($path, $options);
    }

    private function executeTestCommand(string $path, array $options): void
    {
        $command = 'php artisan test ' . $path;

        if (isset($options['stop-on-failure']) && $options['stop-on-failure']) {
            $command .= ' --stop-on-failure';
        }

        if (isset($options['filter']) && $options['filter']) {
            $command .= ' --filter=' . $options['filter'];
        }

        if (isset($options['coverage']) && $options['coverage']) {
            $command .= ' --coverage';
        }

        if (isset($options['parallel']) && $options['parallel']) {
            $command .= ' --parallel';
        }

        echo "🚀 Executing: $command\n";
        echo "----------------------------------------\n";

        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        foreach ($output as $line) {
            echo $line . "\n";
        }

        echo "----------------------------------------\n";

        if ($returnCode === 0) {
            echo "✅ Tests completed successfully!\n";
        } else {
            echo "❌ Tests failed with exit code: $returnCode\n";
        }

        echo "\n";
    }

    private function showHelp(): void
    {
        echo "Usage: php run_tests_by_category.php [category] [subcategory] [options]\n\n";
        echo "Categories:\n";
        $this->showAvailableCategories();
        echo "\nOptions:\n";
        echo "  --stop-on-failure    Stop on first failure\n";
        echo "  --filter=pattern     Filter tests by pattern\n";
        echo "  --coverage           Generate coverage report\n";
        echo "  --parallel           Run tests in parallel\n\n";
        echo "Examples:\n";
        echo "  php run_tests_by_category.php admin                    # Run all admin tests\n";
        echo "  php run_tests_by_category.php admin resources         # Run admin resource tests\n";
        echo "  php run_tests_by_category.php admin widgets --stop-on-failure  # Run widget tests with stop on failure\n";
        echo "  php run_tests_by_category.php models core --filter=User  # Run core model tests filtered by User\n";
    }

    private function showAvailableCategories(): void
    {
        foreach ($this->categories as $category => $subcategories) {
            echo "  $category:\n";
            foreach ($subcategories as $subcategory => $path) {
                $exists = is_dir($path) ? '✅' : '❌';
                echo "    $subcategory $exists\n";
            }
        }
    }

    public function showTestStats(): void
    {
        echo "📊 Test Statistics\n";
        echo "==================\n\n";

        $totalTests = 0;
        $totalFiles = 0;

        foreach ($this->categories as $category => $subcategories) {
            echo "$category:\n";
            foreach ($subcategories as $subcategory => $path) {
                if (is_dir($path)) {
                    $files = glob($path . '*.php');
                    $fileCount = count($files);
                    $totalFiles += $fileCount;
                    echo "  $subcategory: $fileCount files\n";
                } else {
                    echo "  $subcategory: 0 files (directory not found)\n";
                }
            }
            echo "\n";
        }

        echo "Total test files: $totalFiles\n";
    }
}

// Parse command line arguments
$category = $argv[1] ?? null;
$subcategory = $argv[2] ?? null;
$options = [];

// Parse options
for ($i = 3; $i < count($argv); $i++) {
    $arg = $argv[$i];
    if (str_starts_with($arg, '--')) {
        $option = substr($arg, 2);
        if (str_contains($option, '=')) {
            [$key, $value] = explode('=', $option, 2);
            $options[$key] = $value;
        } else {
            $options[$option] = true;
        }
    }
}

$runner = new TestRunner();

// Handle special commands
if ($category === 'stats') {
    $runner->showTestStats();
} else {
    $runner->runTests($category, $subcategory, $options);
}
