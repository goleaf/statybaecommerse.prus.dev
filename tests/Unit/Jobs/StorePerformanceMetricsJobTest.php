<?php

declare(strict_types=1);

use App\Jobs\StorePerformanceMetricsJob;
use App\Models\PerformanceMetrics;
use Illuminate\Support\Facades\Log;

it('stores performance metrics successfully', function () {
    $job = new StorePerformanceMetricsJob(
        routeName: 'localized.home',
        ttfb: 150.5,
        queryCount: 3,
        peakMemoryMb: 64,
        environment: 'testing',
        userAgent: 'Test Browser'
    );

    $initialCount = PerformanceMetrics::count();

    $job->handle();

    expect(PerformanceMetrics::count())->toBe($initialCount + 1);

    $metrics = PerformanceMetrics::latest()->first();
    expect($metrics->page_route)->toBe('localized.home');
    expect($metrics->ttfb_p50)->toBe(150.5);
    expect($metrics->query_count)->toBe(3);
    expect($metrics->peak_memory_mb)->toBe(64);
    expect($metrics->environment)->toBe('testing');
});

it('handles database errors gracefully', function () {
    // Mock a database error
    PerformanceMetrics::shouldReceive('create')
        ->once()
        ->andThrow(new Exception('Database connection failed'));

    Log::shouldReceive('warning')
        ->once()
        ->with('Failed to store performance metrics in job', Mockery::type('array'));

    $job = new StorePerformanceMetricsJob(
        routeName: 'localized.home',
        ttfb: 150.5,
        queryCount: 3,
        peakMemoryMb: 64,
        environment: 'testing'
    );

    expect(fn () => $job->handle())->toThrow(Exception::class);
});

it('logs failure information when job fails permanently', function () {
    Log::shouldReceive('error')
        ->once()
        ->with('Performance metrics job failed permanently', Mockery::type('array'));

    $job = new StorePerformanceMetricsJob(
        routeName: 'localized.home',
        ttfb: 150.5,
        queryCount: 3,
        peakMemoryMb: 64,
        environment: 'testing'
    );

    $exception = new Exception('Permanent failure');
    $job->failed($exception);
});

it('has appropriate retry and timeout settings', function () {
    $job = new StorePerformanceMetricsJob(
        routeName: 'localized.home',
        ttfb: 150.5,
        queryCount: 3,
        peakMemoryMb: 64,
        environment: 'testing'
    );

    expect($job->tries)->toBe(3);
    expect($job->timeout)->toBe(30);
});

it('uses metrics queue', function () {
    $job = new StorePerformanceMetricsJob(
        routeName: 'localized.home',
        ttfb: 150.5,
        queryCount: 3,
        peakMemoryMb: 64,
        environment: 'testing'
    );

    expect($job->queue)->toBe('metrics');
});
