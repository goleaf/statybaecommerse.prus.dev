<?php

declare(strict_types=1);

use App\Services\SearchPerformanceService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Cache::flush();
});

it('tracks search performance and updates cached recent searches', function () {
    $svc = app(SearchPerformanceService::class);
    $svc->trackSearchPerformance('hammer', 0.25, 10, 'products');

    $key = 'search_performance_recent_'.now()->format('Y-m-d-H');
    $recent = Cache::get($key, []);

    expect($recent)->toBeArray()->not->toBeEmpty();
});

it('returns performance stats and cache hit rates', function () {
    $svc = app(SearchPerformanceService::class);
    $stats = $svc->getPerformanceStats(7);
    $rates = $svc->getCacheHitRates();

    expect($stats)->toHaveKeys(['average_execution_time', 'slow_searches_count', 'total_searches'])
        ->and($rates)->toHaveKeys(['total_cache_hit_rate']);
});
