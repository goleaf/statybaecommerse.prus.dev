<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_query_returns_only_visible_published_products(): void
    {
        $visible = Product::factory()->create([
            'is_visible'   => true,
            'published_at' => now()->subDay(),
            'status'       => 'published',
        ]);

        Product::factory()->create([
            'is_visible'   => false,
            'published_at' => now()->subDay(),
        ]);

        Product::factory()->create([
            'is_visible'   => true,
            'published_at' => null,
        ]);

        $results = Product::query()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->pluck('id')
            ->all();

        $this->assertSame([$visible->id], $results);
    }

    public function test_catalog_filtering_by_brand_and_category(): void
    {
        $brand = Brand::factory()->create();
        $otherBrand = Brand::factory()->create();
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        $matchingProduct = Product::factory()
            ->hasAttached($category, [], 'categories')
            ->create([
                'brand_id'     => $brand->id,
                'is_visible'   => true,
                'published_at' => now()->subDay(),
                'status'       => 'published',
            ]);

        Product::factory()
            ->hasAttached($otherCategory, [], 'categories')
            ->create([
                'brand_id'     => $otherBrand->id,
                'is_visible'   => true,
                'published_at' => now()->subDay(),
                'status'       => 'published',
            ]);

        $results = Product::query()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('brand_id', $brand->id)
            ->whereHas('categories', fn ($query) => $query->whereKey($category->id))
            ->pluck('id');

        $this->assertEquals([$matchingProduct->id], $results->all());
    }

    public function test_catalog_sorting_by_price(): void
    {
        $cheap = Product::factory()->create([
            'price'        => '10.00',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
            'status'       => 'published',
        ]);
        $expensive = Product::factory()->create([
            'price'        => '50.00',
            'is_visible'   => true,
            'published_at' => now()->subDay(),
            'status'       => 'published',
        ]);

        $asc = Product::query()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('price', 'asc')
            ->pluck('id')
            ->all();

        $desc = Product::query()
            ->where('is_visible', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('price', 'desc')
            ->pluck('id')
            ->all();

        $this->assertSame([$cheap->id, $expensive->id], $asc);
        $this->assertSame([$expensive->id, $cheap->id], $desc);
    }
}
