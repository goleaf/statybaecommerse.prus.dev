<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CodeStyleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ValidateCodeStyleCommand extends Command
{
    protected $signature = 'code-style:validate 
                           {--path= : Specific file or directory to validate}
                           {--extensions=php : Comma-separated list of file extensions}
                           {--strict : Exit with error code if any issues found}
                           {--report : Generate a detailed report}';

    protected $description = 'Validate code style in PHP files';

    public function handle(CodeStyleService $codeStyleService): int
    {
        $path = $this->option('path') ?: 'app';
        $extensions = explode(',', $this->option('extensions'));
        $strict = $this->option('strict');
        $report = $this->option('report');

        $this->info('🔍 Code Style Validator');
        $this->newLine();

        $startTime = microtime(true);
        $allViolations = [];

        if (File::isFile($path)) {
            $this->info("Validating file: {$path}");
            $violations = $codeStyleService->validateFile($path);
            $allViolations = array_merge($allViolations, $violations);
        } elseif (File::isDirectory($path)) {
            $this->info("Validating directory: {$path}");
            $violations = $this->validateDirectory($codeStyleService, $path, $extensions);
            $allViolations = array_merge($allViolations, $violations);
        } else {
            $this->error("Path does not exist: {$path}");

            return 1;
        }

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        $this->newLine();
        $this->displaySummary($allViolations, $executionTime);

        if ($report) {
            $this->generateReport($allViolations);
        }

        $exitCode = ($strict && ! empty($allViolations)) ? 1 : 0;

        if ($exitCode === 1) {
            $this->error('Code style validation failed!');
        } else {
            $this->info('✅ Code style validation passed!');
        }

        return $exitCode;
    }

    private function validateDirectory(CodeStyleService $codeStyleService, string $directory, array $extensions): array
    {
        $allViolations = [];
        $files = File::allFiles($directory);
        $processedFiles = 0;
        $filesWithViolations = 0;

        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();

        foreach ($files as $file) {
            $extension = $file->getExtension();

            if (in_array($extension, $extensions)) {
                $violations = $codeStyleService->validateFile($file->getPathname());
                $allViolations = array_merge($allViolations, $violations);

                if (! empty($violations)) {
                    $filesWithViolations++;
                }

                $processedFiles++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("📊 Processed {$processedFiles} files, found violations in {$filesWithViolations} files");

        return $allViolations;
    }

    private function displaySummary(array $allViolations, float $executionTime): void
    {
        $totalViolations = count($allViolations);
        $violationTypes = [];
        $violationsByFile = [];

        foreach ($allViolations as $violation) {
            $type = $violation['type'];
            $file = $violation['file'];

            $violationTypes[$type] = ($violationTypes[$type] ?? 0) + 1;
            $violationsByFile[$file] = ($violationsByFile[$file] ?? 0) + 1;
        }

        $this->info('📈 Summary:');
        $this->line("   Total violations found: {$totalViolations}");
        $this->line('   Files with violations: '.count($violationsByFile));
        $this->line("   Execution time: {$executionTime}s");

        if (! empty($violationTypes)) {
            $this->newLine();
            $this->info('📋 Violation types:');

            foreach ($violationTypes as $type => $count) {
                $color = $count > 10 ? 'red' : ($count > 5 ? 'yellow' : 'green');
                $this->line("   <fg={$color}>{$type}: {$count}</>");
            }
        }

        if (! empty($violationsByFile)) {
            $this->newLine();
            $this->info('📁 Files with most violations:');

            arsort($violationsByFile);
            $topFiles = array_slice($violationsByFile, 0, 10, true);

            foreach ($topFiles as $file => $count) {
                $relativePath = str_replace(base_path().'/', '', $file);
                $color = $count > 10 ? 'red' : ($count > 5 ? 'yellow' : 'green');
                $this->line("   <fg={$color}>{$relativePath}: {$count} violations</>");
            }
        }

        if ($totalViolations === 0) {
            $this->newLine();
            $this->info('🎉 All files are following code style guidelines!');
        }
    }

    private function generateReport(array $allViolations): void
    {
        $reportPath = storage_path('logs/code-style-validation-report.json');
        $reportData = [
            'timestamp' => now()->toISOString(),
            'total_violations' => count($allViolations),
            'violations_by_type' => [],
            'violations_by_file' => [],
            'all_violations' => $allViolations,
        ];

        foreach ($allViolations as $violation) {
            $type = $violation['type'];
            $file = $violation['file'];

            $reportData['violations_by_type'][$type] = ($reportData['violations_by_type'][$type] ?? 0) + 1;
            $reportData['violations_by_file'][$file] = ($reportData['violations_by_file'][$file] ?? 0) + 1;
        }

        File::put($reportPath, json_encode($reportData, JSON_PRETTY_PRINT));
        $this->info("📄 Detailed report saved to: {$reportPath}");
    }
}
