<?php

declare(strict_types=1);

use App\Livewire\ProductVariantSelector;
use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Livewire;

describe('ProductVariantSelector', function (): void {
    it('feature: selects the requested variant and resets dependent state when the variantSelected event is received', function (): void {
        $product = Product::factory()->create([
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        $variants = ProductVariant::factory()
            ->count(2)
            ->for($product)
            ->create();

        $component = Livewire::test(ProductVariantSelector::class, ['product' => $product]);

        $component->set('quantity', 3);

        $selectedVariant = $variants->last();

        $component->dispatch('variantSelected', $selectedVariant->id);

        $component
            ->assertSet('selectedVariant.id', $selectedVariant->id)
            ->assertSet('selectedAttributes', [])
            ->assertSet('showVariantDetails', true)
            ->assertSet('quantity', 1)
            ->assertSet('selectedVariantPricing.final', (float) $selectedVariant->price);

        $component->dispatch('variantSelected', null);

        $component
            ->assertSet('selectedVariant', null)
            ->assertSet('selectedAttributes', [])
            ->assertSet('showVariantDetails', false)
            ->assertSet('quantity', 1)
            ->assertSet('selectedVariantPricing.final', null);
    });

    it('does not render the selected variant description inside the add to cart card', function (): void {
        app()->setLocale('lt');

        $product = Product::factory()->create([
            'status'       => 'published',
            'is_visible'   => true,
            'published_at' => now(),
        ]);

        $variant = ProductVariant::factory()
            ->for($product)
            ->create([
                'is_default_variant' => true,
                'is_default'         => true,
            ]);

        $sidebarDescription = 'Šis aprašymas neturi būti rodomas dešinėje pirkimo kortelėje.';

        $variant->translations()
            ->where('locale', 'lt')
            ->update(['description' => $sidebarDescription]);

        Livewire::test(ProductVariantSelector::class, ['product' => $product])
            ->assertDontSee($sidebarDescription)
            ->assertDontSee(__('product.variants.fields.description'));
    });
});
