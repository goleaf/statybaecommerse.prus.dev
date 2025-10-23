<?php

declare(strict_types=1);

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\SQLiteConnector;

/**
 * SQLite connector that prepares filesystem paths ahead of connection attempts.
 */
final class GracefulSQLiteConnector extends SQLiteConnector
{
    /**
     * Ensure the SQLite database path exists before delegating to the parent resolver.
     */
    protected function parseDatabasePath(string $path): string
    {
        if ($this->isInMemoryPath($path)) {
            return $path;
        }

        if (! str_contains($path, '://')) {
            $this->ensureDatabaseFileExists($path);
        }

        return parent::parseDatabasePath($path);
    }

    /**
     * Determine whether the provided path references an in-memory SQLite database.
     */
    private function isInMemoryPath(string $path): bool
    {
        if ($path === ':memory:') {
            return true;
        }

        return str_contains($path, 'mode=memory');
    }

    /**
     * Create the database directory and touch the file if it does not exist.
     */
    private function ensureDatabaseFileExists(string $path): void
    {
        $absolutePath = $this->isAbsolutePath($path) ? $path : base_path($path);
        $directory = dirname($absolutePath);

        if ($directory !== '' && $directory !== '.' && ! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        if (! is_file($absolutePath)) {
            $handle = @fopen($absolutePath, 'c');

            if (is_resource($handle)) {
                @fclose($handle);
                @chmod($absolutePath, 0644);
            }
        }
    }

    /**
     * Basic absolute path detection for Unix and Windows environments.
     */
    private function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || (strlen($path) > 2 && ctype_alpha($path[0]) && $path[1] === ':' && ($path[2] === '\\' || $path[2] === '/'));
    }
}

