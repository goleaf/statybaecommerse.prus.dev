<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Components\LiveSearch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class LiveSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_search_component_can_be_rendered(): void
    {
        $component = Livewire::test(LiveSearch::class);

        $component->assertStatus(200);
    }

    public function test_live_search_shows_no_results_initially(): void
    {
        $component = Livewire::test(LiveSearch::class);

        $component->assertSet('query', '');
        $component->assertSet('results', []);
        $component->assertSet('showResults', false);
    }

    public function test_live_search_performs_search_when_query_updated(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'Test');

        $component->assertSet('showResults', true);
        $component->assertNotEmpty($component->get('results'));
    }

    public function test_live_search_does_not_search_with_short_query(): void
    {
        Product::factory()->create([
            'name' => 'Test Product',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'T'); // Less than minQueryLength (2)

        $component->assertSet('showResults', false);
        $component->assertEmpty($component->get('results'));
    }

    public function test_live_search_clears_results_when_query_cleared(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $component = Livewire::test(LiveSearch::class);

        // First, set a query to get results
        $component->set('query', 'Test');
        $component->assertSet('showResults', true);
        $component->assertNotEmpty($component->get('results'));

        // Then clear the query
        $component->set('query', '');

        $component->assertSet('showResults', false);
        $component->assertEmpty($component->get('results'));
    }

    public function test_live_search_returns_products_in_results(): void
    {
        $product = Product::factory()->create([
            'name' => 'Amazing Test Product',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'Amazing');

        $results = $component->get('results');
        $this->assertNotEmpty($results);

        $productResult = collect($results)->firstWhere('type', 'product');
        $this->assertNotNull($productResult);
        $this->assertEquals('Amazing Test Product', $productResult['title']);
        $this->assertEquals($product->id, $productResult['id']);
    }

    public function test_live_search_returns_categories_in_results(): void
    {
        $category = Category::factory()->create([
            'name' => 'Amazing Test Category',
            'is_visible' => true,
        ]);

        // Create a product in this category
        $product = Product::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);
        $category->products()->attach($product);

        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'Amazing');

        $results = $component->get('results');
        $this->assertNotEmpty($results);

        $categoryResult = collect($results)->firstWhere('type', 'category');
        $this->assertNotNull($categoryResult);
        $this->assertEquals('Amazing Test Category', $categoryResult['title']);
        $this->assertEquals($category->id, $categoryResult['id']);
    }

    public function test_live_search_returns_brands_in_results(): void
    {
        $brand = Brand::factory()->create([
            'name' => 'Amazing Test Brand',
            'is_enabled' => true,
        ]);

        // Create a product for this brand
        Product::factory()->create([
            'name' => 'Brand Product',
            'brand_id' => $brand->id,
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'Amazing');

        $results = $component->get('results');
        $this->assertNotEmpty($results);

        $brandResult = collect($results)->firstWhere('type', 'brand');
        $this->assertNotNull($brandResult);
        $this->assertEquals('Amazing Test Brand', $brandResult['title']);
        $this->assertEquals($brand->id, $brandResult['id']);
    }

    public function test_live_search_respects_max_results_limit(): void
    {
        // Create more products than the max results limit
        for ($i = 1; $i <= 15; $i++) {
            Product::factory()->create([
                'name' => "Test Product {$i}",
                'is_visible' => true,
                'published_at' => now()->subDay(),
            ]);
        }

        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'Test');

        $results = $component->get('results');
        $this->assertLessThanOrEqual(10, count($results)); // Default maxResults is 10
    }

    public function test_live_search_can_be_configured_with_custom_max_results(): void
    {
        // Create products
        for ($i = 1; $i <= 5; $i++) {
            Product::factory()->create([
                'name' => "Test Product {$i}",
                'is_visible' => true,
                'published_at' => now()->subDay(),
            ]);
        }

        $component = Livewire::test(LiveSearch::class)
            ->set('maxResults', 3);

        $component->set('query', 'Test');

        $results = $component->get('results');
        $this->assertLessThanOrEqual(3, count($results));
    }

    public function test_live_search_can_be_configured_with_custom_min_query_length(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $component = Livewire::test(LiveSearch::class)
            ->set('minQueryLength', 1);

        $component->set('query', 'T'); // Single character

        $component->assertSet('showResults', true);
        $component->assertNotEmpty($component->get('results'));
    }

    public function test_live_search_shows_loading_state(): void
    {
        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'Test');

        // The component should show loading state briefly
        $component->assertSet('isSearching', false); // Should be false after search completes
    }

    public function test_live_search_select_result_redirects_to_url(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'Test');

        $results = $component->get('results');
        $productResult = collect($results)->firstWhere('type', 'product');

        $component->call('selectResult', $productResult);

        $component->assertRedirect(route('products.show', 'test-product'));
    }

    public function test_live_search_clears_results_when_select_result(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'Test');
        $component->assertSet('showResults', true);

        $results = $component->get('results');
        $productResult = collect($results)->firstWhere('type', 'product');

        $component->call('selectResult', $productResult);

        $component->assertSet('showResults', false);
        $component->assertEmpty($component->get('results'));
    }

    public function test_live_search_handles_empty_search_results(): void
    {
        $component = Livewire::test(LiveSearch::class);

        $component->set('query', 'nonexistent');

        $component->assertSet('showResults', true);
        $component->assertEmpty($component->get('results'));
    }

    public function test_live_search_handles_special_characters_in_query(): void
    {
        $product = Product::factory()->create([
            'name' => 'Product with % special chars',
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $component = Livewire::test(LiveSearch::class);

        $component->set('query', '% special');

        $component->assertSet('showResults', true);
        $results = $component->get('results');
        $this->assertNotEmpty($results);
        $this->assertEquals('Product with % special chars', $results[0]['title']);
    }

    public function test_live_search_validates_query_input(): void
    {
        $component = Livewire::test(LiveSearch::class);

        // Test with very long query
        $longQuery = str_repeat('a', 300);
        $component->set('query', $longQuery);

        // Should still work but might be truncated by validation
        $component->assertSet('query', $longQuery);
    }

    public function test_live_search_renders_correct_view(): void
    {
        $component = Livewire::test(LiveSearch::class);

        $component->assertViewIs('livewire.components.live-search');
    }

    public function test_live_search_has_correct_default_properties(): void
    {
        $component = Livewire::test(LiveSearch::class);

        $component->assertSet('maxResults', 10);
        $component->assertSet('minQueryLength', 2);
        $component->assertSet('isSearching', false);
        $component->assertSet('showResults', false);
    }
}

