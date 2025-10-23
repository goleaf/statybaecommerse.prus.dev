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
}
