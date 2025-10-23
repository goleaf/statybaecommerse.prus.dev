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

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $connection = env('DB_CONNECTION', 'sqlite');
        $database = env('DB_DATABASE', ':memory:');

        // Make the connection configurable so contributors can point tests at a persistent SQLite file when needed.
        config()->set('database.default', $connection);

        if ($connection === 'sqlite') {
            // Respect the chosen SQLite database path (":memory:" by default) to avoid schema mismatches between processes.
            config()->set('database.connections.sqlite.database', $database);
        }

        // Disable Telescope and ensure it uses the same database connection during automated tests.
        config()->set('telescope.enabled', false);
        config()->set('telescope.storage.database.connection', $connection);
        config()->set('debugbar.enabled', false);

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
