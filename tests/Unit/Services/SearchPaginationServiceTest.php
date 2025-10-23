<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\SearchPaginationService;
use Tests\TestCase;

final class SearchPaginationServiceTest extends TestCase
{
    public function test_in_stock_filter_recognises_false_like_strings(): void
    {
        $service = app(SearchPaginationService::class);

        $results = [
            ['id' => 1, 'in_stock' => true],
            ['id' => 2, 'in_stock' => false],
        ];

        // Ensure that the textual "false" flag is treated as an explicit
        // request to filter out in-stock results rather than being ignored.
        $response = $service->paginateSearchResults(
            results: $results,
            query: 'widgets',
            page: 1,
            pageSize: 10,
            filters: ['in_stock' => 'false'],
        );

        $this->assertCount(1, $response['data']);
        $this->assertSame(2, $response['data'][0]['id']);
    }

    public function test_featured_filter_accepts_zero_string_as_boolean_false(): void
    {
        $service = app(SearchPaginationService::class);

        $results = [
            ['id' => 1, 'is_featured' => true],
            ['id' => 2, 'is_featured' => false],
        ];

        // Confirm that the numeric string "0" behaves like boolean false so
        // shoppers can intentionally include non-featured results.
        $response = $service->paginateSearchResults(
            results: $results,
            query: 'gadgets',
            page: 1,
            pageSize: 10,
            filters: ['featured' => '0'],
        );

        $this->assertCount(1, $response['data']);
        $this->assertSame(2, $response['data'][0]['id']);
    }
}
