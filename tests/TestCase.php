<?php

declare(strict_types=1);

namespace Tests;

use App\Support\Cache\TagAwareCache;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Contracts\Translation\Loader as TranslationLoader;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Throwable;
use Tests\Support\TestingDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private bool $createdEnvFile = false;

    private ?Panel $resolvedAdminPanel = null;

    private bool $createdViteManifest = false;

    private string $viteManifestPath = '';

    protected function setUp(): void
    {
        if (! class_exists(TestingDatabase::class) && file_exists(__DIR__ . '/Support/TestingDatabase.php')) {
            require_once __DIR__ . '/Support/TestingDatabase.php';
        }

        // Resolve the shared SQLite database location before the application boots so the
        // parent setup sequence works with the same persistent datastore prepared by
        // Tests\Support\TestingDatabase.
        $this->sqliteDatabasePath = TestingDatabase::path();

        TestingDatabase::ensureExists();

        parent::setUp();

        $appBasePath = dirname(__DIR__);
        $envFile = $appBasePath.'/.env';

        if (! file_exists($envFile)) {
            file_put_contents($envFile, '');
            $this->createdEnvFile = true;
        } else {
            $this->createdEnvFile = false;
        }

        // Ensure the database connection stays aligned with the shared SQLite database so model
        // factories run against real tables after Laravel's bootstrapping sequence completes.
        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $this->sqliteDatabasePath);
        Config::set('database.connections.sqlite.foreign_key_constraints', true);
        Config::set('database.connections.sqlite.journal_mode', null);
        Config::set('database.connections.sqlite.busy_timeout', 5000);
        Config::set('cache.default', 'array');
        Config::set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        Config::set('app.debug', false);
        // Ensure Telescope doesn't use MySQL during tests and avoid watchers overhead.
        Config::set('telescope.enabled', false);
        Config::set('telescope.storage.database.connection', 'sqlite');
        $this->ensureViteManifest();
        $this->refreshTranslationLoader();
        app()->instance('request', Request::create('/'));
        Auth::forgetGuards();
        $this->withoutMiddleware([
            \App\Http\Middleware\ZoneDetector::class,
            \App\Http\Middleware\SetLocale::class,
            \Spatie\Permission\Middleware\PermissionMiddleware::class,
            \Spatie\Permission\Middleware\RoleMiddleware::class,
            \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        if (function_exists('activity')) {
            activity()->disableLogging();
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

        if ($this->createdViteManifest && $this->viteManifestPath !== '' && file_exists($this->viteManifestPath)) {
            // Remove the temporary Vite manifest so local builds are never polluted by the tests.
            unlink($this->viteManifestPath);

            $directory = dirname($this->viteManifestPath);
            if (is_dir($directory) && count(scandir($directory) ?: []) <= 2) {
                // Clean up the empty build directory that was created specifically for the manifest file.
                rmdir($directory);
            }
        }

        Auth::forgetGuards();

        parent::tearDown();

    }

    /**
     * Override the default database refresh cycle to lean on the shared
     * TestingDatabase helper. Laravel's RefreshDatabase trait will
     * call this method for every test that uses the trait, so we
     * ensure migrations only execute once per process and
     * then wrap each test in a fresh transaction.
     */
    protected function refreshDatabase()
    {
        $this->beforeRefreshingDatabase();

        TestingDatabase::migrate();

        $this->beginDatabaseTransaction();

        $this->afterRefreshingDatabase();
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
        $databasePath = TestingDatabase::path();
        TestingDatabase::ensureExists();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $databasePath);
        Config::set('database.connections.sqlite.foreign_key_constraints', true);
        Config::set('database.connections.sqlite.journal_mode', null);
        Config::set('database.connections.sqlite.busy_timeout', 5000);
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

    private function ensureViteManifest(): void
    {
        // Determine the manifest location using Laravel's helper so tests mirror production structure.
        $this->viteManifestPath = public_path('build/manifest.json');

        if (file_exists($this->viteManifestPath)) {
            // Respect an existing manifest generated by a real build step.
            $this->createdViteManifest = false;

            return;
        }

        $directory = dirname($this->viteManifestPath);

        if (! is_dir($directory)) {
            // Ensure the build directory exists before attempting to write the manifest file.
            mkdir($directory, 0o755, true);
        }

        $manifest = [
            // Provide minimal asset mappings so Blade's @vite directive resolves without throwing.
            'resources/css/app.scss' => [
                'file'    => 'assets/app.css',
                'isEntry' => true,
                'src'     => 'resources/css/app.scss',
            ],
            'resources/js/app.js' => [
                'file'    => 'assets/app.js',
                'isEntry' => true,
                'src'     => 'resources/js/app.js',
            ],
            'resources/css/filament/admin/theme.scss' => [
                'file'    => 'assets/filament-admin-theme.css',
                'isEntry' => false,
                'src'     => 'resources/css/filament/admin/theme.scss',
            ],
        ];

        file_put_contents(
            $this->viteManifestPath,
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );

        $this->createdViteManifest = true;
    }
}
