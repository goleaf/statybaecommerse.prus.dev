<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Tests\Support\TestingDatabase;

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

        $existingToken = $_SERVER['TEST_TOKEN']
            ?? $_ENV['TEST_TOKEN']
            ?? getenv('TEST_TOKEN');

        if (! is_string($existingToken) || $existingToken === '') {
            $token = 'pid_' . getmypid();
            putenv('TEST_TOKEN=' . $token);
            $_ENV['TEST_TOKEN'] = $token;
            $_SERVER['TEST_TOKEN'] = $token;
        }

        // Avoid forcing a per-process SQLite path so the test suite can reuse
        // a single on-disk database across runs. This keeps migrations stable
        // and avoids stray worker-specific files during local execution.
        // If TEST_TOKEN is already provided by the environment (e.g. parallel
        // runners), it will be respected by Tests\Support\TestingDatabase::path().

        // Prime environment variables before Laravel boots so the framework resolves
        // the correct SQLite connection on its initial pass through configuration.
        $databasePath = TestingDatabase::path();
        TestingDatabase::ensureExists();

        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE='.$databasePath);
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $databasePath;
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = $databasePath;

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Align Laravel's runtime configuration and run migrations once for the
        // shared SQLite datastore backing the full test suite.
        TestingDatabase::configure($app);
        TestingDatabase::migrate();

        // Disable heavy debugging services and ensure Telescope persists data to the
        // same SQLite connection so schema assertions operate on a single database.
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

        $databasePath = TestingDatabase::path();
        $this->sqliteDatabasePreExisted = file_exists($databasePath);

        if (! $this->sqliteDatabasePreExisted) {
            TestingDatabase::ensureExists();
        }

        $this->sqliteDatabasePath = $databasePath;

        return $this->sqliteDatabasePath;
    }
}
