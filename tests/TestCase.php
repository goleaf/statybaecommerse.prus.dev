<?php

declare(strict_types=1);

namespace Tests;

use App\Support\Cache\TagAwareCache;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Contracts\Translation\Loader as TranslationLoader;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private bool $createdEnvFile = false;

    private ?string $sqliteDatabasePath = null;

    private ?Panel $resolvedAdminPanel = null;

    protected function setUp(): void
    {
        // Calculate and prepare the SQLite database path before booting the application so the
        // framework picks up the correct connection details during the refresh cycle.
        $this->sqliteDatabasePath = dirname(__DIR__).'/database/testing.sqlite';
        if (! file_exists($this->sqliteDatabasePath)) {
            touch($this->sqliteDatabasePath);
        }

        // Update environment variables prior to the parent setup call because Laravel resolves
        // database configuration while bootstrapping the application instance.
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE='.$this->sqliteDatabasePath);
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $this->sqliteDatabasePath;
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = $this->sqliteDatabasePath;

        parent::setUp();

        if (! file_exists(base_path('.env'))) {
            file_put_contents(base_path('.env'), '');
            $this->createdEnvFile = true;
        } else {
            $this->createdEnvFile = false;
        }

        $sqlitePath = database_path('testing.sqlite');

        if (! file_exists($sqlitePath)) {
            // Create a dedicated SQLite database file so migrations persist within the test lifecycle.
            touch($sqlitePath);
        }

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $sqlitePath);
        Config::set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        // Ensure Telescope doesn't use MySQL during tests and avoid watchers overhead
        Config::set('telescope.enabled', false);
        Config::set('telescope.storage.database.connection', 'sqlite');

        if (! Schema::hasTable('users')) {
            // Guarantee that the core migrations are available before factories interact with the database, especially when
            // running focused suites where RefreshDatabase may not have warmed the in-memory sqlite schema yet.
            Artisan::call('migrate');
        }
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

        TagAwareCache::restore();

        parent::tearDown();

        // Clean up the temporary SQLite database file after each test run to prevent stale
        // state from impacting subsequent tests or developers running the suite locally.
        if ($this->sqliteDatabasePath !== null && file_exists($this->sqliteDatabasePath)) {
            unlink($this->sqliteDatabasePath);
            $this->sqliteDatabasePath = null;
        }
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
                // Ensure base lang/ JSON files remain available when tests rebuild translators.
                $loader->addJsonPath(lang_path());
                // Also expose resources/lang JSON directories so admin commerce labels resolve.
                $loader->addJsonPath(resource_path('lang'));
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

    /**
     * Force migrate:fresh to target the SQLite testing database.
     */
    protected function migrateFreshUsing()
    {
        $parameters = [
            '--database'   => 'sqlite',
            '--drop-views' => property_exists($this, 'dropViews') ? $this->dropViews : false,
            '--drop-types' => property_exists($this, 'dropTypes') ? $this->dropTypes : false,
        ];

        if (property_exists($this, 'seeder') && $this->seeder) {
            $parameters['--seeder'] = $this->seeder;
        } else {
            $parameters['--seed'] = property_exists($this, 'seed') ? $this->seed : false;
        }

        return $parameters;
    }
}
