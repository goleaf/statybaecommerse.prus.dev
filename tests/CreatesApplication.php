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

        // Use a dedicated on-disk SQLite database so migrations persist across connections.
        $testingDatabasePath = database_path('testing.sqlite');

        if (! file_exists($testingDatabasePath)) {
            // Create the database file if it has not been initialised yet.
            touch($testingDatabasePath);
        }

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $testingDatabasePath);
        // Disable Telescope and force its connection to sqlite during tests to avoid MySQL usage
        config()->set('telescope.enabled', false);
        config()->set('telescope.storage.database.connection', 'sqlite');
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
