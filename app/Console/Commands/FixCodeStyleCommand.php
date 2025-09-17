<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CodeStyleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class FixCodeStyleCommand extends Command
{
    protected $signature = 'code-style:fix 
                           {--path= : Specific file or directory to fix}
                           {--extensions=php : Comma-separated list of file extensions}
                           {--dry-run : Show what would be fixed without making changes}
                           {--report : Generate a detailed report}';

    protected $description = 'Fix code style issues in PHP files';

    public function handle(CodeStyleService $codeStyleService): int
    {
        $path = $this->option('path') ?: 'app';
        $extensions = explode(',', $this->option('extensions'));
        $dryRun = $this->option('dry-run');
        $report = $this->option('report');

        $this->info('🔧 Code Style Fixer');
        $this->newLine();

        if ($dryRun) {
            $this->warn('Running in dry-run mode - no files will be modified');
            $this->newLine();
        }

        $startTime = microtime(true);
        $allFixes = [];

        if (File::isFile($path)) {
            $this->info("Processing file: {$path}");
            $fixes = $this->processFile($codeStyleService, $path, $dryRun);
            $allFixes = array_merge($allFixes, $fixes);
        } elseif (File::isDirectory($path)) {
            $this->info("Processing directory: {$path}");
            $fixes = $this->processDirectory($codeStyleService, $path, $extensions, $dryRun);
            $allFixes = array_merge($allFixes, $fixes);
        } else {
            $this->error("Path does not exist: {$path}");

            return 1;
        }

        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        $this->newLine();
        $this->displaySummary($allFixes, $executionTime);

        if ($report) {
            $this->generateReport($allFixes);
        }

        return 0;
    }

    private function processFile(CodeStyleService $codeStyleService, string $filePath, bool $dryRun): array
    {
        $fixes = $codeStyleService->validateFile($filePath);

        if (empty($fixes)) {
            $this->line("✅ {$filePath} - No issues found");

            return [];
        }

        $this->warn("⚠️  {$filePath} - Found ".count($fixes).' issues:');

        foreach ($fixes as $fix) {
            $this->line("   Line {$fix['line']}: {$fix['message']}");
        }

        if (! $dryRun) {
            $appliedFixes = $codeStyleService->fixFile($filePath);
            if (! empty($appliedFixes)) {
                $this->info('🔧 Applied '.count($appliedFixes).' fixes');
            }
        }

        return $fixes;
    }

    private function processDirectory(CodeStyleService $codeStyleService, string $directory, array $extensions, bool $dryRun): array
    {
        $allFixes = [];
        $files = File::allFiles($directory);
        $processedFiles = 0;
        $filesWithIssues = 0;

        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();

        foreach ($files as $file) {
            $extension = $file->getExtension();

            if (in_array($extension, $extensions)) {
                $fixes = $this->processFile($codeStyleService, $file->getPathname(), $dryRun);
                $allFixes = array_merge($allFixes, $fixes);

                if (! empty($fixes)) {
                    $filesWithIssues++;
                }

                $processedFiles++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("📊 Processed {$processedFiles} files, found issues in {$filesWithIssues} files");

        return $allFixes;
    }

    private function displaySummary(array $allFixes, float $executionTime): void
    {
        $totalIssues = count($allFixes);
        $issueTypes = [];

        foreach ($allFixes as $fix) {
            $type = $fix['type'];
            $issueTypes[$type] = ($issueTypes[$type] ?? 0) + 1;
        }

        $this->info('📈 Summary:');
        $this->line("   Total issues found: {$totalIssues}");
        $this->line("   Execution time: {$executionTime}s");

        if (! empty($issueTypes)) {
            $this->newLine();
            $this->info('📋 Issue types:');

            foreach ($issueTypes as $type => $count) {
                $this->line("   {$type}: {$count}");
            }
        }

        if ($totalIssues === 0) {
            $this->newLine();
            $this->info('🎉 All files are following code style guidelines!');
        }
    }

    private function generateReport(array $allFixes): void
    {
        $reportPath = storage_path('logs/code-style-report.json');
        $reportData = [
            'timestamp' => now()->toISOString(),
            'total_issues' => count($allFixes),
            'issues_by_type' => [],
            'issues_by_file' => [],
            'all_issues' => $allFixes,
        ];

        foreach ($allFixes as $fix) {
            $type = $fix['type'];
            $file = $fix['file'];

            $reportData['issues_by_type'][$type] = ($reportData['issues_by_type'][$type] ?? 0) + 1;
            $reportData['issues_by_file'][$file] = ($reportData['issues_by_file'][$file] ?? 0) + 1;
        }

        File::put($reportPath, json_encode($reportData, JSON_PRETTY_PRINT));
        $this->info("📄 Detailed report saved to: {$reportPath}");
    }
}
