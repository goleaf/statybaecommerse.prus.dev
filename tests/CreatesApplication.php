<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

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

        // Guarantee the fallback SQLite file exists so migrations never fail when the memory driver is unavailable.
        $sqliteFile = __DIR__ . '/../database/database.sqlite';

        if (! file_exists($sqliteFile)) {
            touch($sqliteFile);
        }

        // Force the database configuration to the SQLite file before the app boots.
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=' . $sqliteFile);
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $sqliteFile;
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = $sqliteFile;

        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Ensure tests always use in-memory SQLite to avoid file corruption issues
        $config = $app->make('config');
        $config->set('database.default', 'sqlite');
        $config->set('database.connections.sqlite.database', $sqliteFile);
        // Disable Telescope and force its connection to sqlite during tests to avoid MySQL usage
        $config->set('telescope.enabled', false);
        $config->set('telescope.storage.database.connection', 'sqlite');
        $config->set('debugbar.enabled', false);

        return $app;
    }
}
