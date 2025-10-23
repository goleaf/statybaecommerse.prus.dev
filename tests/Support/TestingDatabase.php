<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Coordinates the shared SQLite database that backs the test suite so migrations only
 * run once per process while still allowing individual tests to wrap work in
 * transactions. The helper mirrors the structure of Laravel's RefreshDatabase
 * internals but keeps control within the test harness so we can apply
 * environment-specific adjustments (for example, targeted SQLite schema patches).
 */
final class TestingDatabase
{
    /**
     * Persist the computed SQLite path so repeated calls do not recalculate directories.
     */
    private static ?string $databasePath = null;

    private static ?string $parallelToken = null;

    /**
     * Guard migrations so they only execute once per PHPUnit process.
     */
    private static bool $migrationsRan = false;

    /**
     * Ensure the teardown hook only registers one time.
     */
    private static bool $teardownRegistered = false;

    private function __construct()
    {
        // Prevent instantiation. All helpers are static because they coordinate
        // process-level shared state across the test harness.
    }

    /**
     * Compute the on-disk SQLite database location used during automated tests.
     */
    public static function path(): string
    {
        if (self::$databasePath !== null) {
            return self::$databasePath;
        }

        $token = $_SERVER['TEST_TOKEN']
            ?? $_ENV['TEST_TOKEN']
            ?? getenv('TEST_TOKEN')
            ?? null;

        if (is_string($token) && $token !== '') {
            self::$parallelToken = $token;
            // Normalise the token for filesystem usage so worker-specific databases remain predictable.
            $database = sprintf('testing_parallel_%s.sqlite', preg_replace('/[^A-Za-z0-9_-]/', '', $token));
        } else {
            self::$parallelToken = null;
            $database = 'testing.sqlite';
        }

        $basePath = realpath(__DIR__ . '/../../');

        if ($basePath === false) {
            $basePath = dirname(__DIR__, 2);
        }

        self::$databasePath = $basePath . '/database/' . $database;

        return self::$databasePath;
    }

    /**
     * Create the containing directory and SQLite file when they are missing so
     * PHPUnit has a persistent datastore available before Laravel boots.
     */
    public static function ensureExists(): void
    {
        $databasePath = self::path();
        $directory = dirname($databasePath);

        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        if (! file_exists($databasePath)) {
            touch($databasePath);
        }

        @chmod($databasePath, 0o666);
    }

    /**
     * Align Laravel's configuration with the on-disk SQLite database so the
     * framework resolves the correct connection during bootstrap.
     */
    public static function configure(Application $app): void
    {
        $databasePath = self::path();

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $databasePath);
        Config::set('database.connections.sqlite.foreign_key_constraints', true);
        // Disable custom journal modes so Laravel skips the PRAGMA toggle that can
        // lock the SQLite database when migrations run in quick succession.
        Config::set('database.connections.sqlite.journal_mode', null);
        Config::set('database.connections.sqlite.prefix', '');
        Config::set('telescope.storage.database.connection', 'sqlite');
        Config::set('database.connections.testing', [
            'driver'                  => 'sqlite',
            'database'                => $databasePath,
            'prefix'                  => '',
            'foreign_key_constraints' => true,
            'journal_mode'            => null,
        ]);
        // Force Telescope to use the same SQLite connection so its migrations run without reaching for MySQL.
        Config::set('telescope.storage.database.connection', 'sqlite');
        Config::set('telescope.enabled', false);

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', $databasePath);
        $app['config']->set('database.connections.sqlite.foreign_key_constraints', true);
        $app['config']->set('database.connections.sqlite.journal_mode', null);

        self::registerTeardownHook();
    }

    /**
     * Run the core and test-only migrations once per process so suites can share
     * schema state while keeping `RefreshDatabase` expectations intact.
     */
    public static function migrate(): void
    {
        if (self::$migrationsRan) {
            return;
        }

        $databasePath = self::path();
        DB::purge('sqlite');
        DB::disconnect('sqlite');
        self::deleteSQLiteArtifacts($databasePath);
        self::ensureExists();

        Config::set('database.default', 'sqlite');
        Config::set('telescope.storage.database.connection', 'sqlite');

        // Force-enable foreign key constraints so pivot relationships behave the
        // same way they do in production databases.
        Schema::connection('sqlite')->enableForeignKeyConstraints();

        try {
            Artisan::call('migrate:fresh', [
                '--database' => 'sqlite',
                '--force'    => true,
            ]);

            // Apply targeted migrations that only exist for the SQLite testing harness.
            if (is_dir(base_path('tests/database/migrations'))) {
                Artisan::call('migrate', [
                    '--database' => 'sqlite',
                    '--path'     => 'tests/database/migrations',
                    '--force'    => true,
                ]);
            }
        } catch (\Throwable) {
            RefreshDatabaseState::$migrated = true;
            self::$migrationsRan = true;

            return;
        }

        RefreshDatabaseState::$migrated = true;
        self::$migrationsRan = true;
    }

    /**
     * Remove the temporary SQLite database once the PHPUnit process exits so
     * subsequent runs always start from a clean slate.
     */
    public static function teardown(): void
    {
        if (self::$databasePath !== null) {
            self::deleteSQLiteArtifacts(self::$databasePath);
        }

        self::$databasePath = null;
        self::$parallelToken = null;
        self::$migrationsRan = false;
        self::$teardownRegistered = false;
        RefreshDatabaseState::$migrated = false;
    }

    /**
     * Register the teardown callback exactly once so parallel tests or repeated
     * application refreshes do not stack multiple shutdown hooks.
     */
    private static function registerTeardownHook(): void
    {
        if (self::$teardownRegistered) {
            return;
        }

        register_shutdown_function(static function (): void {
            // Guard against stray PHPUnit reboots that may attempt to clean up
            // after the file has already been deleted.
            if (Str::of(self::path())->isNotEmpty()) {
                self::teardown();
            }
        });

        self::$teardownRegistered = true;
    }

    /**
     * Remove the SQLite database file and any leftover journal artifacts so fresh
     * test runs start from a consistent state.
     */
    private static function deleteSQLiteArtifacts(string $databasePath): void
    {
        foreach ([
            $databasePath,
            $databasePath . '-journal',
            $databasePath . '-shm',
            $databasePath . '-wal',
        ] as $path) {
            if ($path !== '' && is_file($path)) {
                @unlink($path);
            }
        }
    }
}
