<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;

trait CreatesApplication
{
    /**
     * Track the lazily created SQLite database path so repeated calls reuse it.
     */
    private ?string $sqliteDatabasePath = null;

    /**
     * Remember whether the SQLite database file existed before the test run.
     */
    private bool $sqliteDatabasePreExisted = false;

    public function createApplication(): Application
    {
        $envPath = __DIR__ . '/../.env';

        if (! file_exists($envPath)) {
            file_put_contents($envPath, '');

            register_shutdown_function(static function () use ($envPath): void {
                if (file_exists($envPath)) {
                    unlink($envPath);
                }
            });
        }

        $sqlitePath = dirname(__DIR__) . '/database/testing.sqlite';

        if (! file_exists($sqlitePath)) {
            // Ensure the persistent SQLite file exists before the framework boots connections.
            touch($sqlitePath);
        }

        // Prime environment variables before the application boots so config reads prefer SQLite.
        foreach ([
            'DB_CONNECTION'           => 'sqlite',
            'DB_DATABASE'             => $sqlitePath,
            'TELESCOPE_DB_CONNECTION' => 'sqlite',
        ] as $key => $value) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Configure SQLite to use a real file so that migrations persist across connections.
        $databasePath = $this->resolveSqliteDatabasePath();
        if (! $this->sqliteDatabasePreExisted) {
            register_shutdown_function(static function () use ($databasePath): void {
                if (file_exists($databasePath)) {
                    unlink($databasePath);
                }
            });
        }
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $databasePath);
        Config::set('database.connections.sqlite.foreign_key_constraints', true);
        // Disable Telescope and Debugbar so tests do not attempt to talk to MySQL.
        Config::set('telescope.enabled', false);
        Config::set('telescope.storage.database.connection', 'sqlite');
        Config::set('activitylog.database_connection', 'sqlite');
        Config::set('debugbar.enabled', false);

        return $app;
    }

    /**
     * Resolve the SQLite database path, creating the file if it is missing.
     */
    protected function resolveSqliteDatabasePath(): string
    {
        if (is_string($this->sqliteDatabasePath) && $this->sqliteDatabasePath !== '') {
            return $this->sqliteDatabasePath;
        }

        $databasePath = dirname(__DIR__) . '/database/testing.sqlite';
        $this->sqliteDatabasePreExisted = file_exists($databasePath);

        if (! $this->sqliteDatabasePreExisted) {
            // Touch the file so SQLite has a persistent database to migrate against.
            touch($databasePath);
        }

        $this->sqliteDatabasePath = $databasePath;

        return $this->sqliteDatabasePath;
    }
}
