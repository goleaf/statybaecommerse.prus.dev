<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Currency::factory()->create(['id' => 1, 'code' => 'EUR']);
    }

    /** @test */
    public function it_can_belong_to_a_product_morphically(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        
        $price = Price::factory()->create([
            'priceable_id'   => $product->id,
            'priceable_type' => Product::class,
        ]);

        $this->assertInstanceOf(Product::class, $price->priceable);
        $this->assertEquals($product->id, $price->priceable->id);
    }

    /** @test */
    public function it_can_belong_to_a_product_variant_morphically(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'name' => 'Test Variant']);
        
        $price = Price::factory()->create([
            'priceable_id'   => $variant->id,
            'priceable_type' => ProductVariant::class,
        ]);

        $this->assertInstanceOf(ProductVariant::class, $price->priceable);
        $this->assertEquals($variant->id, $price->priceable->id);
    }

    /** @test */
    public function it_has_a_working_product_relationship_helper(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        
        $price = Price::factory()->create([
            'priceable_id'   => $product->id,
            'priceable_type' => Product::class,
        ]);

        // This relationship was previously broken by an incorrect whereIn clause.
        // It should now correctly return the related product.
        $this->assertNotNull($price->product);
        $this->assertEquals($product->id, $price->product->id);
    }

    /** @test */
    public function it_can_eager_load_product_relationship_without_sql_error(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);
        
        Price::factory()->create([
            'priceable_id'   => $product->id,
            'priceable_type' => Product::class,
        ]);

        // This would have thrown "no such column: prices.priceable_type" before the fix.
        $prices = Price::with('product')->get();

        $this->assertCount(1, $prices);
        $this->assertTrue($prices->first()->relationLoaded('product'));
        $this->assertEquals($product->id, $prices->first()->product->id);
    }
}
