<?php

declare(strict_types=1);

use App\Services\SearchCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

it('generates stable cache keys from query and context', function () {
    $svc = app(SearchCacheService::class);
    $k1 = $svc->generateCacheKey('hammer', ['user_id' => 5]);
    $k2 = $svc->generateCacheKey('hammer', ['user_id' => 5]);
    $k3 = $svc->generateCacheKey('hammer', ['user_id' => 6]);

    expect($k1)->toBe($k2)->and($k1)->not->toBe($k3);
});

it('caches and retrieves search results', function () {
    Cache::flush();
    $svc = app(SearchCacheService::class);

    $key = $svc->generateCacheKey('hammer', ['user_id' => 1]);
    $results = [
        ['id' => 1, 'type' => 'product', 'title' => 'Hammer A'],
        ['id' => 2, 'type' => 'product', 'title' => 'Hammer B'],
    ];

    $svc->cacheSearchResults($key, $results, 'hammer', ['user_id' => 1]);

    $fetched = $svc->getCachedResults($key);
    expect($fetched)->toBeArray()->toHaveCount(2)->and($fetched[0]['title'])->toBe('Hammer A');
});
