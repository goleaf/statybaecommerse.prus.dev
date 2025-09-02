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
            'is_enabled' => true,
        ]);

        $this->assertDatabaseHas('brands', [
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'is_enabled' => true,
        ]);
    }

    public function test_brand_has_many_products(): void
    {
        $brand = Brand::factory()->create();
        Product::factory()->count(5)->create(['brand_id' => $brand->id]);

        $this->assertCount(5, $brand->products);
        $this->assertInstanceOf(Product::class, $brand->products->first());
    }

    public function test_brand_cache_flush_on_save(): void
    {
        $brand = Brand::factory()->create();
        
        // Mock cache to test if flush is called
        \Illuminate\Support\Facades\Cache::shouldReceive('forget')
            ->with('sitemap:urls:en')
            ->once();

        $brand->save();
    }

    public function test_brand_soft_deletes(): void
    {
        $brand = Brand::factory()->create();
        $brandId = $brand->id;

        $brand->delete();

        $this->assertSoftDeleted('brands', ['id' => $brandId]);
        $this->assertNotNull($brand->fresh()->deleted_at);
    }

    public function test_brand_fillable_attributes(): void
    {
        $brand = new Brand();
        
        $expectedFillable = [
            'name',
            'slug',
            'description',
            'website',
            'is_enabled',
            'seo_title',
            'seo_description',
        ];

        $this->assertEquals($expectedFillable, $brand->getFillable());
    }

    public function test_brand_casts(): void
    {
        $brand = Brand::factory()->create(['is_enabled' => true]);

        $this->assertIsBool($brand->is_enabled);
    }

    public function test_brand_uses_correct_table(): void
    {
        $brand = new Brand();
        $this->assertEquals('brands', $brand->getTable());
    }
}