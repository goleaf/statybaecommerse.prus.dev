<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PHPDocServeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'docs:serve 
                            {--port=8080 : Port to serve documentation on}
                            {--host=localhost : Host to serve documentation on}';

    /**
     * The console command description.
     */
    protected $description = 'Serve PHPDoc documentation locally';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $port = $this->option('port');
        $host = $this->option('host');

        $htmlDir = base_path('docs/html');

        if (! File::isDirectory($htmlDir)) {
            $this->error('❌ Documentation not found. Please generate it first:');
            $this->line('   php artisan docs:generate');

            return Command::FAILURE;
        }

        // Check if port is available
        if (! $this->isPortAvailable($host, (int) $port)) {
            $this->error("❌ Port {$port} is already in use. Please choose a different port.");

            return Command::FAILURE;
        }

        $this->info('🌐 Starting PHPDoc documentation server...');
        $this->newLine();

        $this->line("📂 Serving from: {$htmlDir}");
        $this->line("🔗 URL: http://{$host}:{$port}");
        $this->line('⏹️  Press Ctrl+C to stop');
        $this->newLine();

        // Show some documentation stats
        $this->showDocumentationStats($htmlDir);

        $this->newLine();
        $this->info('🚀 Server starting...');
        $this->newLine();

        // Start the server
        $command = 'cd '.escapeshellarg($htmlDir)." && php -S {$host}:{$port}";

        // Use passthru to show server output
        passthru($command);

        return Command::SUCCESS;
    }

    /**
     * Check if port is available.
     */
    private function isPortAvailable(string $host, int $port): bool
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, 1);

        if ($connection) {
            fclose($connection);

            return false; // Port is in use
        }

        return true; // Port is available
    }

    /**
     * Show documentation statistics.
     */
    private function showDocumentationStats(string $htmlDir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($htmlDir)
        );

        $fileCount = 0;
        $totalSize = 0;

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $fileCount++;
                $totalSize += $file->getSize();
            }
        }

        $this->line('📊 Documentation Statistics:');
        $this->line("  • Files: {$fileCount}");
        $this->line('  • Size: '.$this->formatBytes($totalSize));
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
