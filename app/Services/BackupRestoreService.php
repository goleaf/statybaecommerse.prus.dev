<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Service to restore files from backup and handle compatibility issues
 */
class BackupRestoreService
{
    private VersionCompatibilityService $compatibilityService;

    private array $restoredFiles = [];

    private array $skippedFiles = [];

    private array $errors = [];

    public function __construct(VersionCompatibilityService $compatibilityService)
    {
        $this->compatibilityService = $compatibilityService;
    }

    /**
     * Restore files from the most recent backup
     */
    public function restoreFromLatestBackup(): array
    {
        $backupPath = $this->getLatestBackupPath();

        if (! $backupPath) {
            $this->errors[] = 'No backup directory found';

            return $this->getResults();
        }

        $filamentBackupPath = $backupPath . '/filament-backup';

        if (! is_dir($filamentBackupPath)) {
            $this->errors[] = 'Filament backup directory not found in: ' . $backupPath;

            return $this->getResults();
        }

        // Restore core files first
        $this->restoreAdminPanelProvider($filamentBackupPath);

        // Restore resources with compatibility fixes
        $this->restoreResourcesDirectory($filamentBackupPath . '/Resources');

        // Restore pages
        $this->restorePagesDirectory($filamentBackupPath . '/Pages');

        // Restore widgets
        $this->restoreWidgetsDirectory($filamentBackupPath . '/Widgets');

        return $this->getResults();
    }

    /**
     * Get the latest backup directory path
     */
    private function getLatestBackupPath(): ?string
    {
        $backupDir = base_path('backups');

        if (! is_dir($backupDir)) {
            return null;
        }

        $directories = collect(File::directories($backupDir))
            ->filter(fn ($dir) => str_contains(basename($dir), 'pre-downgrade'))
            ->sortByDesc(fn ($dir) => basename($dir))
            ->first();

        return $directories;
    }

    /**
     * Restore AdminPanelProvider
     */
    private function restoreAdminPanelProvider(string $backupPath): void
    {
        $sourceFile = $backupPath . '/AdminPanelProvider.php';
        $targetFile = app_path('Filament/AdminPanelProvider.php');

        if (file_exists($sourceFile)) {
            $content = file_get_contents($sourceFile);

            // Apply compatibility fixes
            $content = $this->compatibilityService->fixCompatibilityIssues($content);

            // Ensure directory exists
            File::ensureDirectoryExists(dirname($targetFile));

            file_put_contents($targetFile, $content);
            $this->restoredFiles[] = $targetFile;
        }
    }

    /**
     * Restore resources directory with compatibility fixes
     */
    private function restoreResourcesDirectory(string $backupResourcesPath): void
    {
        if (! is_dir($backupResourcesPath)) {
            return;
        }

        $targetResourcesPath = app_path('Filament/Resources');

        // Get all PHP files in the backup resources directory
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($backupResourcesPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace($backupResourcesPath, '', $file->getPathname());
                $targetPath = $targetResourcesPath . $relativePath;

                // Skip problematic resources for now
                if ($this->shouldSkipResource($file->getPathname())) {
                    $this->skippedFiles[] = $targetPath . ' (compatibility issues)';

                    continue;
                }

                $content = file_get_contents($file->getPathname());

                // Apply compatibility fixes
                $content = $this->compatibilityService->fixCompatibilityIssues($content);
                $content = $this->fixSpecificResourceIssues($content, $relativePath);

                // Ensure directory exists
                File::ensureDirectoryExists(dirname($targetPath));

                file_put_contents($targetPath, $content);
                $this->restoredFiles[] = $targetPath;
            }
        }
    }

    /**
     * Check if a resource should be skipped due to compatibility issues
     */
    private function shouldSkipResource(string $filePath): bool
    {
        $content = file_get_contents($filePath);

        // Skip resources that use packages not compatible with Filament 3.3
        $incompatiblePatterns = [
            'LaraZeus\SpatieTranslatable',
            'Hydrat\TableLayoutToggle',
            'Coolsam\Flatpickr',
            'Awcodes\FilamentBadgeableColumn',
            'Defstudio\FilamentSearchableInput',
        ];

        foreach ($incompatiblePatterns as $pattern) {
            if (str_contains($content, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fix specific resource compatibility issues
     */
    private function fixSpecificResourceIssues(string $content, string $relativePath): string
    {
        // Remove incompatible imports
        $incompatibleImports = [
            'use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable as SpatieTranslatableResource;',
            'use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;',
            'use Coolsam\Flatpickr\Forms\Components\Flatpickr;',
            'use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;',
        ];

        foreach ($incompatibleImports as $import) {
            $content = str_replace($import, '', $content);
        }

        // Remove incompatible trait usage
        $content = preg_replace('/use SpatieTranslatableResource;?\s*/', '', $content);
        $content = preg_replace('/use HasToggleableTable;?\s*/', '', $content);

        return $content;
    }

    /**
     * Restore pages directory
     */
    private function restorePagesDirectory(string $backupPagesPath): void
    {
        if (! is_dir($backupPagesPath)) {
            return;
        }

        $targetPagesPath = app_path('Filament/Pages');
        $this->copyDirectoryWithFixes($backupPagesPath, $targetPagesPath);
    }

    /**
     * Restore widgets directory
     */
    private function restoreWidgetsDirectory(string $backupWidgetsPath): void
    {
        if (! is_dir($backupWidgetsPath)) {
            return;
        }

        $targetWidgetsPath = app_path('Filament/Widgets');
        $this->copyDirectoryWithFixes($backupWidgetsPath, $targetWidgetsPath);
    }

    /**
     * Copy directory with compatibility fixes
     */
    private function copyDirectoryWithFixes(string $source, string $target): void
    {
        if (! is_dir($source)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $relativePath = str_replace($source, '', $file->getPathname());
                $targetPath = $target . $relativePath;

                $content = file_get_contents($file->getPathname());

                // Apply compatibility fixes
                $content = $this->compatibilityService->fixCompatibilityIssues($content);
                $content = $this->fixSpecificResourceIssues($content, $relativePath);

                // Ensure directory exists
                File::ensureDirectoryExists(dirname($targetPath));

                file_put_contents($targetPath, $content);
                $this->restoredFiles[] = $targetPath;
            }
        }
    }

    /**
     * Get restoration results
     */
    private function getResults(): array
    {
        return [
            'restored' => $this->restoredFiles,
            'skipped'  => $this->skippedFiles,
            'errors'   => $this->errors,
            'summary'  => [
                'restored_count' => count($this->restoredFiles),
                'skipped_count'  => count($this->skippedFiles),
                'error_count'    => count($this->errors),
            ],
        ];
    }
}
