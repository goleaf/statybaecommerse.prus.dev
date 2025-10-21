<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\DataFilteringService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class DataFilteringServiceTest extends TestCase
{
    private DataFilteringService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DataFilteringService();
    }

    public function test_filter_quality_products_removes_invalid_entries_regardless_of_position(): void
    {
        $products = Collection::make([
            // Leading invalid product should be discarded.
            [
                'id' => 1,
                'name' => '',
                'is_visible' => true,
                'price' => 10.0,
                'slug' => 'placeholder-product',
                'stock_quantity' => 5,
                'is_published' => true,
            ],
            // First valid product should be retained.
            [
                'id' => 2,
                'name' => 'Starter Pack',
                'is_visible' => true,
                'price' => 15.0,
                'slug' => 'starter-pack',
                'stock_quantity' => 10,
                'is_published' => true,
            ],
            // Mid-collection invalid entry previously slipped through the skipWhile gate.
            [
                'id' => 3,
                'name' => 'Hidden Bundle',
                'is_visible' => false,
                'price' => 25.0,
                'slug' => 'hidden-bundle',
                'stock_quantity' => 2,
                'is_published' => true,
            ],
            // Trailing valid item ensures we retain all good data points.
            [
                'id' => 4,
                'name' => 'Premium Pack',
                'is_visible' => true,
                'price' => 45.0,
                'slug' => 'premium-pack',
                'stock_quantity' => 3,
                'is_published' => true,
            ],
            // Trailing invalid item should be filtered as well.
            [
                'id' => 5,
                'name' => 'Sold Out Edition',
                'is_visible' => true,
                'price' => 30.0,
                'slug' => 'sold-out-edition',
                'stock_quantity' => 0,
                'is_published' => true,
            ],
        ]);

        $filtered = $this->service->filterQualityProducts($products);

        $this->assertSame([2, 4], $filtered->pluck('id')->all());
    }

    public function test_filter_with_multiple_criteria_excludes_interleaved_mismatches(): void
    {
        $items = Collection::make([
            [
                'id' => 11,
                'price' => 5,
                'category' => 'electronics',
                'in_stock' => true,
            ],
            [
                'id' => 12,
                'price' => 55,
                'category' => 'electronics',
                'in_stock' => false,
            ],
            [
                'id' => 13,
                'price' => 75,
                'category' => 'gaming',
                'in_stock' => true,
            ],
            [
                'id' => 14,
                'price' => 85,
                'category' => 'electronics',
                'in_stock' => true,
            ],
        ]);

        $criteria = [
            'price' => ['min' => 50, 'max' => 100],
            'category' => 'electronics',
            'in_stock' => true,
        ];

        $filtered = $this->service->filterWithMultipleCriteria($items, $criteria);

        $this->assertSame([14], $filtered->pluck('id')->all());
    }
}
