<?php

declare(strict_types=1);

use App\Services\SearchPaginationService;
use Tests\TestCase;

uses(TestCase::class);

it('paginates results with correct flags', function () {
    $svc = app(SearchPaginationService::class);
    $results = [];
    for ($i = 1; $i <= 50; $i++) {
        $results[] = [
            'id' => $i,
            'price' => $i,
            'type' => $i % 2 ? 'a' : 'b',
            'category' => 'c'.($i % 3),
            'brand' => 'b'.($i % 2),
            'average_rating' => ($i % 5) + 1,
        ];
    }

    $page = $svc->paginateSearchResults($results, 'q', 2, 10);

    expect($page['pagination']['current_page'])->toBe(2)
        ->and($page['pagination']['per_page'])->toBe(10)
        ->and($page['pagination']['total'])->toBe(50)
        ->and((int) $page['pagination']['total_pages'])->toBe(5)
        ->and($page['pagination']['has_next_page'])->toBeTrue()
        ->and($page['pagination']['has_prev_page'])->toBeTrue()
        ->and(count($page['data']))->toBe(10);
});

it('applies filters to results', function () {
    $svc = app(SearchPaginationService::class);
    $results = [
        ['type' => 'a', 'price' => 5, 'category' => 'tech', 'brand' => 'acme', 'average_rating' => 4.8],
        ['type' => 'b', 'price' => 55, 'category' => 'home', 'brand' => 'globex', 'average_rating' => 3.2],
        ['type' => 'a', 'price' => 500, 'category' => 'tech', 'brand' => 'acme', 'average_rating' => 4.0],
    ];

    $out = $svc->paginateSearchResults($results, 'q', 1, 20, [
        'type' => 'a',
        'price_min' => 10,
        'price_max' => 500,
        'brand' => 'acme',
        'rating_min' => 4.0,
    ]);

    expect($out['pagination']['total'])->toBe(1)
        ->and((float) $out['data'][0]['price'])->toBe(500.0);
});

it('builds available filters summary', function () {
    $svc = app(SearchPaginationService::class);
    $results = [
        ['type' => 'a', 'price' => 5, 'category' => 'tech', 'brand' => 'acme', 'average_rating' => 4.8],
        ['type' => 'b', 'price' => 55, 'category' => 'home', 'brand' => 'globex', 'average_rating' => 3.2],
        ['type' => 'a', 'price' => 500, 'category' => 'tech', 'brand' => 'acme', 'average_rating' => 4.0],
    ];

    $filters = $svc->getAvailableFilters($results);

    expect($filters)->toHaveKeys(['types', 'price_ranges', 'categories', 'brands', 'ratings']);
});
