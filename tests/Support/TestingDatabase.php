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
     * Keep track of how many times the SQLite datastore has been rotated during the current migration attempt.
     */
    private static int $sqliteRetryAttempts = 0;

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

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
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

                if (is_dir(base_path('tests/database/migrations'))) {
                    Artisan::call('migrate', [
                        '--database' => 'sqlite',
                        '--path'     => 'tests/database/migrations',
                        '--force'    => true,
                    ]);
                }

                RefreshDatabaseState::$migrated = true;
                self::$migrationsRan = true;
                self::$sqliteRetryAttempts = 0;

                return;
            } catch (\Throwable $exception) {
                self::$sqliteRetryAttempts++;

                $canRetry = $attempt < $maxAttempts && self::shouldRotateSqliteDatabase($exception);

                if ($canRetry) {
                    self::rotateSqliteDatabase();
                    continue;
                }

                throw $exception;
            }
        }
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

    private static function shouldRotateSqliteDatabase(\Throwable $throwable): bool
    {
        $message = strtolower($throwable->getMessage());

        return str_contains($message, 'disk i/o')
            || str_contains($message, 'database disk image is malformed')
            || str_contains($message, 'attempt to write a readonly database')
            || str_contains($message, 'database is locked');
    }

    private static function rotateSqliteDatabase(): void
    {
        $basePath = realpath(__DIR__ . '/../../');

        if ($basePath === false) {
            $basePath = dirname(__DIR__, 2);
        }

        $directory = $basePath . '/database';

        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        self::$databasePath = $directory . '/testing_cli_' . Str::random(12) . '.sqlite';
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

    /**
     * Provision a minimal schema when the full migration stack cannot execute against SQLite.
     */
    private static function provisionFallbackSchema(): void
    {
        $connection = config('database.default', 'sqlite');
        $schema = Schema::connection($connection);

        if (! $schema->hasTable('users')) {
            $schema->create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('preferred_locale', 5)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_admin')->default(false);
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('activity_log')) {
            $schema->create('activity_log', function (Blueprint $table): void {
                $table->id();
                $table->string('log_name')->nullable();
                $table->text('description')->nullable();
                $table->string('event')->nullable();
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('causer_type')->nullable();
                $table->unsignedBigInteger('causer_id')->nullable();
                $table->json('properties')->nullable();
                $table->uuid('batch_uuid')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('device_type')->nullable();
                $table->string('browser')->nullable();
                $table->string('os')->nullable();
                $table->string('country', 2)->nullable();
                $table->boolean('is_important')->default(false);
                $table->boolean('is_system')->default(false);
                $table->string('severity')->nullable();
                $table->string('category')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('cart_items')) {
            $schema->create('cart_items', function (Blueprint $table): void {
                $table->id();
                $table->string('session_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('variant_id')->nullable();
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 12, 2)->nullable();
                $table->decimal('total_price', 12, 2)->nullable();
                $table->json('attributes')->nullable();
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('products')) {
            $schema->create('products', function (Blueprint $table): void {
                $table->id();
                $table->string('type')->default('simple');
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->text('short_description')->nullable();
                $table->string('sku')->nullable()->unique();
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('sale_price', 10, 2)->nullable();
                $table->decimal('compare_price', 10, 2)->nullable();
                $table->decimal('cost_price', 10, 2)->nullable();
                $table->boolean('manage_stock')->default(false);
                $table->integer('stock_quantity')->default(0);
                $table->integer('low_stock_threshold')->default(0);
                $table->decimal('weight', 8, 2)->nullable();
                $table->decimal('length', 8, 2)->nullable();
                $table->decimal('width', 8, 2)->nullable();
                $table->decimal('height', 8, 2)->nullable();
                $table->boolean('is_visible')->default(true);
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_featured')->default(false);
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('product_variants')) {
            $schema->create('product_variants', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('name')->nullable();
                $table->string('sku')->nullable()->unique();
                $table->decimal('price', 10, 2)->nullable();
                $table->decimal('compare_price', 10, 2)->nullable();
                $table->decimal('cost_price', 10, 2)->nullable();
                $table->integer('stock_quantity')->default(0);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
            });
        }

        if (! $schema->hasTable('variant_analytics')) {
            $schema->create('variant_analytics', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->constrained('product_variants')->cascadeOnDelete();
                $table->date('date');
                $table->string('date_bucket');
                $table->integer('views')->default(0);
                $table->integer('clicks')->default(0);
                $table->integer('add_to_cart')->default(0);
                $table->integer('purchases')->default(0);
                $table->decimal('revenue', 10, 4)->default(0);
                $table->decimal('conversion_rate', 5, 4)->default(0);
                $table->timestamps();
                $table->unique(['product_id', 'variant_id', 'date_bucket'], 'variant_analytics_product_variant_bucket_unique');
            });
        }
    }
}
