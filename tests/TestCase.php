<?php

declare(strict_types=1);

namespace Tests;

use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private bool $createdEnvFile = false;

    private ?Panel $resolvedAdminPanel = null;

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

        // Ensure JSON translations from lang/*.json are available during tests
        Lang::addJsonPath(lang_path());
        Lang::addJsonPath(resource_path('lang'));
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
        if ($this->resolvedAdminPanel instanceof Panel) {
            Filament::setCurrentPanel(null);
            Filament::setServingStatus(false);
            $this->resolvedAdminPanel = null;
        }

        if ($this->createdEnvFile && file_exists(base_path('.env'))) {
            unlink(base_path('.env'));
        }

        parent::tearDown();
    }

    protected function resolveAdminPanel(): Panel
    {
        if ($this->resolvedAdminPanel instanceof Panel) {
            Filament::setCurrentPanel($this->resolvedAdminPanel);

            return $this->resolvedAdminPanel;
        }

        $panel = Filament::getPanel('admin');

        if (! $panel instanceof Panel) {
            self::fail('The admin panel must be registered for Filament tests.');
        }

        Filament::setCurrentPanel($panel);
        Filament::setServingStatus(true);

        $this->resolvedAdminPanel = $panel;

        return $panel;
    }
}
