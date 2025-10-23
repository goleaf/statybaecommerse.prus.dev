<?php

declare(strict_types=1);

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\SQLiteConnector;

final class GracefulSQLiteConnector extends SQLiteConnector
{
    /**
     * Ensure the on-disk SQLite database exists before delegating to Laravel's
     * connector so backup and test connections initialise consistently.
     */
    protected function parseDatabasePath(string $path): string
    {
        if ($this->isInMemoryPath($path)) {
            return $path;
        }

        $database = $path;
        $directory = dirname($path);

        if ($directory !== '' && ! is_dir($directory)) {
            // Create the parent directory when tests clean the storage path between runs.
            @mkdir($directory, 0o755, true);
        }

        if (! file_exists($path)) {
            // Touch the database file so SQLite connectors never fail on first use.
            $handle = @fopen($path, 'cb');

            if ($handle !== false) {
                fclose($handle);
                @chmod($path, 0o666);
            }
        }

        return parent::parseDatabasePath($database);
    }

    /**
     * Determine whether the configured SQLite path references an in-memory database.
     */
    private function isInMemoryPath(string $path): bool
    {
        return $path === ':memory:'
            || str_contains($path, '?mode=memory')
            || str_contains($path, '&mode=memory');
    }
}
