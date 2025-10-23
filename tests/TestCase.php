<?php

declare(strict_types=1);

namespace Tests;

use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Contracts\Translation\Loader as TranslationLoader;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private bool $createdEnvFile = false;

    private string $sqliteDatabasePath;

    private ?Panel $resolvedAdminPanel = null;

    protected function setUp(): void
    {
        // Resolve the shared SQLite database location before the application boots so the
        // parent setup sequence works with the same persistent datastore prepared by
        // Tests\Support\TestingDatabase.
        $this->sqliteDatabasePath = TestingDatabase::path();

        parent::setUp();

        if (! file_exists(base_path('.env'))) {
            file_put_contents(base_path('.env'), '');
            $this->createdEnvFile = true;
        } else {
            $this->createdEnvFile = false;
        }

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', ':memory:');
        Config::set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        // Ensure Telescope doesn't use MySQL during tests and avoid watchers overhead
        Config::set('telescope.enabled', false);
        Config::set('telescope.storage.database.connection', 'sqlite');
        $this->refreshTranslationLoader();
        app()->instance('request', Request::create('/'));
        $this->withoutMiddleware([
            \App\Http\Middleware\ZoneDetector::class,
            \App\Http\Middleware\SetLocale::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $this->resolveAdminPanel();
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

    /**
     * Ensure JSON translation files remain available during tests.
     */
    private function refreshTranslationLoader(): void
    {
        /** @var TranslatorContract $translator */
        $translator = app('translator');

        if (method_exists($translator, 'getLoader')) {
            $loader = $translator->getLoader();

            if ($loader instanceof TranslationLoader && method_exists($loader, 'addJsonPath')) {
                $loader->addJsonPath(lang_path());
            }
        }

        if (method_exists($translator, 'setLoaded')) {
            $translator->setLoaded([]);
        } elseif (method_exists($translator, 'flushLoadedTranslations')) {
            $translator->flushLoadedTranslations();
        }

        $defaultLocale = (string) config('app.locale', 'en');
        $fallbackLocale = (string) config('app.fallback_locale', 'en');

        app()->setLocale($defaultLocale);
        $translator->setLocale($defaultLocale);
        $translator->setFallback($fallbackLocale);

        if (method_exists($translator, 'load')) {
            foreach ($this->supportedLocalesForTesting() as $locale) {
                $translator->load('*', 'json', $locale);
            }
        }
    }

    /**
     * Resolve all locales that should be considered during tests.
     *
     * @return array<int, string>
     */
    private function supportedLocalesForTesting(): array
    {
        $configured = config('shared.localization.supported_locales', []);
        $appConfigured = array_filter(array_map('trim', explode(',', (string) config('app.supported_locales', ''))));
        $fallback = (string) config('app.fallback_locale', '');

        $locales = array_filter(array_merge($configured, $appConfigured, [$fallback]));

        return array_values(array_unique($locales));
    }

    protected function assertStringContains(string $needle, string $haystack, string $message = ''): void
    {
        $this->assertStringContainsString($needle, $haystack, $message);
    }
}
