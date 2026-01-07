<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\VersionCompatibilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * Console command for managing Filament version compatibility transformations
 */
final class FilamentCompatibilityCommand extends Command
{
    protected $signature = 'filament:compatibility 
                           {action : Action to perform (transform|stats|clear-cache)}
                           {--path= : Path to file or directory to transform}
                           {--dry-run : Show what would be transformed without making changes}';

    protected $description = 'Manage Filament v4 to v3.3 compatibility transformations';

    public function __construct(
        private readonly VersionCompatibilityService $compatibilityService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'transform'   => $this->handleTransform(),
            'stats'       => $this->handleStats(),
            'clear-cache' => $this->handleClearCache(),
            default       => $this->handleInvalidAction($action),
        };
    }

    private function handleTransform(): int
    {
        $path = $this->option('path');
        $dryRun = $this->option('dry-run');

        if (! $path) {
            $this->error('Path option is required for transform action');

            return self::FAILURE;
        }

        if (! File::exists($path)) {
            $this->error("Path does not exist: {$path}");

            return self::FAILURE;
        }

        $this->info("Processing: {$path}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be modified');
        }

        try {
            if (File::isFile($path)) {
                return $this->transformFile($path, $dryRun);
            } else {
                return $this->transformDirectory($path, $dryRun);
            }
        } catch (Throwable $e) {
            $this->error("Transformation failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    private function transformFile(string $filePath, bool $dryRun): int
    {
        if ($dryRun) {
            $content = File::get($filePath);
            $result = $this->compatibilityService->transformContent($content);
        } else {
            $result = $this->compatibilityService->fixResourceFile($filePath);
        }

        if ($result->hasError()) {
            $this->error("Error processing file: {$result->getError()}");

            return self::FAILURE;
        }

        if ($result->wasTransformed()) {
            $this->info("✓ Transformed: {$filePath}");
            $this->line('  Applied transformations: ' . implode(', ', $result->getAppliedTransformations()));
        } else {
            $this->line("- No changes needed: {$filePath}");
        }

        return self::SUCCESS;
    }

    private function transformDirectory(string $directory, bool $dryRun): int
    {
        if ($dryRun) {
            $this->warn('Directory dry-run not fully implemented - use file-by-file dry-run instead');

            return self::FAILURE;
        }

        $results = $this->compatibilityService->fixAllResourcesInDirectory($directory);

        $this->info("Processed {$results->count()} files");

        foreach ($results as $result) {
            $this->line("✓ {$result['file']}");
        }

        return self::SUCCESS;
    }

    private function handleStats(): int
    {
        $stats = $this->compatibilityService->getTransformationStats();

        $this->info('Filament Compatibility Service Statistics');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Available Strategies', $stats['available_strategies']],
                ['Cache Prefix', $stats['cache_prefix']],
                ['Cache TTL (seconds)', $stats['cache_ttl']],
            ]
        );

        $this->info('Available Transformation Strategies:');
        foreach ($stats['strategies'] as $strategy) {
            $this->line("  - {$strategy}");
        }

        return self::SUCCESS;
    }

    private function handleClearCache(): int
    {
        $this->info('Clearing transformation cache...');

        $success = $this->compatibilityService->clearCache();

        if ($success) {
            $this->info('✓ Cache cleared successfully');

            return self::SUCCESS;
        } else {
            $this->error('✗ Failed to clear cache');

            return self::FAILURE;
        }
    }

    private function handleInvalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");
        $this->info('Available actions: transform, stats, clear-cache');

        return self::FAILURE;
    }
}
