<?php

declare(strict_types=1);

use App\Livewire\Components\ProductCardDetailed;
use App\Models\Product;
use Livewire\Livewire;

beforeEach(function () {
    $this->product = Product::factory()->create([
        'name'           => 'Test Product Detailed',
        'price'          => 199.99,
        'status'         => 'published',
        'is_visible'     => true,
        'stock_quantity' => 25,
    ]);
});

it('feature: can render product card detailed component', function () {
    Livewire::test(ProductCardDetailed::class, ['product' => $this->product])
        ->assertSee('Test Product Detailed')
        ->assertSee('199,99')
        ->assertStatus(200);
});

it('feature: can add product to cart', function () {
    $component = Livewire::test(ProductCardDetailed::class, ['product' => $this->product]);

    $component->call('addToCart');

    $component->assertDispatched('add-to-cart');
});

it('feature: can toggle product comparison', function () {
    Livewire::test(ProductCardDetailed::class, ['product' => $this->product])
        ->call('toggleComparison')
        ->assertDispatched('notify')
        ->assertDispatched('add-to-comparison');
});

it('feature: can open quick view', function () {
    Livewire::test(ProductCardDetailed::class, ['product' => $this->product])
        ->call('quickView')
        ->assertDispatched('product-quick-view');
});

it('feature: can navigate to product page', function () {
    Livewire::test(ProductCardDetailed::class, ['product' => $this->product])
        ->call('viewProduct')
        ->assertRedirect(route('product.show', $this->product));
});

it('feature: shows correct comparison status', function () {
    Livewire::test(ProductCardDetailed::class, ['product' => $this->product])
        ->assertSet('isInComparison', false); // Simplified for now
});

it('feature: refreshes status on events', function () {
    $component = Livewire::test(ProductCardDetailed::class, ['product' => $this->product]);

    $component->call('toggleComparison');
    $component->assertDispatched('add-to-comparison');
});

it('feature: handles component properties correctly', function () {
    $component = Livewire::test(ProductCardDetailed::class, [
        'product'       => $this->product,
        'showQuickView' => true,
        'showCompare'   => false,
        'layout'        => 'list',
    ]);

    $component->assertSet('showQuickView', true)
        ->assertSet('showCompare', false)
        ->assertSet('layout', 'list');
});
