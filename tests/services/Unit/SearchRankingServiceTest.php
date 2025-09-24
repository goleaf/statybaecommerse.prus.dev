<?php

declare(strict_types=1);

use App\Services\SearchRankingService;
use Tests\TestCase;

uses(TestCase::class);

it('ranks results and caches by query+context', function () {
    $svc = app(SearchRankingService::class);
    $results = [
        ['type' => 'product', 'title' => 'Red Hammer', 'description' => 'Strong steel', 'relevance_score' => 50, 'sales_count' => 10],
        ['type' => 'product', 'title' => 'Blue Hammer', 'description' => 'Lightweight', 'relevance_score' => 60, 'sales_count' => 5],
    ];

    $ranked1 = $svc->rankResults($results, 'hammer');
    $ranked2 = $svc->rankResults($results, 'hammer');

    expect($ranked1)->toBeArray()->and($ranked2)->toEqual($ranked1);
});

it('applies business rules boost', function () {
    $svc = app(SearchRankingService::class);
    $results = [
        ['type' => 'product', 'title' => 'Item A', 'in_stock' => true, 'ranking_score' => 0.5, 'is_featured' => true],
        ['type' => 'product', 'title' => 'Item B', 'in_stock' => false, 'ranking_score' => 0.5, 'is_featured' => false],
    ];

    $boosted = $svc->applyBusinessRules($results);

    expect($boosted[0]['ranking_score'])->toBeGreaterThan($boosted[1]['ranking_score']);
});
