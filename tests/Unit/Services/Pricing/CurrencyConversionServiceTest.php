<?php

declare(strict_types=1);

use App\Services\Pricing\CurrencyConversionService;
use App\Services\Pricing\PriceConfiguration;

test('keeps amount unchanged for non-eur currency pairs', function (): void {
    $service = new CurrencyConversionService(new PriceConfiguration([
        'currency' => 'EUR',
    ]));

    expect($service->convert(100.0, 'EUR', 'USD'))->toBe(100.0);
    expect($service->convert(100.0, 'USD', 'EUR'))->toBe(100.0);
    expect($service->convert(100.0, 'USD', 'GBP'))->toBe(100.0);
});

test('keeps amount unchanged for eur to eur conversion', function (): void {
    $service = new CurrencyConversionService(new PriceConfiguration([
        'currency' => 'EUR',
    ]));

    expect($service->convert(100.0, 'EUR', 'EUR'))->toBe(100.0);
});

test('rounds very small values according to configuration', function (): void {
    $service = new CurrencyConversionService(new PriceConfiguration([
        'currency' => 'EUR',
    ]));

    expect($service->convert(0.0001, 'EUR', 'USD'))->toBe(0.0);
});

test('always resolves eur as the base currency', function (): void {
    $eurService = new CurrencyConversionService(new PriceConfiguration([
        'currency' => 'EUR',
    ]));
    $usdConfiguredService = new CurrencyConversionService(new PriceConfiguration([
        'currency' => 'USD',
    ]));

    expect($eurService->getBaseCurrency())->toBe('EUR');
    expect($usdConfiguredService->getBaseCurrency())->toBe('EUR');
});
