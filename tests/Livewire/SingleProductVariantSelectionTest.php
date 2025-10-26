<?php

declare(strict_types=1);

namespace Tests\Livewire;

use App\Livewire\Components\ProductImageGallery;
use App\Livewire\Pages\SingleProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SingleProductVariantSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_selection_updates_pricing_summary(): void
    {
        $product = Product::factory()->create([
            'type'         => 'variable',
            'is_visible'   => true,
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $defaultVariant = ProductVariant::factory()->for($product)->create([
            'price'              => 49.99,
            'compare_price'      => 59.99,
            'is_default_variant' => true,
            'is_default'         => true,
        ]);

        $alternativeVariant = ProductVariant::factory()->for($product)->create([
            'price'              => 89.50,
            'compare_price'      => 109.50,
            'is_default_variant' => false,
            'is_default'         => false,
        ]);

        $component = Livewire::test(SingleProduct::class, ['product' => $product]);

        $this->assertSame($defaultVariant->id, $component->get('activeVariantId'));

        $initialPricing = $component->get('pricingSummary');
        $this->assertEquals((float) $defaultVariant->price, $initialPricing['current']);
        $this->assertEquals((float) $defaultVariant->compare_price, $initialPricing['compare']);

        $component->dispatch('variant.selected', variantId: $alternativeVariant->id);

        $this->assertSame($alternativeVariant->id, $component->get('activeVariantId'));

        $updatedPricing = $component->get('pricingSummary');
        $this->assertEquals((float) $alternativeVariant->price, $updatedPricing['current']);
        $this->assertEquals((float) $alternativeVariant->compare_price, $updatedPricing['compare']);
    }

    public function test_gallery_switches_to_variant_images(): void
    {
        $product = Product::factory()->create([
            'type'         => 'variable',
            'is_visible'   => true,
            'status'       => 'published',
            'published_at' => now(),
        ]);

        $variant = ProductVariant::factory()->for($product)->create([
            'is_default_variant' => true,
            'is_default'         => true,
        ]);

        VariantImage::factory()->for($variant, 'variant')->create([
            'image_path' => 'products/sample.jpg',
            'alt_text'   => 'Variant Primary',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        $gallery = Livewire::test(ProductImageGallery::class, ['product' => $product]);

        $gallery->dispatch('variant.selected', variantId: $variant->id);

        $this->assertSame($variant->id, $gallery->get('activeVariantId'));

        $images = $gallery->instance()->images();
        $this->assertNotEmpty($images);
        $this->assertStringContainsString('sample.jpg', $images[0]['original']);
    }
}
