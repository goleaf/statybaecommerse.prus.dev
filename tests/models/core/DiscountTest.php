<?php

declare(strict_types=1);

use App\Models\Discount;
use App\Models\DiscountCondition;

it('can create a discount', function () {
    $discount = Discount::factory()->create([
        'name' => 'Summer Sale',
        'type' => 'percentage',
        'value' => 20.00,
        'starts_at' => now(),
        'ends_at' => now()->addDays(30),
        'is_active' => true,
    ]);

    expect($discount->name)->toBe('Summer Sale');
    expect($discount->type)->toBe('percentage');
    expect($discount->value)->toBe(20.00);
    expect($discount->is_active)->toBeTrue();
});

it('has conditions relationship', function () {
    $discount = Discount::factory()->create();
    $condition = DiscountCondition::factory()->create(['discount_id' => $discount->id]);

    expect($discount->conditions)->toHaveCount(1);
    expect($discount->conditions->first()->id)->toBe($condition->id);
});

it('can check if discount is currently active', function () {
    $activeDiscount = Discount::factory()->create([
        'starts_at' => now()->subDays(1),
        'ends_at' => now()->addDays(1),
        'is_active' => true,
    ]);

    $expiredDiscount = Discount::factory()->create([
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDays(1),
        'is_active' => true,
    ]);

    $inactiveDiscount = Discount::factory()->create([
        'starts_at' => now()->subDays(1),
        'ends_at' => now()->addDays(1),
        'is_active' => false,
    ]);

    expect($activeDiscount->isCurrentlyActive())->toBeTrue();
    expect($expiredDiscount->isCurrentlyActive())->toBeFalse();
    expect($inactiveDiscount->isCurrentlyActive())->toBeFalse();
});

it('can calculate discount amount for percentage type', function () {
    $discount = Discount::factory()->create([
        'type' => 'percentage',
        'value' => 20.00,
    ]);

    $discountAmount = $discount->calculateDiscountAmount(100.00);
    expect($discountAmount)->toBe(20.00);
});

it('can calculate discount amount for fixed type', function () {
    $discount = Discount::factory()->create([
        'type' => 'fixed',
        'value' => 15.00,
    ]);

    $discountAmount = $discount->calculateDiscountAmount(100.00);
    expect($discountAmount)->toBe(15.00);
});

it('can filter active discounts', function () {
    $activeDiscount = Discount::factory()->create(['is_active' => true]);
    $inactiveDiscount = Discount::factory()->create(['is_active' => false]);

    $activeDiscounts = Discount::where('is_active', true)->get();

    expect($activeDiscounts)->toHaveCount(1);
    expect($activeDiscounts->first()->id)->toBe($activeDiscount->id);
});

it('can filter current discounts', function () {
    $currentDiscount = Discount::factory()->create([
        'starts_at' => now()->subDays(1),
        'ends_at' => now()->addDays(1),
    ]);
    $futureDiscount = Discount::factory()->create([
        'starts_at' => now()->addDays(1),
        'ends_at' => now()->addDays(10),
    ]);
    $expiredDiscount = Discount::factory()->create([
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDays(1),
    ]);

    $currentDiscounts = Discount::where('starts_at', '<=', now())
        ->where(function ($query) {
            $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        })->get();

    expect($currentDiscounts)->toHaveCount(1);
    expect($currentDiscounts->first()->id)->toBe($currentDiscount->id);
});

it('tracks usage count', function () {
    $discount = Discount::factory()->create(['usage_count' => 0]);

    $discount->incrementUsage();
    expect($discount->fresh()->usage_count)->toBe(1);

    $discount->incrementUsage();
    expect($discount->fresh()->usage_count)->toBe(2);
});

it('can check if usage limit is reached', function () {
    $discount = Discount::factory()->create([
        'usage_limit' => 5,
        'usage_count' => 4,
    ]);

    expect($discount->isUsageLimitReached())->toBeFalse();

    $discount->update(['usage_count' => 5]);
    expect($discount->fresh()->isUsageLimitReached())->toBeTrue();
});
