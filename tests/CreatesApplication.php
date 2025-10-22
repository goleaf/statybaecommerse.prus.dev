<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    public function createApplication(): Application
    {
        $envPath = __DIR__.'/../.env';

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
}
