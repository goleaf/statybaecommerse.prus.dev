<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Tests\Support\TestingDatabase;

trait CreatesApplication
{
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

        $config = $app->make('config');

        // Force the application to rely on SQLite for all database interactions during tests.
        $config->set('database.default', 'sqlite');
        $config->set('database.connections.sqlite.database', $sqlitePath);
        // Disable Telescope and debugbar integrations that expect MySQL connections.
        $config->set('telescope.enabled', false);
        $config->set('telescope.storage.database.connection', 'sqlite');
        $config->set('debugbar.enabled', false);

        return $app;
    }
}
