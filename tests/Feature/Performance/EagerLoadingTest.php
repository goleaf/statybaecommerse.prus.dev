<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Livewire\Home\ProductCatalogue;
use App\Livewire\Home\ProductShelf;
use App\Livewire\Pages\Category\Show as CategoryShow;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\QueryMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Test suite to verify that N+1 query patterns have been eliminated
 * through proper eager loading in product list components.
 */
final class EagerLoadingTest extends TestCase
{
    use RefreshDatabase;

    private QueryMonitoringService $queryMonitor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queryMonitor = app(QueryMonitoringService::class);
    }

    public function test_product_shelf_eliminates_n1_patterns(): void
    {
        // Create test data with relations
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create(['name' => 'Test Category']);

        // Create multiple products to test for N+1 patterns
        $products = Product::factory()
            ->count(10)
            ->create([
                'brand_id'     => $brand->id,
                'is_visible'   => true,
                'is_featured'  => true,
                'published_at' => now()->subDay(),
            ]);

        // Create translations for products
        foreach ($products as $product) {
            $product->translations()->create([
                'locale'            => 'en',
                'name'              => $product->name,
                'slug'              => $product->slug,
                'short_description' => $product->short_description,
            ]);
            $product->categories()->attach($category->id);
        }

        // Create translations for brand and category
        $brand->translations()->create([
            'locale' => 'en',
            'name'   => $brand->name,
            'slug'   => $brand->slug,
        ]);

        $category->translations()->create([
            'locale' => 'en',
            'name'   => $category->name,
            'slug'   => $category->slug,
        ]);

        // Start monitoring queries
        $this->queryMonitor->startMonitoring(30); // Allow up to 30 queries for Livewire component

        // Render the ProductShelf component
        $component = Livewire::test(ProductShelf::class, [
            'preset' => 'featured',
            'limit'  => 10,
        ]);

        $component->assertOk();

        // Stop monitoring and analyze results
        $queryData = $this->queryMonitor->stopMonitoring();

        // Assert that we don't exceed the query budget
        $this->assertLessThanOrEqual(30, $queryData['total_queries'],
            'ProductShelf exceeded query budget. Queries: ' . $queryData['total_queries']);

        // Assert no actual N+1 patterns detected (ignore schema queries)
        $actualN1Patterns = array_filter($queryData['n1_patterns'], fn ($pattern) => $pattern['likely_n1'] === true);
        $this->assertEmpty($actualN1Patterns,
            'N+1 patterns detected in ProductShelf: ' . json_encode($actualN1Patterns));

        // Verify that products are loaded
        $this->assertNotEmpty($component->get('products'));
    }

    public function test_category_show_eliminates_n1_patterns(): void
    {
        // Create test data
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create([
            'name'       => 'Test Category',
            'is_visible' => true,
        ]);

        // Create multiple products
        $products = Product::factory()
            ->count(15)
            ->create([
                'brand_id'     => $brand->id,
                'is_visible'   => true,
                'published_at' => now()->subDay(),
            ]);

        // Create translations and attach category to products
        foreach ($products as $product) {
            $product->translations()->create([
                'locale'            => 'en',
                'name'              => $product->name,
                'slug'              => $product->slug,
                'short_description' => $product->short_description,
            ]);
            $product->categories()->attach($category->id);
        }

        // Create translations for brand and category
        $brand->translations()->create([
            'locale' => 'en',
            'name'   => $brand->name,
            'slug'   => $brand->slug,
        ]);

        $category->translations()->create([
            'locale' => 'en',
            'name'   => $category->name,
            'slug'   => $category->slug,
        ]);

        // Start monitoring queries
        $this->queryMonitor->startMonitoring(50); // Allow up to 50 queries for Livewire component with pagination

        // Test the CategoryShow component
        $component = Livewire::test(CategoryShow::class, [
            'category' => $category,
        ]);

        $component->assertOk();

        // Stop monitoring and analyze results
        $queryData = $this->queryMonitor->stopMonitoring();

        // Assert that we don't exceed the query budget
        $this->assertLessThanOrEqual(50, $queryData['total_queries'],
            'CategoryShow exceeded query budget. Queries: ' . $queryData['total_queries']);

        // Assert no actual N+1 patterns detected (ignore schema queries)
        $actualN1Patterns = array_filter($queryData['n1_patterns'], fn ($pattern) => $pattern['likely_n1'] === true);
        $this->assertEmpty($actualN1Patterns,
            'N+1 patterns detected in CategoryShow: ' . json_encode($actualN1Patterns));

        // Verify that products are loaded
        $this->assertNotEmpty($component->get('products'));
    }

    public function test_product_catalogue_eliminates_n1_patterns(): void
    {
        // Create test data
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create([
            'name'       => 'Test Category',
            'is_visible' => true,
        ]);

        // Create multiple products
        $products = Product::factory()
            ->count(20)
            ->create([
                'brand_id'     => $brand->id,
                'is_visible'   => true,
                'published_at' => now()->subDay(),
            ]);

        // Create translations and attach category to products
        foreach ($products as $product) {
            $product->translations()->create([
                'locale'            => 'en',
                'name'              => $product->name,
                'slug'              => $product->slug,
                'short_description' => $product->short_description,
            ]);
            $product->categories()->attach($category->id);
        }

        // Create translations for brand and category
        $brand->translations()->create([
            'locale' => 'en',
            'name'   => $brand->name,
            'slug'   => $brand->slug,
        ]);

        $category->translations()->create([
            'locale' => 'en',
            'name'   => $category->name,
            'slug'   => $category->slug,
        ]);

        // Start monitoring queries
        $this->queryMonitor->startMonitoring(25); // Allow up to 25 queries for pagination

        // Test the ProductCatalogue component
        $component = Livewire::test(ProductCatalogue::class, [
            'perPage' => 16,
        ]);

        $component->assertOk();

        // Stop monitoring and analyze results
        $queryData = $this->queryMonitor->stopMonitoring();

        // Assert that we don't exceed the query budget
        $this->assertLessThanOrEqual(25, $queryData['total_queries'],
            'ProductCatalogue exceeded query budget. Queries: ' . $queryData['total_queries']);

        // Assert no actual N+1 patterns detected (ignore schema queries)
        $actualN1Patterns = array_filter($queryData['n1_patterns'], fn ($pattern) => $pattern['likely_n1'] === true);
        $this->assertEmpty($actualN1Patterns,
            'N+1 patterns detected in ProductCatalogue: ' . json_encode($actualN1Patterns));

        // Verify that products are loaded
        $this->assertNotEmpty($component->get('products'));
    }

    public function test_product_list_item_data_does_not_trigger_lazy_loading(): void
    {
        // Create test data with all required relations
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create(['name' => 'Test Category']);

        $product = Product::factory()->create([
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        // Create translations
        $product->translations()->create([
            'locale'            => 'en',
            'name'              => $product->name,
            'slug'              => $product->slug,
            'short_description' => $product->short_description,
        ]);

        $brand->translations()->create([
            'locale' => 'en',
            'name'   => $brand->name,
            'slug'   => $brand->slug,
        ]);

        $category->translations()->create([
            'locale' => 'en',
            'name'   => $category->name,
            'slug'   => $category->slug,
        ]);

        $product->categories()->attach($category->id);

        // Create some reviews to ensure aggregates have data
        $product->reviews()->create([
            'rating'      => 5,
            'content'     => 'Great product!',
            'is_approved' => true,
        ]);
        $product->reviews()->create([
            'rating'      => 4,
            'content'     => 'Good product',
            'is_approved' => true,
        ]);

        // Load product with proper eager loading
        $loadedProduct = Product::query()
            ->forProductList()
            ->withListRelations()
            ->withAvg(['reviews as average_rating' => fn ($q) => $q->where('is_approved', true)], 'rating')
            ->withCount(['reviews' => fn ($q) => $q->where('is_approved', true)])
            ->find($product->id);

        $this->assertNotNull($loadedProduct);

        // Start monitoring to detect any lazy loading
        $this->queryMonitor->startMonitoring(1); // Should not trigger any additional queries

        // Convert to DTO - this should not trigger any database queries
        $dto = \App\Data\Storefront\Home\ProductListItemData::fromModel($loadedProduct, 'en');

        // Stop monitoring
        $queryData = $this->queryMonitor->stopMonitoring();

        // Assert no queries were executed during DTO conversion
        $this->assertEquals(0, $queryData['total_queries'],
            'ProductListItemData::fromModel triggered lazy loading. Queries: ' . $queryData['total_queries']);

        // Verify DTO has expected data
        $this->assertEquals($product->id, $dto->id);
        $this->assertEquals($product->name, $dto->name);
        $this->assertEquals($brand->name, $dto->brandName);
        $this->assertContains($category->name, $dto->categoryLabels);
    }

    public function test_with_list_relations_scope_loads_all_required_relations(): void
    {
        // Create test data
        $brand = Brand::factory()->create(['name' => 'Test Brand']);
        $category = Category::factory()->create(['name' => 'Test Category']);

        $product = Product::factory()->create([
            'brand_id'     => $brand->id,
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        // Create translations
        $product->translations()->create([
            'locale'            => 'en',
            'name'              => $product->name,
            'slug'              => $product->slug,
            'short_description' => $product->short_description,
        ]);

        $brand->translations()->create([
            'locale' => 'en',
            'name'   => $brand->name,
            'slug'   => $brand->slug,
        ]);

        $category->translations()->create([
            'locale' => 'en',
            'name'   => $category->name,
            'slug'   => $category->slug,
        ]);

        $product->categories()->attach($category->id);

        // Load product with the scope
        $loadedProduct = Product::query()
            ->forProductList()
            ->withListRelations()
            ->find($product->id);

        $this->assertNotNull($loadedProduct);

        // Verify all required relations are loaded
        $this->assertTrue($loadedProduct->relationLoaded('brand'));
        $this->assertTrue($loadedProduct->relationLoaded('categories'));
        $this->assertTrue($loadedProduct->relationLoaded('translations'));
        $this->assertTrue($loadedProduct->relationLoaded('media'));

        // Verify nested relations are loaded
        if ($loadedProduct->brand) {
            $this->assertTrue($loadedProduct->brand->relationLoaded('translations'));
        }

        if ($loadedProduct->categories->isNotEmpty()) {
            $this->assertTrue($loadedProduct->categories->first()->relationLoaded('translations'));
        }
    }

    public function test_query_monitoring_service_detects_n1_patterns(): void
    {
        // Create test data
        $brand = Brand::factory()->create();
        $products = Product::factory()->count(5)->create(['brand_id' => $brand->id]);

        // Start monitoring
        $this->queryMonitor->startMonitoring(10);

        // Simulate N+1 pattern by loading products without eager loading
        $loadedProducts = Product::withoutGlobalScopes()->get();

        // This will trigger N+1 queries
        foreach ($loadedProducts as $product) {
            $brandName = $product->brand?->name; // Lazy loading
        }

        // Stop monitoring
        $queryData = $this->queryMonitor->stopMonitoring();

        // Should detect N+1 patterns
        $this->assertNotEmpty($queryData['n1_patterns'],
            'QueryMonitoringService should detect N+1 patterns');

        $this->assertGreaterThan(5, $queryData['total_queries'],
            'Should have more queries due to N+1 pattern');
    }
}
