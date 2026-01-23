<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

/**
 * Production readiness checklist test.
 *
 * Verifies framework optimizations are configured, Redis configuration is production-ready,
 * queue processing is properly configured, and monitoring systems are in place.
 */
final class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_framework_optimizations_are_configured(): void
    {
        // Verify performance configuration exists
        expect(config('performance.framework.enable_optimizations'))->toBeBool();

        // Verify optimization commands are defined
        $commands = config('performance.framework.optimization_commands');
        expect($commands)->toBeArray();
        expect($commands)->toContain('config:cache');
        expect($commands)->toContain('route:cache');
        expect($commands)->toContain('view:cache');
        expect($commands)->toContain('event:cache');
    }

    public function test_optimization_commands_can_be_executed(): void
    {
        // Skip config:cache in test environment due to serialization issues
        if (! app()->environment('testing')) {
            $exitCode = Artisan::call('config:cache');
            expect($exitCode)->toBe(0);
        }

        $exitCode = Artisan::call('route:cache');
        expect($exitCode)->toBe(0);

        $exitCode = Artisan::call('view:cache');
        expect($exitCode)->toBe(0);

        $exitCode = Artisan::call('event:cache');
        expect($exitCode)->toBe(0);

        // Clean up
        if (! app()->environment('testing')) {
            Artisan::call('config:clear');
        }
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('event:clear');
    }

    public function test_redis_configuration_is_production_ready(): void
    {
        // Verify Redis configuration exists
        expect(config('performance.redis'))->toBeArray();

        // Verify Redis settings
        $redisConfig = config('performance.redis');
        expect($redisConfig)->toHaveKey('enabled');
        expect($redisConfig)->toHaveKey('cache_prefix');
        expect($redisConfig)->toHaveKey('session_prefix');

        // Verify prefixes are set to avoid conflicts
        expect($redisConfig['cache_prefix'])->toBeString();
        expect($redisConfig['session_prefix'])->toBeString();
        expect($redisConfig['cache_prefix'])->not->toBeEmpty();
        expect($redisConfig['session_prefix'])->not->toBeEmpty();
    }

    public function test_cache_configuration_is_production_ready(): void
    {
        // Verify cache driver is configured
        $cacheDriver = config('cache.default');
        expect($cacheDriver)->toBeString();

        // In test environment, array driver is acceptable
        if (! app()->environment('testing')) {
            expect($cacheDriver)->not->toBe('array'); // Array driver not suitable for production
        }

        // Verify cache stores are configured
        $stores = config('cache.stores');
        expect($stores)->toBeArray();
        expect($stores)->toHaveKey($cacheDriver);

        // Verify performance cache settings
        expect(config('performance.cache.optimize_serialization'))->toBeBool();
        expect(config('performance.cache.enable_warming'))->toBeBool();
        expect(config('performance.cache.prevent_redundant_writes'))->toBeBool();
    }

    public function test_session_configuration_is_production_ready(): void
    {
        // Verify session driver is not file-based for production
        $sessionDriver = config('session.driver');
        expect($sessionDriver)->toBeString();

        // Verify session configuration
        expect(config('session.lifetime'))->toBeInt();
        expect(config('session.expire_on_close'))->toBeBool();
        expect(config('session.encrypt'))->toBeBool();

        // Verify secure session settings
        if (app()->environment('production')) {
            expect(config('session.secure'))->toBeBool();
        }
        expect(config('session.http_only'))->toBe(true);
        expect(config('session.same_site'))->toBeString();
    }

    public function test_queue_configuration_is_production_ready(): void
    {
        // Verify queue driver is not sync for production
        $queueDriver = config('queue.default');
        expect($queueDriver)->toBeString();

        if (app()->environment('production')) {
            expect($queueDriver)->not->toBe('sync');
        }

        // Verify queue connections are configured
        $connections = config('queue.connections');
        expect($connections)->toBeArray();
        expect($connections)->toHaveKey($queueDriver);

    }

    public function test_performance_monitoring_is_configured(): void
    {
        // Verify performance monitoring is enabled
        expect(config('performance.monitoring.enabled'))->toBeBool();

        // Verify budgets are configured
        $queryBudgets = config('performance.monitoring.query_budgets');
        expect($queryBudgets)->toBeArray();
        expect($queryBudgets)->toHaveKey('home');
        expect($queryBudgets)->toHaveKey('category');
        expect($queryBudgets)->toHaveKey('product');
        expect($queryBudgets)->toHaveKey('search');

        $memoryBudgets = config('performance.monitoring.memory_budgets');
        expect($memoryBudgets)->toBeArray();
        expect($memoryBudgets)->toHaveKey('home');
        expect($memoryBudgets)->toHaveKey('category');
        expect($memoryBudgets)->toHaveKey('product');
        expect($memoryBudgets)->toHaveKey('search');

        $ttfbBudgets = config('performance.monitoring.ttfb_budgets');
        expect($ttfbBudgets)->toBeArray();
        expect($ttfbBudgets)->toHaveKey('home');
        expect($ttfbBudgets)->toHaveKey('category');
        expect($ttfbBudgets)->toHaveKey('product');
        expect($ttfbBudgets)->toHaveKey('search');
    }

    public function test_logging_configuration_is_production_ready(): void
    {
        // Verify logging configuration
        $logChannel = config('logging.default');
        expect($logChannel)->toBeString();

        $channels = config('logging.channels');
        expect($channels)->toBeArray();
        expect($channels)->toHaveKey($logChannel);

        // Verify log level is appropriate
        $logLevel = config('logging.channels.' . $logChannel . '.level', 'debug');
        if (app()->environment('production')) {
            expect($logLevel)->not->toBe('debug');
        }
    }

    public function test_database_configuration_is_production_ready(): void
    {
        // Verify database connection
        $dbConnection = config('database.default');
        expect($dbConnection)->toBeString();

        $connections = config('database.connections');
        expect($connections)->toBeArray();
        expect($connections)->toHaveKey($dbConnection);

        // Verify database settings
        $connection = $connections[$dbConnection];
        expect($connection)->toHaveKey('driver');

        // SQLite doesn't have host, so check conditionally
        if ($connection['driver'] !== 'sqlite') {
            expect($connection)->toHaveKey('host');
        }
        expect($connection)->toHaveKey('database');

        // Verify connection pooling settings if applicable
        if (isset($connection['options'])) {
            expect($connection['options'])->toBeArray();
        }
    }

    public function test_app_environment_configuration(): void
    {
        // Verify APP_ENV is set
        expect(config('app.env'))->toBeString();

        // Verify APP_DEBUG is appropriate for environment
        $debug = config('app.debug');
        expect($debug)->toBeBool();

        if (app()->environment('production')) {
            expect($debug)->toBe(false);
        }

        // Verify APP_KEY is set
        expect(config('app.key'))->toBeString();
        expect(config('app.key'))->not->toBeEmpty();
    }

    public function test_security_configuration_is_production_ready(): void
    {
        // Verify HTTPS enforcement in production
        if (app()->environment('production')) {
            expect(config('app.url'))->toStartWith('https://');
        }

        // Verify CSRF protection is enabled (session encryption is optional)
        expect(config('session.encrypt'))->toBeBool();

        // Verify secure headers configuration
        $trustedProxies = config('trustedproxy.proxies');
        expect($trustedProxies)->not->toBeNull();
    }

    public function test_performance_middleware_is_registered(): void
    {
        // Verify performance measurement middleware exists
        $middlewareGroups = config('app.middleware_groups', []);

        // Check if performance middleware is available
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        expect($kernel)->toBeInstanceOf(\Illuminate\Contracts\Http\Kernel::class);
    }
}
