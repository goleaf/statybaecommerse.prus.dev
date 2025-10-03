<?php declare(strict_types=1);

namespace Tests;

use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private bool $createdEnvFile = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists(base_path('.env'))) {
            file_put_contents(base_path('.env'), '');
            $this->createdEnvFile = true;
        } else {
            $this->createdEnvFile = false;
        }

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        // Ensure Telescope doesn't use MySQL during tests and avoid watchers overhead
        Config::set('telescope.enabled', false);
        Config::set('telescope.storage.database.connection', 'sqlite');
        $this->withoutMiddleware([
            \App\Http\Middleware\ZoneDetector::class,
            \App\Http\Middleware\SetLocale::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->createdEnvFile && file_exists(base_path('.env'))) {
            unlink(base_path('.env'));
        }

        parent::tearDown();
    }

    protected function resolveAdminPanel(): Panel
    {
        $panel = Filament::getPanel('admin');

        if (! $panel instanceof Panel) {
            self::fail('The admin panel must be registered for Filament tests.');
        }

        Filament::setCurrentPanel($panel);

        return $panel;
    }
}
