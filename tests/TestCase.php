<?php

declare(strict_types=1);

namespace Tests;

use App\Support\Cache\TagAwareCache;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Contracts\Translation\Loader as TranslationLoader;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private bool $createdEnvFile = false;

    private ?string $sqliteDatabasePath = null;

    private ?Panel $resolvedAdminPanel = null;

    /**
     * Track the absolute path to the SQLite database that powers integration tests.
     */
    private string $sqliteDatabasePath = '';

    protected function setUp(): void
    {
        // Resolve the SQLite database path before the application boots so migrations
        // can target a persistent file instead of the default in-memory database that
        // loses tables between separate PDO connections.
        $this->sqliteDatabasePath = dirname(__DIR__) . '/database/testing.sqlite';

        if (! file_exists($this->sqliteDatabasePath)) {
            // Create the SQLite file eagerly to avoid runtime race conditions when the
            // framework attempts to run the first migration during the boot cycle.
            if (@touch($this->sqliteDatabasePath) === false) {
                throw new RuntimeException('Unable to create the testing SQLite database file.');
            }
        }

        // Force the environment to rely on the persistent SQLite database file for
        // every test execution so RefreshDatabase operates consistently.
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=' . $this->sqliteDatabasePath);
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $this->sqliteDatabasePath;
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_DATABASE'] = $this->sqliteDatabasePath;

        parent::setUp();

        $appBasePath = dirname(__DIR__);
        $envFile = $appBasePath.'/.env';

        if (! file_exists($envFile)) {
            file_put_contents($envFile, '');
            $this->createdEnvFile = true;
        } else {
            $this->createdEnvFile = false;
        }

        $testingDatabasePath = database_path('testing.sqlite');

        if (! file_exists($testingDatabasePath)) {
            // Guarantee the shared SQLite database exists before configuring the connection.
            touch($testingDatabasePath);
        }

        Config::set('database.default', 'sqlite');
        // Point the connection to the same persistent SQLite database file that was
        // created before bootstrapping so model factories run against real tables.
        Config::set('database.connections.sqlite.database', $this->sqliteDatabasePath);
        Config::set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        // Ensure Telescope doesn't use MySQL during tests and avoid watchers overhead
        Config::set('telescope.enabled', false);
        Config::set('telescope.storage.database.connection', 'sqlite');

        if (! Schema::hasTable('migrations')) {
            // Ensure the schema exists before factories execute so contract tests get
            // deterministic table structures on file-based SQLite databases.
            Artisan::call('migrate:fresh', ['--database' => 'sqlite']);
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

        if (! Schema::connection('sqlite')->hasTable('api_keys')) {
            // Guarantee the API key schema exists for partner API tests when RefreshDatabase skips migrations.
            Artisan::call('migrate', [
                '--database' => 'sqlite',
                '--force' => true,
            ]);
        }
    }

    protected function tearDown(): void
    {
        if ($this->resolvedAdminPanel instanceof Panel) {
            Filament::setCurrentPanel(null);
            Filament::setServingStatus(false);
            $this->resolvedAdminPanel = null;
        }

        $appBasePath = dirname(__DIR__);
        $envFile = $appBasePath.'/.env';

        if ($this->createdEnvFile && file_exists($envFile)) {
            unlink($envFile);
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
     * Ensure the in-memory SQLite database is active before migrations execute.
     *
     * @return void
     */
    protected function beforeRefreshingDatabase()
    {
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', database_path('database.sqlite'));
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
     * Always run destructive migration commands with the force flag enabled during tests.
     */
    protected function migrateFreshUsing()
    {
        return array_merge(parent::migrateFreshUsing(), ['--force' => true]);
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
