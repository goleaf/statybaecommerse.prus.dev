<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_can_be_created(): void
    {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'description' => 'Test brand description',
            'website' => 'https://testbrand.com',
            'is_enabled' => true,
        ]);

        $this->assertInstanceOf(Brand::class, $brand);
        $this->assertEquals('Test Brand', $brand->name);
        $this->assertEquals('test-brand', $brand->slug);
        $this->assertEquals('Test brand description', $brand->description);
        $this->assertEquals('https://testbrand.com', $brand->website);
        $this->assertTrue($brand->is_enabled);
    }

    public function test_brand_translation_methods(): void
    {
        $brand = Brand::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);
        
        // Test translation methods
        $this->assertEquals('Original Name', $brand->getTranslatedName());
        $this->assertEquals('Original Description', $brand->getTranslatedDescription());
        $this->assertEquals($brand->slug, $brand->getTranslatedSlug());
        
        // Test translation methods with locale parameter
        $this->assertEquals('Original Name', $brand->getTranslatedName('en'));
        $this->assertEquals('Original Description', $brand->getTranslatedDescription('en'));
    }

    public function test_brand_scopes(): void
    {
        // Clear any existing brands first
        Brand::query()->delete();
        Product::query()->delete();

        // Create test brands with specific attributes
        $enabledBrand = Brand::factory()->create(['is_enabled' => true]);
        $disabledBrand = Brand::factory()->create(['is_enabled' => false]);
        $brandWithProducts = Brand::factory()->create(['is_enabled' => false]); // Make sure it's not enabled
        $brandWithWebsite = Brand::factory()->create(['website' => 'https://example.com', 'is_enabled' => false]);

        // Create products for the brand
        Product::factory()->count(3)->create(['brand_id' => $brandWithProducts->id]);

        // Test enabled scope
        $enabledBrands = Brand::enabled()->get();
        $this->assertGreaterThanOrEqual(1, $enabledBrands->count());
        $this->assertTrue($enabledBrands->contains('id', $enabledBrand->id));

        // Test with products scope
        $brandsWithProducts = Brand::withProducts()->get();
        $this->assertGreaterThanOrEqual(1, $brandsWithProducts->count());
        $this->assertTrue($brandsWithProducts->contains('id', $brandWithProducts->id));

        // Test active scope (alias for enabled)
        $activeBrands = Brand::active()->get();
        $this->assertGreaterThanOrEqual(1, $activeBrands->count());
        $this->assertTrue($activeBrands->contains('id', $enabledBrand->id));

        // Test with website scope
        $brandsWithWebsite = Brand::withWebsite()->get();
        $this->assertGreaterThanOrEqual(1, $brandsWithWebsite->count());
        $this->assertTrue($brandsWithWebsite->contains('id', $brandWithWebsite->id));
    }

    public function test_brand_helper_methods(): void
    {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'website' => 'https://testbrand.com',
            'is_enabled' => true,
        ]);

        // Test info methods
        $brandInfo = $brand->getBrandInfo();
        $this->assertArrayHasKey('id', $brandInfo);
        $this->assertArrayHasKey('name', $brandInfo);
        $this->assertArrayHasKey('slug', $brandInfo);

        $mediaInfo = $brand->getMediaInfo();
        $this->assertArrayHasKey('has_logo', $mediaInfo);
        $this->assertArrayHasKey('has_banner', $mediaInfo);
        $this->assertArrayHasKey('logo_url', $mediaInfo);

        $seoInfo = $brand->getSeoInfo();
        $this->assertArrayHasKey('seo_title', $seoInfo);
        $this->assertArrayHasKey('canonical_url', $seoInfo);
        $this->assertArrayHasKey('meta_tags', $seoInfo);

        $businessInfo = $brand->getBusinessInfo();
        $this->assertArrayHasKey('products_count', $businessInfo);
        $this->assertArrayHasKey('is_active', $businessInfo);
        $this->assertArrayHasKey('has_products', $businessInfo);

        $completeInfo = $brand->getCompleteInfo();
        $this->assertArrayHasKey('translations', $completeInfo);
        $this->assertArrayHasKey('has_translations', $completeInfo);
    }

    public function test_brand_status_methods(): void
    {
        $enabledBrand = Brand::factory()->create(['is_enabled' => true]);
        $disabledBrand = Brand::factory()->create(['is_enabled' => false]);

        // Test isActive method
        $this->assertTrue($enabledBrand->isActive());
        $this->assertFalse($disabledBrand->isActive());
    }

    public function test_brand_product_methods(): void
    {
        $brand = Brand::factory()->create();
        
        // Test hasProducts method (initially false)
        $this->assertFalse($brand->hasProducts());
        $this->assertFalse($brand->hasPublishedProducts());

        // Create products for the brand
        Product::factory()->count(2)->create(['brand_id' => $brand->id, 'status' => 'draft']);
        Product::factory()->create([
            'brand_id' => $brand->id, 
            'status' => 'published',
            'is_visible' => true,
            'published_at' => now()->subDay()
        ]);

        // Refresh the brand to get updated relations
        $brand->refresh();

        // Test hasProducts method (now true)
        $this->assertTrue($brand->hasProducts());
        $this->assertTrue($brand->hasPublishedProducts());

        // Test products count
        $this->assertEquals(3, $brand->products()->count());
        $this->assertGreaterThanOrEqual(1, $brand->products()->published()->count());
    }

    public function test_brand_website_methods(): void
    {
        $brandWithWebsite = Brand::factory()->create(['website' => 'https://example.com']);
        $brandWithoutWebsite = Brand::factory()->create(['website' => null]);

        // Test hasWebsite method
        $this->assertTrue($brandWithWebsite->hasWebsite());
        $this->assertFalse($brandWithoutWebsite->hasWebsite());

        // Test getWebsiteDomain method
        $this->assertEquals('example.com', $brandWithWebsite->getWebsiteDomain());
        $this->assertNull($brandWithoutWebsite->getWebsiteDomain());
    }

    public function test_brand_media_methods(): void
    {
        $brand = Brand::factory()->create();

        // Test hasAnyMedia method (initially false)
        $this->assertFalse($brand->hasAnyMedia());

        // Test media info
        $mediaInfo = $brand->getMediaInfo();
        $this->assertArrayHasKey('has_logo', $mediaInfo);
        $this->assertArrayHasKey('has_banner', $mediaInfo);
        $this->assertArrayHasKey('logo_urls', $mediaInfo);
        $this->assertArrayHasKey('banner_urls', $mediaInfo);
    }

    public function test_brand_seo_methods(): void
    {
        $brand = Brand::factory()->create([
            'seo_title' => 'SEO Title',
            'seo_description' => 'SEO Description',
        ]);

        // Test getMetaTags method
        $metaTags = $brand->getMetaTags();
        $this->assertArrayHasKey('title', $metaTags);
        $this->assertArrayHasKey('description', $metaTags);
        $this->assertArrayHasKey('og:title', $metaTags);
        $this->assertArrayHasKey('og:description', $metaTags);
        $this->assertEquals('SEO Title', $metaTags['title']);
        $this->assertEquals('SEO Description', $metaTags['description']);
    }

    public function test_brand_translation_management(): void
    {
        $brand = Brand::factory()->create([
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        // Test available locales (should be empty initially)
        $this->assertEmpty($brand->getAvailableLocales());

        // Test has translation for
        $this->assertFalse($brand->hasTranslationFor('en'));

        // Test get or create translation
        $translation = $brand->getOrCreateTranslation('en');
        $this->assertInstanceOf(\App\Models\Translations\BrandTranslation::class, $translation);
        $this->assertEquals('en', $translation->locale);

        // Test update translation
        $this->assertTrue($brand->updateTranslation('en', [
            'name' => 'English Name',
            'description' => 'English Description',
        ]));

        // Test available locales now includes 'en'
        $this->assertContains('en', $brand->getAvailableLocales());
        $this->assertTrue($brand->hasTranslationFor('en'));
    }

    public function test_brand_full_display_name(): void
    {
        $enabledBrand = Brand::factory()->create(['name' => 'Test Brand', 'is_enabled' => true]);
        $disabledBrand = Brand::factory()->create(['name' => 'Disabled Brand', 'is_enabled' => false]);

        $enabledDisplayName = $enabledBrand->getFullDisplayName();
        $this->assertStringContainsString('Test Brand', $enabledDisplayName);
        $this->assertStringContainsString('Enabled', $enabledDisplayName);

        $disabledDisplayName = $disabledBrand->getFullDisplayName();
        $this->assertStringContainsString('Disabled Brand', $disabledDisplayName);
        $this->assertStringContainsString('Disabled', $disabledDisplayName);
    }

    public function test_brand_additional_scopes(): void
    {
        // Clear any existing brands first
        Brand::query()->delete();

        // Create test brands
        $recentBrand = Brand::factory()->create(['created_at' => now()->subDays(15)]);
        $oldBrand = Brand::factory()->create(['created_at' => now()->subDays(45)]);
        $brandWithWebsite = Brand::factory()->create(['website' => 'https://example.com', 'created_at' => now()->subDays(60)]);
        $brandWithoutWebsite = Brand::factory()->create(['website' => null, 'created_at' => now()->subDays(60)]);

        // Test recent scope
        $recentBrands = Brand::recent(30)->get();
        $this->assertCount(1, $recentBrands);
        $this->assertEquals($recentBrand->id, $recentBrands->first()->id);

        // Test with website scope
        $brandsWithWebsite = Brand::withWebsite()->get();
        $this->assertGreaterThanOrEqual(1, $brandsWithWebsite->count());
        $this->assertTrue($brandsWithWebsite->contains('id', $brandWithWebsite->id));

        // Test without website scope
        $brandsWithoutWebsite = Brand::withoutWebsite()->get();
        $this->assertGreaterThanOrEqual(1, $brandsWithoutWebsite->count());
        $this->assertTrue($brandsWithoutWebsite->contains('id', $brandWithoutWebsite->id));
    }

    public function test_brand_relations(): void
    {
        $brand = Brand::factory()->create();

        // Test products relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $brand->products());
    }

    public function test_brand_route_key_name(): void
    {
        $brand = Brand::factory()->create(['slug' => 'test-brand']);

        // Test route key name
        $this->assertEquals('slug', $brand->getRouteKeyName());
    }

    public function test_brand_cache_flushing(): void
    {
        // Test that cache flushing method exists and is callable
        $this->assertTrue(method_exists(Brand::class, 'flushCaches'));
        
        // This is a static method, so we just verify it exists
        $this->assertTrue(is_callable([Brand::class, 'flushCaches']));
    }
}