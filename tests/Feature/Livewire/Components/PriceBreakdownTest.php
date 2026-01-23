<?php

declare(strict_types=1);

use App\Livewire\Components\PriceBreakdown;
use Livewire\Livewire;

test('price breakdown component renders correctly', function () {
    Livewire::test(PriceBreakdown::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.components.price-breakdown');
});

test('price breakdown shows all sections by default', function () {
    Livewire::test(PriceBreakdown::class)
        ->assertSet('showSubtotal', true)
        ->assertSet('showTaxes', true)
        ->assertSet('showTotal', true)
        ->assertSet('variant', 'default');
});

test('price breakdown can hide specific sections', function () {
    Livewire::test(PriceBreakdown::class, [
        'showSubtotal' => false,
        'showTaxes'    => true,
        'showTotal'    => false,
    ])
        ->assertSet('showSubtotal', false)
        ->assertSet('showTaxes', true)
        ->assertSet('showTotal', false);
});

test('price breakdown supports mobile variant', function () {
    Livewire::test(PriceBreakdown::class, [
        'variant' => 'mobile',
    ])
        ->assertSet('variant', 'mobile');
});

test('price breakdown returns zero subtotal when cart is empty', function () {
    Livewire::test(PriceBreakdown::class)
        ->assertSet('subtotal', 0.0);
});

test('price breakdown responds to cart updates', function () {
    $component = Livewire::test(PriceBreakdown::class);

    // Simulate cart update event
    $component->dispatch('cart-updated');

    $component->assertStatus(200);
});

test('price breakdown responds to coupon updates', function () {
    $component = Livewire::test(PriceBreakdown::class);

    // Simulate coupon update event
    $component->dispatch('coupon-updated');

    $component->assertStatus(200);
});
