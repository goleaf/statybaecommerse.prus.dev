<?php

declare(strict_types=1);

namespace App\Support\Filesystem;

use Illuminate\Support\Facades\Artisan;

/**
 * Manages backup database creation and verification for testing environments.
 */
final class BackupDatabaseManager
{
    private bool $hasEnsuredBackup = false;

    public function __construct(
        private readonly FilesystemPermissions $permissions
    ) {}

    public function ensureBackupForPath(string $path): void
    {
        if (! app()->environment('testing') || $this->hasEnsuredBackup) {
            return;
        }

        if (! $this->canRunBackupCommands()) {
            return;
        }

        $this->runBackupCommands();
        $this->ensureBackupDatabaseExists($path);
    }

    public function ensureBackupDatabaseExists(string $path): void
    {
        $connectionName = config('backup.connection');
        if (! is_string($connectionName) || $connectionName === '') {
            return;
        }

        $connection = config("database.connections.{$connectionName}");
        if (! $this->isSqliteConnection($connection)) {
            return;
        }

        $databasePath = $connection['database'] ?? null;
        if (! $this->isValidDatabasePath($databasePath)) {
            return;
        }

        $absoluteDatabasePath = $this->resolveDatabasePath($databasePath);
        if (! $this->shouldCreateDatabase($path, $absoluteDatabasePath)) {
            return;
        }

        $this->createDatabaseFile($absoluteDatabasePath);
    }

    private function runBackupCommands(): void
    {
        $this->hasEnsuredBackup = true;

        Artisan::call('backup:prepare', [
            '--connection'   => config('backup.connection'),
            '--storage-path' => config('backup.storage_path'),
        ]);

        $verifyConfig = config('backup.verify');
        Artisan::call('backup:verify', [
            '--storage-path' => config('backup.storage_path'),
            '--working-path' => is_array($verifyConfig) ? ($verifyConfig['working_path'] ?? null) : null,
            '--connection'   => is_array($verifyConfig) ? ($verifyConfig['connection_name'] ?? null) : null,
        ]);
    }

    private function isSqliteConnection(?array $connection): bool
    {
        return is_array($connection) && ($connection['driver'] ?? null) === 'sqlite';
    }

    private function isValidDatabasePath(?string $databasePath): bool
    {
        return is_string($databasePath)
            && $databasePath !== ''
            && ! str_contains($databasePath, '://')
            && ! $this->isInMemoryDatabase($databasePath);
    }

    private function shouldCreateDatabase(string $path, string $absoluteDatabasePath): bool
    {
        $databaseDirectory = dirname($absoluteDatabasePath);
        $preparedDirectory = $this->normalisePath($path);
        $targetDirectory = $this->normalisePath($databaseDirectory);

        if ($preparedDirectory === '' || $targetDirectory === '') {
            return false;
        }

        $prefix = $preparedDirectory === DIRECTORY_SEPARATOR
            ? DIRECTORY_SEPARATOR
            : $preparedDirectory . DIRECTORY_SEPARATOR;

        return $targetDirectory === $preparedDirectory || str_starts_with($targetDirectory, $prefix);
    }

    private function createDatabaseFile(string $absoluteDatabasePath): void
    {
        $databaseDirectory = dirname($absoluteDatabasePath);

        if (! is_dir($databaseDirectory)) {
            @mkdir($databaseDirectory, $this->permissions->getDirectoryMode(), true);
        }

        if (! is_file($absoluteDatabasePath)) {
            $handle = @fopen($absoluteDatabasePath, 'c');
            if (is_resource($handle)) {
                @fclose($handle);
                @chmod($absoluteDatabasePath, $this->permissions->getFileMode());
            }
        }
    }

    private function resolveDatabasePath(string $path): string
    {
        if (str_contains($path, '://')) {
            return $path;
        }

        return $this->isAbsolutePath($path) ? $path : base_path($path);
    }

    private function normalisePath(string $path): string
    {
        if ($path === '') {
            return '';
        }

        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $resolved = realpath($path);

        if ($resolved !== false) {
            $path = $resolved;
        }

        if ($path === DIRECTORY_SEPARATOR) {
            return DIRECTORY_SEPARATOR;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            if (preg_match('/^[A-Za-z]:\\\\$/', $path) === 1) {
                return strtolower($path);
            }
            $path = rtrim($path, DIRECTORY_SEPARATOR);

            return $path === '' ? DIRECTORY_SEPARATOR : strtolower($path);
        }

        $path = rtrim($path, DIRECTORY_SEPARATOR);

        return $path === '' ? DIRECTORY_SEPARATOR : $path;
    }

    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            return preg_match('/^[A-Za-z]:\\\\/', $path) === 1;
        }

        return str_starts_with($path, DIRECTORY_SEPARATOR);
    }

    private function isInMemoryDatabase(string $databasePath): bool
    {
        return $databasePath === ':memory:'
            || str_contains($databasePath, ':memory:')
            || str_contains($databasePath, 'mode=memory');
    }

    private function canRunBackupCommands(): bool
    {
        $connection = config('backup.connection');
        if (! is_string($connection) || $connection === '') {
            return false;
        }

        $config = config("database.connections.{$connection}");
        if (! is_array($config)) {
            return false;
        }

        $driver = $config['driver'] ?? null;
        if ($driver === 'sqlite') {
            $databasePath = $config['database'] ?? null;
            if (! is_string($databasePath) || $databasePath === '') {
                return false;
            }

            if ($this->isInMemoryDatabase($databasePath) || str_contains($databasePath, '://')) {
                return true;
            }

            $absolutePath = $this->resolveDatabasePath($databasePath);

            return is_file($absolutePath);
        }

        return true;
    }
}
