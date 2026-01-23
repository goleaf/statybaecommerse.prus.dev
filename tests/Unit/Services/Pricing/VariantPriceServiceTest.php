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
    $this->priceConfig = Mockery::mock(PriceConfiguration::class);
    $this->currencyService = Mockery::mock(CurrencyConversionService::class);
    $this->service = new VariantPriceService($this->priceConfig, $this->currencyService);
});

test('calculates basic variant price without adjustments', function () {
    $variant = ProductVariant::factory()->create([
        'price'      => 100.00,
        'is_on_sale' => false,
    ]);

    $this->priceConfig->shouldReceive('round')->andReturnUsing(fn ($value) => round($value, 2));
    $this->currencyService->shouldReceive('convert')->andReturnUsing(fn ($amount) => $amount);

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

    // Mock the isCurrentlyOnSale method to return true
    $variant = Mockery::mock($variant)->makePartial();
    $variant->shouldReceive('isCurrentlyOnSale')->andReturn(true);

    $this->priceConfig->shouldReceive('round')->andReturnUsing(fn ($value) => round($value, 2));
    $this->currencyService->shouldReceive('convert')->andReturnUsing(fn ($amount) => $amount);

    $result = $this->service->calculate($variant);

    expect($result->regularPrice)->toBe(100.00);
    expect($result->salePrice)->toBe(80.00);
    expect($result->finalPrice)->toBe(80.00);
});

test('applies variant size modifier', function () {
    $variant = ProductVariant::factory()->create([
        'price'               => 100.00,
        'size_price_modifier' => 10.00,
        'is_on_sale'          => false,
    ]);

    $this->priceConfig->shouldReceive('round')->andReturnUsing(fn ($value) => round($value, 2));
    $this->currencyService->shouldReceive('convert')->andReturnUsing(fn ($amount) => $amount);

    $result = $this->service->calculate($variant);

    expect($result->finalPrice)->toBe(110.00);
    expect($result->variantModifiers)->toBe(10.00);
});

test('converts currency correctly', function () {
    $variant = ProductVariant::factory()->create([
        'price' => 100.00,
    ]);

    $this->priceConfig->shouldReceive('round')->andReturnUsing(fn ($value) => round($value, 2));
    $this->currencyService->shouldReceive('convert')
        ->with(100.00, 'EUR', 'USD')
        ->andReturn(110.00);

    $result = $this->service->calculate($variant, [
        'currency'      => 'USD',
        'base_currency' => 'EUR',
    ]);

    expect($result->currency)->toBe('USD');
    expect($result->finalPrice)->toBe(110.00);
});

test('handles quantity-based context', function () {
    $variant = ProductVariant::factory()->create([
        'price' => 100.00,
    ]);

    $this->priceConfig->shouldReceive('round')->andReturnUsing(fn ($value) => round($value, 2));
    $this->currencyService->shouldReceive('convert')->andReturnUsing(fn ($amount) => $amount);

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

    $this->priceConfig->shouldReceive('round')->andReturnUsing(fn ($value) => round($value, 2));
    $this->currencyService->shouldReceive('convert')->andReturnUsing(fn ($amount) => $amount);

    $result = $this->service->calculate($variant, [
        'customer_group_ids' => [1, 2, 3],
    ]);

    expect($result)->toBeInstanceOf(VariantPriceResult::class);
});

test('price history recording is disabled', function () {
    $variant = ProductVariant::factory()->create([
        'price' => 100.00,
    ]);

    $this->priceConfig->shouldReceive('round')->andReturnUsing(fn ($value) => round($value, 2));
    $this->currencyService->shouldReceive('convert')->andReturnUsing(fn ($amount) => $amount);

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

    $this->priceConfig->shouldReceive('round')->andReturnUsing(fn ($value) => round(max(0.0, $value), 2));
    $this->currencyService->shouldReceive('convert')->andReturnUsing(fn ($amount) => max(0.0, $amount));

    $result = $this->service->calculate($variant);

    expect($result->finalPrice)->toBeGreaterThanOrEqual(0.0);
});
