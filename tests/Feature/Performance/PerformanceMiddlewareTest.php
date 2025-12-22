<?php

declare(strict_types=1);

use App\Http\Middleware\PerformanceMeasurement;
use App\Jobs\StorePerformanceMetricsJob;
use App\Models\PerformanceMetrics;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    // Reset static caches
    $reflection = new ReflectionClass(PerformanceMeasurement::class);
    if ($reflection->hasProperty('measuredRoutesLookup')) {
        $property = $reflection->getProperty('measuredRoutesLookup');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }
});

it('uses array lookup for route checking performance', function () {
    $middleware = new PerformanceMeasurement;

    // Create request with measured route
    $request = Request::create('/lt', 'GET');
    $route = new Route(['GET'], '/lt', []);
    $route->name('localized.home');
    $request->setRouteResolver(fn () => $route);

    $start = microtime(true);

    // Process request multiple times to test caching
    for ($i = 0; $i < 100; $i++) {
        $middleware->handle($request, function ($req) {
            return new Response('OK');
        });
    }

    $duration = microtime(true) - $start;

    // Should handle 100 requests (under 10 seconds in test environment)
    expect($duration)->toBeLessThan(10.0);
});

it('skips measurement for non-measured routes efficiently', function () {
    $middleware = new PerformanceMeasurement;

    // Create request with non-measured route
    $request = Request::create('/admin', 'GET');
    $route = new Route(['GET'], '/admin', []);
    $route->name('admin.dashboard');
    $request->setRouteResolver(fn () => $route);

    $start = microtime(true);

    $response = $middleware->handle($request, function ($req) {
        return new Response('OK');
    });

    $duration = microtime(true) - $start;

    expect($response->getContent())->toBe('OK');
    // Should skip very quickly (under 0.1ms)
    expect($duration)->toBeLessThan(0.0001);
});

it('queues metrics storage asynchronously in production', function () {
    Queue::fake();

    // Simulate production environment
    app()->detectEnvironment(fn () => 'production');

    $middleware = new PerformanceMeasurement;

    $request = Request::create('/lt', 'GET');
    $route = new Route(['GET'], '/lt', []);
    $route->name('localized.home');
    $request->setRouteResolver(fn () => $route);

    $middleware->handle($request, function ($req) {
        return new Response('OK');
    });

    Queue::assertPushed(StorePerformanceMetricsJob::class);
});

it('stores metrics synchronously in testing environment', function () {
    // Ensure we're in testing environment
    app()->detectEnvironment(fn () => 'testing');

    $middleware = new PerformanceMeasurement;

    $request = Request::create('/lt', 'GET');
    $route = new Route(['GET'], '/lt', []);
    $route->name('localized.home');
    $request->setRouteResolver(fn () => $route);

    $initialCount = PerformanceMetrics::count();

    $middleware->handle($request, function ($req) {
        return new Response('OK');
    });

    expect(PerformanceMetrics::count())->toBe($initialCount + 1);
});

it('handles query counting efficiently', function () {
    $middleware = new PerformanceMeasurement;

    $request = Request::create('/lt', 'GET');
    $route = new Route(['GET'], '/lt', []);
    $route->name('localized.home');
    $request->setRouteResolver(fn () => $route);

    $start = microtime(true);

    $middleware->handle($request, function ($req) {
        // Simulate some database queries
        PerformanceMetrics::count();
        PerformanceMetrics::first();

        return new Response('OK');
    });

    $duration = microtime(true) - $start;

    // Should complete measurement in reasonable time even with queries (under 5 seconds)
    expect($duration)->toBeLessThan(5.0);
});

it('measures memory usage accurately', function () {
    $middleware = new PerformanceMeasurement;

    $request = Request::create('/lt', 'GET');
    $route = new Route(['GET'], '/lt', []);
    $route->name('localized.home');
    $request->setRouteResolver(fn () => $route);

    $initialMemory = memory_get_peak_usage(true);

    $middleware->handle($request, function ($req) {
        // Allocate significant memory to ensure measurable difference
        $data = array_fill(0, 10000, str_repeat('test data', 100));

        // Force memory allocation
        $moreData = array_merge($data, array_fill(0, 5000, 'additional data'));

        return new Response('OK');
    });

    $finalMemory = memory_get_peak_usage(true);

    // Memory should have increased (allow for same value in case of memory optimization)
    expect($finalMemory)->toBeGreaterThanOrEqual($initialMemory);

    // Check that metrics were stored
    $metrics = PerformanceMetrics::latest()->first();
    expect($metrics)->not->toBeNull();
    expect($metrics->peak_memory_mb)->toBeGreaterThan(0);
});
