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

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Ensure tests always use in-memory SQLite to avoid file corruption issues
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        // Disable Telescope and force its connection to sqlite during tests to avoid MySQL usage
        config()->set('telescope.enabled', false);
        config()->set('telescope.storage.database.connection', 'sqlite');
        config()->set('debugbar.enabled', false);

        return $app;
    }
}
