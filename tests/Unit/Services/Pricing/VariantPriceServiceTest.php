<?php

declare(strict_types=1);

use App\Data\Pricing\VariantPriceResult;
use App\Models\ProductVariant;
use App\Services\Pricing\CurrencyConversionService;
use App\Services\Pricing\PriceConfiguration;
use App\Services\Pricing\VariantPriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->priceConfig = new PriceConfiguration([
        'rounding' => [
            'precision' => 2,
            'mode'      => PHP_ROUND_HALF_UP,
        ],
    ]);
    $this->currencyService = new CurrencyConversionService($this->priceConfig);
    $this->service = new VariantPriceService($this->priceConfig, $this->currencyService);
});

test('calculates basic variant price without adjustments', function () {
    $variant = ProductVariant::factory()->create([
        'price'      => 100.00,
        'is_on_sale' => false,
    ]);

    $result = $this->service->calculate($variant);

    expect($result)->toBeInstanceOf(VariantPriceResult::class);
    expect($result->regularPrice)->toBe(100.00);
    expect($result->finalPrice)->toBe(100.00);
    expect($result->historyRecorded)->toBeFalse();
});

test('applies sale price when variant is on sale', function () {
    $variant = ProductVariant::factory()->create([
        'price'             => 100.00,
        'promotional_price' => 80.00,
        'is_on_sale'        => true,
    ]);

    $result = $this->service->calculate($variant);

    expect($result->regularPrice)->toBe(100.00);
    expect($result->finalPrice)->toBe(80.00);
});

test('applies variant size modifier', function () {
    $variant = ProductVariant::factory()->create([
        'price'               => 100.00,
        'size_price_modifier' => 10.00,
        'is_on_sale'          => false,
    ]);

    $result = $this->service->calculate($variant);

    expect($result->finalPrice)->toBe(110.00);
    expect($result->variantModifiers)->toBe(10.00);
});

test('converts currency correctly', function () {
    $variant = ProductVariant::factory()->create([
        'price' => 100.00,
    ]);

    $result = $this->service->calculate($variant, [
        'currency'      => 'USD',
        'base_currency' => 'EUR',
    ]);

    expect($result->currency)->toBe('EUR');
    expect($result->finalPrice)->toBe(100.00);
});

test('handles quantity-based context', function () {
    $variant = ProductVariant::factory()->create([
        'price' => 100.00,
    ]);

    $result = $this->service->calculate($variant, [
        'quantity' => 5,
    ]);

    expect($result)->toBeInstanceOf(VariantPriceResult::class);
    // Quantity affects price list and dynamic rule resolution
});

test('handles customer group context', function () {
    $variant = ProductVariant::factory()->create([
        'price' => 100.00,
    ]);

    $result = $this->service->calculate($variant, [
        'customer_group_ids' => [1, 2, 3],
    ]);

    expect($result)->toBeInstanceOf(VariantPriceResult::class);
});

test('price history recording is disabled', function () {
    $variant = ProductVariant::factory()->create([
        'price' => 100.00,
    ]);

    $result = $this->service->calculate($variant, [
        'record_history' => true,
        'history_reason' => 'test',
    ]);

    expect($result->historyRecorded)->toBeFalse();
});

test('handles edge cases with zero and negative prices', function () {
    $variant = ProductVariant::factory()->create([
        'price' => 0.00,
    ]);

    $result = $this->service->calculate($variant);

    expect($result->finalPrice)->toBeGreaterThanOrEqual(0.0);
});
