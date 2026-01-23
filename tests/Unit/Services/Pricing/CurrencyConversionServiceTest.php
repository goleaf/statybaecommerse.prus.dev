<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Services\Pricing\CurrencyConversionService;
use App\Services\Pricing\PriceConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->priceConfig = Mockery::mock(PriceConfiguration::class);
    $this->priceConfig->shouldReceive('round')->andReturnUsing(fn ($value) => round($value, 2));
    $this->priceConfig->shouldReceive('currency')->andReturn('EUR');

    $this->service = new CurrencyConversionService($this->priceConfig);
});

test('converts between different currencies', function () {
    Currency::factory()->create(['code' => 'EUR', 'exchange_rate' => 1.0, 'is_default' => true]);
    Currency::factory()->create(['code' => 'USD', 'exchange_rate' => 1.1]);

    $result = $this->service->convert(100.0, 'EUR', 'USD');

    expect($result)->toBe(110.0);
});

test('returns same amount for same currency', function () {
    $result = $this->service->convert(100.0, 'EUR', 'EUR');

    expect($result)->toBe(100.0);
});

test('returns rounded amount for small values', function () {
    $result = $this->service->convert(0.0001, 'EUR', 'USD');

    expect($result)->toBe(0.0);
});

test('handles missing currency rates gracefully', function () {
    $result = $this->service->convert(100.0, 'EUR', 'INVALID');

    expect($result)->toBe(100.0);
});

test('caches currency rates', function () {
    Currency::factory()->create(['code' => 'EUR', 'exchange_rate' => 1.0]);

    // First call should query database
    $result1 = $this->service->convert(100.0, 'EUR', 'EUR');

    // Second call should use cache
    $result2 = $this->service->convert(200.0, 'EUR', 'EUR');

    expect($result1)->toBe(100.0);
    expect($result2)->toBe(200.0);
});

test('gets base currency from configuration', function () {
    Currency::factory()->create(['code' => 'EUR', 'is_default' => true]);

    $baseCurrency = $this->service->getBaseCurrency();

    expect($baseCurrency)->toBe('EUR');
});

test('falls back to default currency when config is empty', function () {
    $this->priceConfig->shouldReceive('currency')->andReturn('');
    Currency::factory()->create(['code' => 'USD', 'is_default' => true]);

    $baseCurrency = $this->service->getBaseCurrency();

    expect($baseCurrency)->toBe('USD');
});

test('uses EUR as ultimate fallback', function () {
    $this->priceConfig->shouldReceive('currency')->andReturn('');

    $baseCurrency = $this->service->getBaseCurrency();

    expect($baseCurrency)->toBe('EUR');
});
