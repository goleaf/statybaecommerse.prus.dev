<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\CodeStyleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;

final class CodeStyleWatchCommand extends Command
{
    protected $signature = 'code-style:watch 
                           {--path=app : Directory to watch}
                           {--extensions=php : Comma-separated list of file extensions}
                           {--interval=1 : Watch interval in seconds}';

    protected $description = 'Watch files for changes and auto-fix code style issues';

    private array $fileTimestamps = [];
    private CodeStyleService $codeStyleService;

    public function handle(CodeStyleService $codeStyleService): int
    {
        $this->codeStyleService = $codeStyleService;

        $path = $this->option('path');
        $extensions = explode(',', $this->option('extensions'));
        $interval = (float) $this->option('interval');

        if (!File::isDirectory($path)) {
            $this->error("Directory does not exist: {$path}");
            return 1;
        }

        $this->info("🔍 Watching directory: {$path}");
        $this->info('📁 File extensions: ' . implode(', ', $extensions));
        $this->info("⏱️  Watch interval: {$interval}s");
        $this->newLine();
        $this->info('Press Ctrl+C to stop watching...');
        $this->newLine();

        // Initialize file timestamps
        $this->initializeFileTimestamps($path, $extensions);

        // Set up file watching
        $this->watchFiles($path, $extensions, $interval);

        return 0;
    }

    private function initializeFileTimestamps(string $path, array $extensions): void
    {
        $files = File::allFiles($path);

        foreach ($files as $file) {
            if (in_array($file->getExtension(), $extensions)) {
                $this->fileTimestamps[$file->getPathname()] = $file->getMTime();
            }
        }

        $this->info('📊 Initialized timestamps for ' . count($this->fileTimestamps) . ' files');
    }

    private function watchFiles(string $path, array $extensions, float $interval): void
    {
        $loop = Loop::get();

        $timer = $loop->addPeriodicTimer($interval, function () use ($path, $extensions) {
            $this->checkForChanges($path, $extensions);
        });

        // Handle Ctrl+C
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGINT, function () use ($loop, $timer) {
                $this->info("\n🛑 Stopping file watcher...");
                $loop->cancelTimer($timer);
                $loop->stop();
            });
        }

        try {
            $loop->run();
        } catch (\Exception $e) {
            $this->error('Error in file watcher: ' . $e->getMessage());
        }
    }

    private function checkForChanges(string $path, array $extensions): void
    {
        $files = File::allFiles($path);
        $changedFiles = [];

        foreach ($files as $file) {
            if (!in_array($file->getExtension(), $extensions)) {
                continue;
            }

            $filePath = $file->getPathname();
            $currentTimestamp = $file->getMTime();
            $lastTimestamp = $this->fileTimestamps[$filePath] ?? 0;

            if ($currentTimestamp > $lastTimestamp) {
                $this->fileTimestamps[$filePath] = $currentTimestamp;
                $changedFiles[] = $filePath;
            }
        }

        foreach ($changedFiles as $filePath) {
            $this->processChangedFile($filePath);
        }
    }

    private function processChangedFile(string $filePath): void
    {
        $relativePath = str_replace(base_path() . '/', '', $filePath);

        // Check for violations
        $violations = $this->codeStyleService->validateFile($filePath);

        if (empty($violations)) {
            $this->line("✅ {$relativePath} - No issues found");
            return;
        }

        $this->warn("⚠️  {$relativePath} - Found " . count($violations) . ' issues');

        // Show violations
        foreach ($violations as $violation) {
            $this->line("   Line {$violation['line']}: {$violation['message']}");
        }

        // Auto-fix
        $fixes = $this->codeStyleService->fixFile($filePath);

        if (!empty($fixes)) {
            $this->info('🔧 Auto-fixed ' . count($fixes) . ' issues');
        }

        $this->newLine();
    }
}
