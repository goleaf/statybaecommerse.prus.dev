<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class PHPDocCleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'docs:clean 
                            {--force : Force clean without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Clean existing PHPDoc documentation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧹 Cleaning PHPDoc documentation...');
        $this->newLine();

        $docsDir = base_path('docs');

        if (! File::exists($docsDir)) {
            $this->info('✅ No documentation directory found. Nothing to clean.');

            return Command::SUCCESS;
        }

        // Show what will be deleted
        $this->showDirectoryContents($docsDir);

        if (! $this->option('force')) {
            if (! $this->confirm('Are you sure you want to delete all documentation files?')) {
                $this->info('❌ Clean operation cancelled.');

                return Command::SUCCESS;
            }
        }

        // Delete the docs directory
        File::deleteDirectory($docsDir);

        $this->info('✅ Documentation cleaned successfully!');
        $this->line('📂 Removed: docs/');

        return Command::SUCCESS;
    }

    /**
     * Show directory contents before deletion.
     */
    private function showDirectoryContents(string $directory): void
    {
        $this->line('📁 Directory contents to be deleted:');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $fileCount = 0;
        $dirCount = 0;

        foreach ($iterator as $item) {
            $relativePath = str_replace($directory . '/', '', $item->getPathname());

            if ($item->isDir()) {
                $this->line("  📁 {$relativePath}/");
                $dirCount++;
            } else {
                $this->line("  📄 {$relativePath}");
                $fileCount++;
            }
        }

        $this->newLine();
        $this->line("📊 Summary: {$dirCount} directories, {$fileCount} files");
        $this->newLine();
    }
}
