<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductTest extends TestCase
{
    use RefreshDatabase;

    private function createTestBrand(): Brand
    {
        return Brand::factory()->create([
            'name' => 'Test Brand',
        ]);
    }

    private function createTestCategory(): Category
    {
        return Category::factory()->create([
            'name' => 'Test Category',
        ]);
    }

    public function test_product_can_be_created(): void
    {
        $brand = $this->createTestBrand();
        $category = $this->createTestCategory();

        $product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST001',
            'description' => 'Test description',
            'price' => 99.99,
            'sale_price' => 79.99,
            'brand_id' => $brand->id,
            'is_visible' => true,
            'is_featured' => false,
            'status' => 'published',
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('test-product', $product->slug);
        $this->assertEquals('TEST001', $product->sku);
        $this->assertEquals('Test description', $product->description);
        $this->assertEquals(99.99, $product->price);
        $this->assertEquals(79.99, $product->sale_price);
        $this->assertEquals($brand->id, $product->brand_id);
        $this->assertTrue($product->is_visible);
        $this->assertFalse($product->is_featured);
        $this->assertEquals('published', $product->status);
    }

    public function test_product_translation_methods(): void
    {
        $product = Product::factory()->create(['name' => 'Original Name']);
        
        // Test translation methods
        $this->assertEquals('Original Name', $product->getTranslatedName());
        $this->assertEquals($product->description, $product->getTranslatedDescription());
        $this->assertEquals($product->slug, $product->getTranslatedSlug());
        
        // Test with translation
        $product->updateTranslation('en', [
            'name' => 'English Name',
            'description' => 'English Description',
            'slug' => 'english-slug',
        ]);
        
        $this->assertEquals('English Name', $product->getTranslatedName('en'));
        $this->assertEquals('English Description', $product->getTranslatedDescription('en'));
        $this->assertEquals('english-slug', $product->getTranslatedSlug('en'));
    }

    public function test_product_scopes(): void
    {
        // Clear any existing products first
        Product::query()->delete();

        // Create test products with specific attributes
        $publishedProduct = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $draftProduct = Product::factory()->create(['status' => 'draft']);
        $featuredProduct = Product::factory()->create(['is_featured' => true]);
        $visibleProduct = Product::factory()->create(['is_visible' => true]);
        $hiddenProduct = Product::factory()->create(['is_visible' => false]);

        // Test published scope
        $publishedProducts = Product::published()->get();
        $this->assertCount(1, $publishedProducts);
        $this->assertEquals($publishedProduct->id, $publishedProducts->first()->id);

        // Test featured scope
        $featuredProducts = Product::featured()->get();
        $this->assertCount(1, $featuredProducts);
        $this->assertEquals($featuredProduct->id, $featuredProducts->first()->id);

        // Test visible scope
        $visibleProducts = Product::visible()->get();
        $this->assertCount(2, $visibleProducts); // publishedProduct and visibleProduct
    }

    public function test_product_helper_methods(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'price' => 100.00,
            'sale_price' => 80.00,
            'cost_price' => 60.00,
            'length' => 10.0,
            'width' => 5.0,
            'height' => 2.0,
        ]);

        // Test info methods
        $productInfo = $product->getProductInfo();
        $this->assertArrayHasKey('id', $productInfo);
        $this->assertArrayHasKey('name', $productInfo);
        $this->assertArrayHasKey('sku', $productInfo);

        $inventoryInfo = $product->getInventoryInfo();
        $this->assertArrayHasKey('stock_quantity', $inventoryInfo);
        $this->assertArrayHasKey('stock_status', $inventoryInfo);
        $this->assertArrayHasKey('is_in_stock', $inventoryInfo);

        $pricingInfo = $product->getPricingInfo();
        $this->assertArrayHasKey('price', $pricingInfo);
        $this->assertArrayHasKey('sale_price', $pricingInfo);
        $this->assertArrayHasKey('current_price', $pricingInfo);

        $physicalInfo = $product->getPhysicalInfo();
        $this->assertArrayHasKey('weight', $physicalInfo);
        $this->assertArrayHasKey('dimensions', $physicalInfo);

        $completeInfo = $product->getCompleteInfo();
        $this->assertArrayHasKey('translations', $completeInfo);
        $this->assertArrayHasKey('has_translations', $completeInfo);
    }

    public function test_product_pricing_calculations(): void
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'sale_price' => 80.00,
            'cost_price' => 60.00,
        ]);

        // Test discount percentage
        $discountPercentage = $product->getDiscountPercentage();
        $this->assertEquals(20.0, $discountPercentage);

        // Test profit margin
        $profitMargin = $product->getProfitMargin();
        $this->assertEquals(40.0, $profitMargin);

        // Test markup percentage
        $markupPercentage = $product->getMarkupPercentage();
        $this->assertEquals(66.67, $markupPercentage, '', 0.01);
    }

    public function test_product_physical_calculations(): void
    {
        $product = Product::factory()->create([
            'length' => 10.0,
            'width' => 5.0,
            'height' => 2.0,
        ]);

        // Test dimensions
        $dimensions = $product->getDimensions();
        $this->assertEquals('10 × 5 × 2 cm', $dimensions);

        // Test volume
        $volume = $product->getVolume();
        $this->assertEquals(0.0001, $volume, '', 0.00001);
    }

    public function test_product_stock_methods(): void
    {
        $product = Product::factory()->create([
            'manage_stock' => true,
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
        ]);

        // Test stock status
        $this->assertTrue($product->isInStock());
        $this->assertFalse($product->isLowStock());
        $this->assertFalse($product->isOutOfStock());
        $this->assertEquals('in_stock', $product->getStockStatus());

        // Test low stock
        $product->update(['stock_quantity' => 3]);
        $this->assertTrue($product->isLowStock());
        $this->assertEquals('low_stock', $product->getStockStatus());

        // Test out of stock
        $product->update(['stock_quantity' => 0]);
        $this->assertTrue($product->isOutOfStock());
        $this->assertEquals('out_of_stock', $product->getStockStatus());
    }

    public function test_product_relations(): void
    {
        $product = Product::factory()->create();

        // Test brand relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $product->brand());

        // Test categories relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $product->categories());

        // Test collections relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $product->collections());

        // Test variants relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->variants());

        // Test reviews relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->reviews());

        // Test images relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->images());

        // Test order items relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->orderItems());
    }

    public function test_product_translation_management(): void
    {
        $product = Product::factory()->create();

        // Test available locales (should be empty initially)
        $this->assertEmpty($product->getAvailableLocales());

        // Test has translation for
        $this->assertFalse($product->hasTranslationFor('en'));

        // Test get or create translation
        $translation = $product->getOrCreateTranslation('en');
        $this->assertInstanceOf(\App\Models\Translations\ProductTranslation::class, $translation);
        $this->assertEquals('en', $translation->locale);

        // Test update translation
        $this->assertTrue($product->updateTranslation('en', [
            'name' => 'English Name',
            'description' => 'English Description',
        ]));

        // Test available locales now includes 'en'
        $this->assertContains('en', $product->getAvailableLocales());
        $this->assertTrue($product->hasTranslationFor('en'));
    }

    public function test_product_full_display_name(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
        ]);

        $displayName = $product->getFullDisplayName();
        $this->assertEquals('Test Product (TEST001)', $displayName);

        // Test without SKU
        $product->update(['sku' => null]);
        $displayName = $product->getFullDisplayName();
        $this->assertEquals('Test Product', $displayName);
    }

    public function test_product_published_status(): void
    {
        $product = Product::factory()->create([
            'is_visible' => true,
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->assertTrue($product->isPublished());

        // Test not published - not visible
        $product->update(['is_visible' => false]);
        $this->assertFalse($product->isPublished());

        // Test not published - wrong status
        $product->update(['is_visible' => true, 'status' => 'draft']);
        $this->assertFalse($product->isPublished());

        // Test not published - future date
        $product->update(['status' => 'published', 'published_at' => now()->addDay()]);
        $this->assertFalse($product->isPublished());
    }
}