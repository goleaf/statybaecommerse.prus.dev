<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

final class ProjectTest extends Command
{
    protected $signature = 'project:test';

    protected $description = 'Run all project tests one by one and save results';

    private string $resultsFile = 'test-results.json';

    public function handle(): int
    {
        $this->info('Starting project tests...');

        // Get all test files
        $testFiles = $this->getAllTestFiles();
        $totalTests = count($testFiles);

        $this->info("Found {$totalTests} test files");

        // Initialize results
        $results = [
            'started_at'      => now()->toDateTimeString(),
            'completed_at'    => null,
            'total_tests'     => $totalTests,
            'completed_tests' => 0,
            'passed_tests'    => 0,
            'failed_tests'    => 0,
            'status'          => 'running',
            'tests'           => [],
            'errors'          => [],
        ];

        $this->saveResults($results);

        // Run each test file individually
        foreach ($testFiles as $index => $testFile) {
            $currentTest = $index + 1;
            $this->info("Running test {$currentTest}/{$totalTests}: {$testFile}");

            $result = Process::run("php artisan test {$testFile}");

            $testResult = [
                'file'   => $testFile,
                'status' => $result->successful() ? 'passed' : 'failed',
                'output' => $result->output(),
                'error'  => $result->failed() ? $result->errorOutput() : null,
                'run_at' => now()->toDateTimeString(),
            ];

            $results['tests'][] = $testResult;
            $results['completed_tests']++;

            if ($result->successful()) {
                $results['passed_tests']++;
            } else {
                $results['failed_tests']++;
                $results['errors'][] = [
                    'file'   => $testFile,
                    'error'  => $result->errorOutput(),
                    'output' => $result->output(),
                ];
            }

            $this->saveResults($results);

            // Small delay to prevent overwhelming the system
            usleep(100000);  // 0.1 second
        }

        // Mark as completed
        $results['completed_at'] = now()->toDateTimeString();
        $results['status'] = 'completed';
        $this->saveResults($results);

        $this->info('All tests completed!');
        $this->info("Passed: {$results['passed_tests']}/{$totalTests}");
        $this->info("Failed: {$results['failed_tests']}/{$totalTests}");

        return self::SUCCESS;
    }

    private function getAllTestFiles(): array
    {
        $testFiles = [];

        // Get all test files from tests directory
        $directories = [
            base_path('tests/Feature'),
            base_path('tests/Unit'),
        ];

        foreach ($directories as $directory) {
            if (File::exists($directory)) {
                $files = File::allFiles($directory);
                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $testFiles[] = $file->getPathname();
                    }
                }
            }
        }

        return $testFiles;
    }

    private function saveResults(array $results): void
    {
        $path = storage_path("app/{$this->resultsFile}");
        File::put($path, json_encode($results, JSON_PRETTY_PRINT));
    }
}
