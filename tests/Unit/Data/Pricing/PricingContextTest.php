<?php

declare(strict_types=1);

use App\Data\Pricing\PricingContext;
use Carbon\Carbon;

test('creates pricing context from array with defaults', function () {
    $context = PricingContext::fromArray([]);


    expect($context->quantity)->toBe(1);
    expect($context->customerGroupIds)->toBeEmpty();
    expect($context->recordHistory)->toBeFalse();
    expect($context->historyReason)->toBeNull();
});

test('creates pricing context with provided values', function () {
    $moment = Carbon::now();

    $context = PricingContext::fromArray([
        'quantity' => 5,
        'customer_group_ids' => [1, 2, 3],
        'currency' => 'USD',
        'base_currency' => 'EUR',
        'now' => $moment,
        'record_history' => true,
        'history_reason' => 'bulk_update',
        'history_price_type' => 'promotional',
        'changed_by' => 123,
    ]);

    expect($context->quantity)->toBe(5);
    expect($context->customerGroupIds->toArray())->toBe([1, 2, 3]);
    expect($context->targetCurrency)->toBe('USD');
    expect($context->baseCurrency)->toBe('EUR');
    expect($context->moment)->toBe($moment);
    expect($context->recordHistory)->toBeTrue();
    expect($context->historyReason)->toBe('bulk_update');
    expect($context->historyPriceType)->toBe('promotional');
    expect($context->changedBy)->toBe(123);
});

test('enforces minimum quantity of 1', function () {
    $context = PricingContext::fromArray(['quantity' => -5]);

    expect($context->quantity)->toBe(1);
});

test('filters null customer group ids', function () {
    $context = PricingContext::fromArray([
        'customer_group_ids' => [1, null, 2, null, 3],
    ]);

    expect($context->customerGroupIds->toArray())->toBe([1, 2, 3]);
});

test('handles empty currency strings', function () {
    $context = PricingContext::fromArray([
        'currency' => '',
        'base_currency' => 'EUR',
    ]);

    expect($context->targetCurrency)->toBe('EUR');
    expect($context->baseCurrency)->toBe('EUR');
});

test('normalizes currency codes to uppercase', function () {
    $context = PricingContext::fromArray([
        'currency' => 'usd',
        'base_currency' => 'eur',
    ]);

    expect($context->targetCurrency)->toBe('USD');
    expect($context->baseCurrency)->toBe('EUR');
});

test('creates moment from now when not provided', function () {
    $before = Carbon::now();
    $context = PricingContext::fromArray([]);
    $after = Carbon::now();

    expect($context->moment)->toBeInstanceOf(Carbon::class);
    expect($context->moment->between($before, $after))->toBeTrue();
});
