<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        config()->set('app.env', 'testing');
        config()->set('app.debug', false);

        if (! config('app.key')) {
            config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        }

        // Default to SQLite in-memory for unit tests so no destructive migrations run.
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);

        app()->setLocale(config('app.locale', 'en'));

        return $app;
    }
}
