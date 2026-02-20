<?php

declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LocalizedSearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_localized_search_page_renders_with_localized_schema_route(): void
    {
        app()->setLocale('lt');

        $response = $this->get('/lt/search?q=bet');

        $response->assertOk();
        $response->assertSee('/lt/search?q={search_term_string}', false);
        $response->assertDontSee('Route [search] not defined', false);
    }

    public function test_localized_search_page_renders_product_results_without_route_exceptions(): void
    {
        app()->setLocale('lt');

        $brand = Brand::factory()->create([
            'name'       => 'Beton Brand',
            'slug'       => 'beton-brand',
            'is_active'  => true,
            'is_enabled' => true,
            'is_visible' => true,
        ]);

        $product = Product::factory()->create([
            'name'         => 'Beton Drill Test',
            'slug'         => 'beton-drill-test',
            'brand_id'     => $brand->id,
            'sku'          => 'BET-TEST-001',
            'status'       => 'published',
            'is_active'    => true,
            'is_enabled'   => true,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get('/lt/search?q=beton');

        $response->assertOk();
        $response->assertSee('Beton Drill Test');
    }
}
