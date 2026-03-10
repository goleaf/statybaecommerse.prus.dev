<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\AutocompleteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class AutocompleteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_products_returns_matching_product_name(): void
    {
        Cache::flush();

        $matchingProduct = $this->createSearchableProduct([
            'name' => 'Kniedė Bralo aliuminis/ plienas standartinė',
            'slug' => 'kniede-bralo-aliuminis-plienas-standartine',
            'sku'  => 'BRALO-KNIEDE-001',
        ]);

        $this->createSearchableProduct([
            'name' => 'Kitas produktas',
            'slug' => 'kitas-produktas',
            'sku'  => 'OTHER-001',
        ]);

        $results = app(AutocompleteService::class)->searchProducts('Kniedė Bralo aliuminis', 10);

        $this->assertNotEmpty($results);
        $this->assertTrue(
            collect($results)->contains(
                static fn (array $result): bool => ($result['id'] ?? null) === $matchingProduct->getKey()
            )
        );
    }

    public function test_popular_suggestions_include_recent_products(): void
    {
        Cache::flush();

        $product = $this->createSearchableProduct([
            'name' => 'Kniedė Bralo aliuminis/ plienas standartinė',
            'slug' => 'kniede-bralo-aliuminis-plienas-standartine',
            'sku'  => 'BRALO-KNIEDE-002',
        ]);

        $suggestions = app(AutocompleteService::class)->getPopularSuggestions(10);

        $this->assertNotEmpty($suggestions);
        $this->assertTrue(
            collect($suggestions)->contains(
                static fn (array $suggestion): bool => ($suggestion['id'] ?? null) === $product->getKey()
            )
        );
    }

    public function test_search_products_returns_product_image_fields(): void
    {
        Cache::flush();

        $product = $this->createSearchableProduct([
            'name' => 'Kniedė Bralo aliuminis/ plienas su nuotrauka',
            'slug' => 'kniede-bralo-aliuminis-plienas-su-nuotrauka',
            'sku'  => 'BRALO-KNIEDE-IMG-001',
        ]);

        ProductImage::factory()->create([
            'product_id' => $product->getKey(),
            'path'       => 'product-images/test-image.jpg',
            'is_active'  => true,
            'is_default' => true,
            'sort_order' => 0,
        ]);

        $results = app(AutocompleteService::class)->searchProducts('Kniedė Bralo aliuminis', 10);
        $match = collect($results)->first(
            static fn (array $result): bool => ($result['id'] ?? null) === $product->getKey()
        );

        $this->assertNotNull($match);
        $this->assertNotEmpty($match['image'] ?? null);
        $this->assertNotEmpty($match['thumbnail'] ?? null);
        $this->assertNotEmpty($match['main_image'] ?? null);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createSearchableProduct(array $overrides = []): Product
    {
        $attributes = array_merge([
            'status'       => 'published',
            'is_enabled'   => true,
            'published_at' => now()->subMinute(),
        ], $overrides);

        if (Schema::hasColumn('products', 'is_visible')) {
            $attributes['is_visible'] = true;
        }

        return Product::factory()->create($attributes);
    }
}
