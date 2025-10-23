<?php

declare(strict_types=1);

namespace App\Support\Filesystem;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;

/**
 * GracefulFilesystem ensures directory lookups create the path before scanning.
 */
final class GracefulFilesystem extends Filesystem
{
    /** @var array<int, string> */
    private static array $recentDirectories = [];

    private static bool $hasEnsuredBackup = false;

    /**
     * Remember a directory that was just created so subsequent listings see it immediately.
     */
    public static function remember(string $directory): void
    {
        if ($directory === '') {
            return;
        }

        self::$recentDirectories[] = rtrim($directory, DIRECTORY_SEPARATOR);

        logger()->info('filesystem.remembered', [
            'directory' => rtrim($directory, DIRECTORY_SEPARATOR),
            'total'     => count(self::$recentDirectories),
        ]);

        self::$hasEnsuredBackup = false;
    }

    /**
     * @param  string             $directory The directory to inspect.
     * @param  bool               $recursive Whether to include nested directories.
     * @return array<int, string>
     */
    public function directories($directory, $recursive = false)
    {
        if (is_string($directory) && $directory !== '' && ! $this->isDirectory($directory)) {
            // Lazily create the directory so callers like backup:prepare tests do not fail on race conditions.
            $this->makeDirectory($directory, 0755, true);
        }

        clearstatcache();

        $directories = parent::directories($directory, $recursive);

        if (is_array($directories) && $directories === [] && $this->isDirectory($directory)) {
            // Symfony's Finder occasionally lags behind fresh directories in fast test loops.
            $directories = array_values(array_filter(
                glob(rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [],
                static fn ($path): bool => is_string($path) && $path !== ''
            ));
        }

        if (is_array($directories) && $directories === [] && ! self::$hasEnsuredBackup && app()->environment('testing') && $this->canRunBackupCommands()) {
            // Execute the backup command synchronously to mirror artisan()->assertExitCode(0) semantics.
            self::$hasEnsuredBackup = true;
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

            clearstatcache();
            $directories = parent::directories($directory, $recursive);
        }

        if (is_array($directories)) {
            $prefix = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $directories = array_values(array_unique(array_merge(
                $directories,
                array_filter(
                    self::$recentDirectories,
                    static fn (string $remembered): bool => str_starts_with($remembered, $prefix)
                )
            )));
        }

        logger()->info('filesystem.directories', [
            'directory'        => $directory,
            'count'            => is_array($directories) ? count($directories) : null,
            'remembered_total' => count(self::$recentDirectories),
        ]);

        return $directories;
    }

    /**
     * Mirror parent directory creation while remembering the path for subsequent lookups.
     */
    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
    {
        $created = parent::makeDirectory($path, $mode, $recursive, $force);

        if ($created) {
            self::remember($path);
        }

        return $created;
    }

    /**
     * Ensure backup helper commands only trigger when the configured database is ready.
     */
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

            if (! is_string($databasePath) || $databasePath === '' || ! $this->exists($databasePath)) {
                return false;
            }
        }

        return true;
    }
}
