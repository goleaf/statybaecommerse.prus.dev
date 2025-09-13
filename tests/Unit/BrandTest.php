<?php declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_can_be_created(): void
    {
        $brand = Brand::factory()->create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
        ]);

        $this->assertDatabaseHas('brands', [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
        ]);
    }

    public function test_brand_has_many_products(): void
    {
        $brand = Brand::factory()->create();
        $products = Product::factory()->count(3)->create(['brand_id' => $brand->id]);

        $this->assertCount(3, $brand->products);
        $this->assertInstanceOf(Product::class, $brand->products->first());
    }

    public function test_brand_has_media_relationship(): void
    {
        $brand = Brand::factory()->create();

        // Test that brand implements HasMedia
        $this->assertInstanceOf(\Spatie\MediaLibrary\HasMedia::class, $brand);
        
        // Test that brand can handle media
        $this->assertTrue(method_exists($brand, 'registerMediaCollections'));
        $this->assertTrue(method_exists($brand, 'registerMediaConversions'));
        $this->assertTrue(method_exists($brand, 'media'));
    }

    public function test_brand_has_translations_relationship(): void
    {
        $brand = Brand::factory()->create();

        // Test that brand has translations relationship
        $this->assertTrue(method_exists($brand, 'translations'));
        $this->assertTrue(method_exists($brand, 'trans'));
    }

    public function test_brand_route_key_name(): void
    {
        $brand = new Brand();
        $this->assertEquals('slug', $brand->getRouteKeyName());
    }

    public function test_brand_casts_work_correctly(): void
    {
        $brand = Brand::factory()->create([
            'is_enabled' => true,
        ]);

        $this->assertIsBool($brand->is_enabled);
    }

    public function test_brand_fillable_attributes(): void
    {
        $brand = new Brand();
        $fillable = $brand->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('slug', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('is_enabled', $fillable);
    }
}