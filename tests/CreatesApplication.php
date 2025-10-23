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

        // Ensure that artisan commands invoked during tests never think the application is in production.
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        $_SERVER['APP_ENV'] = 'testing';

        $sqlitePath = dirname(__DIR__).'/database/testing.sqlite';

        if (! file_exists($sqlitePath)) {
            // Provision a dedicated SQLite database file for the test run and clean it up afterwards.
            touch($sqlitePath);

            register_shutdown_function(static function () use ($sqlitePath): void {
                if (file_exists($sqlitePath)) {
                    unlink($sqlitePath);
                }
            });
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        // Force the resolved application environment to "testing" before the kernel boots.
        $app->detectEnvironment(static fn (): string => 'testing');

        $app->make(Kernel::class)->bootstrap();

        // Ensure tests always use the dedicated SQLite database to avoid MySQL usage
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $sqlitePath);
        // Disable Telescope and force its connection to sqlite during tests to avoid MySQL usage
        config()->set('telescope.enabled', false);
        config()->set('telescope.storage.database.connection', 'sqlite');
        config()->set('debugbar.enabled', false);

        // Align the resolved application environment with the testing expectations.
        config()->set('app.env', 'testing');

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
