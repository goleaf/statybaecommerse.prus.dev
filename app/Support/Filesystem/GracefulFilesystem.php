<?php

declare(strict_types=1);

namespace App\Support\Filesystem;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Enhanced filesystem with graceful directory handling for Laravel 12.
 *
 * Provides automatic directory creation, memory management for recently created
 * directories, backup database support, and Laravel 12 compatibility for the
 * directories() method signature changes.
 *
 * @example
 * ```php
 * $fs = new GracefulFilesystem();
 * $directories = $fs->directories('/path', true); // Recursive
 * $directories = $fs->directories('/path', 2);    // Depth limit
 * ```
 */
final class GracefulFilesystem extends Filesystem
{
    private readonly DirectoryMemoryManager $memoryManager;

    private readonly BackupDatabaseManager $backupManager;

    private readonly DirectoryScanner $scanner;

    private readonly FilesystemPermissions $permissions;

    public function __construct(
        ?FilesystemPermissions $permissions = null,
        ?DirectoryMemoryManager $memoryManager = null,
        ?BackupDatabaseManager $backupManager = null,
        ?DirectoryScanner $scanner = null
    ) {
        $this->permissions = $permissions ?? FilesystemPermissions::default();
        $this->memoryManager = $memoryManager ?? new DirectoryMemoryManager;
        $this->backupManager = $backupManager ?? new BackupDatabaseManager($this->permissions);
        // Use a base filesystem instance to avoid recursive calls back into this class.
        $this->scanner = $scanner ?? new DirectoryScanner(new Filesystem, $this->memoryManager);
    }

    /**
     * Remember a directory that was just created so subsequent listings see it immediately.
     *
     * @param string $directory The directory path to remember
     *
     * @throws InvalidArgumentException When directory path is invalid
     */
    public static function remember(string $directory): void
    {
        // Maintain backward compatibility for static calls
        $instance = app(self::class);
        $instance->rememberDirectory($directory);
    }

    /**
     * Remember a directory that was just created so subsequent listings see it immediately.
     *
     * @param string $directory The directory path to remember
     *
     * @throws InvalidArgumentException When directory path is invalid
     */
    public function rememberDirectory(string $directory): void
    {
        $this->validateDirectoryPath($directory);
        $this->memoryManager->remember($directory);
    }

    /**
     * Get all directories within a directory with Laravel 12 compatibility.
     *
     * Automatically creates the target directory if it doesn't exist and handles
     * the Laravel 12 signature change from boolean to int depth parameter.
     *
     * @param  string             $directory The directory to inspect
     * @param  bool|int           $recursive Whether to include nested directories or depth limit
     *                                       - true: unlimited depth (-1)
     *                                       - false: no recursion (0)
     *                                       - int: specific depth limit
     * @return array<int, string> Array of directory paths
     *
     * @throws InvalidArgumentException When directory path is invalid
     * @throws RuntimeException         When filesystem operation fails
     */
    public function directories($directory, $recursive = false): array
    {
        try {
            $this->validateDirectoryPath($directory);
            $this->ensureDirectoryExistsForScanning($directory);

            $depth = $this->normalizeDepthParameter($recursive);
            $directories = $this->scanner->scanDirectories($directory, $depth);

            // Handle backup preparation in testing environment
            if (empty($directories) && app()->environment('testing')) {
                $this->handleBackupPreparation($directory, $depth);
                $directories = $this->scanner->scanDirectories($directory, $depth);
            }

            $this->logDirectoryOperation($directory, $directories, $recursive);

            return $directories;

        } catch (Throwable $e) {
            Log::error('Filesystem directory scan failed', [
                'directory' => $directory,
                'recursive' => $recursive,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            throw new RuntimeException(
                "Failed to scan directory '{$directory}': {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Convert recursive parameter to depth for Laravel 12 compatibility.
     *
     * Laravel 12 changed the directories() method signature to accept depth
     * instead of boolean recursive parameter.
     *
     * @param  bool|int $recursive The recursive parameter
     * @return int      The normalized depth value
     */
    private function normalizeDepthParameter(bool|int $recursive): int
    {
        return match ($recursive) {
            true    => -1,    // Unlimited depth
            false   => 0,    // No recursion
            default => $recursive, // Pass through integer values
        };
    }

    /**
     * Ensure directory exists before scanning with proper error handling.
     *
     * @param string $directory The directory path to ensure exists
     *
     * @throws RuntimeException When directory creation fails
     */
    private function ensureDirectoryExistsForScanning(string $directory): void
    {
        if ($directory === '' || $this->isDirectory($directory)) {
            return;
        }

        try {
            // Lazily create the directory to prevent race conditions in tests
            $this->makeDirectory($directory, $this->permissions->getDirectoryMode(), true);

            Log::debug('Created directory for scanning', [
                'directory' => $directory,
                'mode'      => decoct($this->permissions->getDirectoryMode()),
            ]);

        } catch (Throwable $e) {
            throw new RuntimeException(
                "Failed to create directory for scanning '{$directory}': {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Handle backup preparation for empty directories in testing environment.
     *
     * @param string $directory The directory being scanned
     * @param int    $depth     The scan depth
     */
    private function handleBackupPreparation(string $directory, int $depth): void
    {
        try {
            $this->backupManager->ensureBackupForPath($directory);
            clearstatcache(); // Clear filesystem cache after backup operations

            Log::debug('Backup preparation completed', [
                'directory' => $directory,
                'depth'     => $depth,
            ]);

        } catch (Throwable $e) {
            Log::warning('Backup preparation failed', [
                'directory' => $directory,
                'error'     => $e->getMessage(),
            ]);
            // Don't throw - backup preparation is not critical for directory scanning
        }
    }

    /**
     * Log directory operation results for debugging and monitoring.
     *
     * @param string             $directory   The directory that was scanned
     * @param array<int, string> $directories The found directories
     * @param bool|int           $recursive   The original recursive parameter
     */
    private function logDirectoryOperation(string $directory, array $directories, bool|int $recursive): void
    {
        Log::info('filesystem.directories', [
            'directory'        => $directory,
            'count'            => count($directories),
            'recursive'        => $recursive,
            'remembered_total' => $this->memoryManager->count(),
            'execution_time'   => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true)),
        ]);
    }

    /**
     * Create a directory while remembering it for subsequent lookups.
     *
     * @param  string   $path      The directory path to create
     * @param  int|null $mode      The directory permissions mode
     * @param  bool     $recursive Whether to create parent directories
     * @param  bool     $force     Whether to force creation
     * @return bool     True if directory was created or already exists
     *
     * @throws RuntimeException When directory creation fails
     */
    public function makeDirectory($path, $mode = null, $recursive = false, $force = false): bool
    {
        try {
            $this->validateDirectoryPath($path);

            $mode = $mode ?? $this->permissions->getDirectoryMode();
            $created = parent::makeDirectory($path, $mode, $recursive, $force);

            if ($created) {
                $this->memoryManager->remember($path);
                $this->backupManager->ensureBackupDatabaseExists($path);

                Log::debug('Directory created and remembered', [
                    'path'      => $path,
                    'mode'      => decoct($mode),
                    'recursive' => $recursive,
                ]);
            }

            return $created;

        } catch (Throwable $e) {
            Log::error('Directory creation failed', [
                'path'  => $path,
                'mode'  => $mode,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                "Failed to create directory '{$path}': {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Ensure a directory exists, creating it if necessary.
     *
     * @param string   $path      The directory path to ensure exists
     * @param int|null $mode      The directory permissions mode
     * @param bool     $recursive Whether to create parent directories
     *
     * @throws RuntimeException When directory creation fails
     */
    public function ensureDirectoryExists($path, $mode = null, $recursive = true): void
    {
        try {
            $this->validateDirectoryPath($path);

            $mode = $mode ?? $this->permissions->getDirectoryMode();
            $alreadyExists = $this->isDirectory($path);

            parent::ensureDirectoryExists($path, $mode, $recursive);

            if (! $alreadyExists) {
                $this->memoryManager->remember($path);
                $this->backupManager->ensureBackupDatabaseExists($path);

                Log::debug('Directory ensured and remembered', [
                    'path' => $path,
                    'mode' => decoct($mode),
                ]);
            }

        } catch (Throwable $e) {
            Log::error('Directory ensure failed', [
                'path'  => $path,
                'mode'  => $mode,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                "Failed to ensure directory exists '{$path}': {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Get memory manager for testing and debugging purposes.
     *
     * @return DirectoryMemoryManager The memory manager instance
     */
    public function getMemoryManager(): DirectoryMemoryManager
    {
        return $this->memoryManager;
    }

    /**
     * Get backup manager for testing and debugging purposes.
     *
     * @return BackupDatabaseManager The backup manager instance
     */
    public function getBackupManager(): BackupDatabaseManager
    {
        return $this->backupManager;
    }

    /**
     * Clear remembered directories (useful for testing and memory management).
     */
    public function clearMemory(): void
    {
        $this->memoryManager->clear();

        Log::debug('Filesystem memory cleared', [
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Validate directory path to prevent security issues.
     *
     * @param string $path The directory path to validate
     *
     * @throws InvalidArgumentException When path is invalid or potentially dangerous
     */
    private function validateDirectoryPath(string $path): void
    {
        if ($path === '') {
            throw new InvalidArgumentException('Directory path cannot be empty');
        }

        // Prevent directory traversal attacks
        if (str_contains($path, '..')) {
            throw new InvalidArgumentException('Directory path cannot contain ".." segments');
        }

        // Prevent null byte injection
        if (str_contains($path, "\0")) {
            throw new InvalidArgumentException('Directory path cannot contain null bytes');
        }

        // Check for extremely long paths that might cause issues
        if (strlen($path) > 4096) {
            throw new InvalidArgumentException('Directory path is too long (max 4096 characters)');
        }
    }
}
