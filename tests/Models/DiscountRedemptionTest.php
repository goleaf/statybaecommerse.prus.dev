<?php

declare(strict_types=1);

use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

// Refresh the database between scenarios while the global Pest bootstrap loads the application TestCase.
uses(RefreshDatabase::class);

it('resolves its belongs to relationships', function (): void {
    // Arrange: create a fully-related redemption record for inspection.
    $redemption = DiscountRedemption::factory()->create();

    // Assert: confirm each relationship returns the expected relation type and model instance.
    expect($redemption->discount())->toBeInstanceOf(BelongsTo::class);
    expect($redemption->discount)->toBeInstanceOf(Discount::class);

    expect($redemption->code())->toBeInstanceOf(BelongsTo::class);
    expect($redemption->code)->toBeInstanceOf(DiscountCode::class);

    expect($redemption->user())->toBeInstanceOf(BelongsTo::class);
    expect($redemption->user)->toBeInstanceOf(User::class);

    expect($redemption->order())->toBeInstanceOf(BelongsTo::class);
    expect($redemption->order)->toBeInstanceOf(Order::class);

    expect($redemption->creator())->toBeInstanceOf(BelongsTo::class);
    expect($redemption->creator)->toBeInstanceOf(User::class);

    expect($redemption->updater())->toBeInstanceOf(BelongsTo::class);
    expect($redemption->updater)->toBeInstanceOf(User::class);
});

it('filters using discount, user, and order scopes', function (): void {
    // Arrange: build shared models so scope filters can isolate the right redemption.
    $discount = Discount::factory()->create();
    $user = User::factory()->create();
    $order = Order::factory()->create();

    $matching = DiscountRedemption::factory()->forDiscount($discount)->create([
        'user_id'  => $user->getKey(),
        'order_id' => $order->getKey(),
    ]);

    DiscountRedemption::factory()->create();

    // Assert: verify each scope reduces the dataset to the expected record.
    expect(DiscountRedemption::query()->forDiscount($discount->getKey())->get())->toHaveCount(1);
    expect(DiscountRedemption::query()->forDiscount($discount->getKey())->first())->toBeInstanceOf(DiscountRedemption::class);

    expect(DiscountRedemption::query()->forUser($user->getKey())->sole()->is($matching))->toBeTrue();
    expect(DiscountRedemption::query()->forOrder($order->getKey())->sole()->is($matching))->toBeTrue();
});

it('limits results to the provided date range', function (): void {
    // Arrange: pin the current time for deterministic comparisons.
    Carbon::setTestNow('2025-01-15 12:00:00');

    $inside = DiscountRedemption::factory()->create([
        'redeemed_at' => Carbon::now()->subDays(2),
    ]);

    DiscountRedemption::factory()->create([
        'redeemed_at' => Carbon::now()->subDays(10),
    ]);

    // Assert: only the record inside the date window should be returned.
    $results = DiscountRedemption::query()->withinDateRange(
        Carbon::now()->subDays(3),
        Carbon::now(),
    )->get();

    expect($results)->toHaveCount(1);
    expect($results->first()?->is($inside))->toBeTrue();

    Carbon::setTestNow();
});

it('applies currency, status, and recency helpers', function (): void {
    // Arrange: create dated redemptions using different statuses and currencies.
    Carbon::setTestNow('2025-01-15 12:00:00');

    $recent = DiscountRedemption::factory()->create([
        'currency_code' => 'EUR',
        'status'        => 'pending',
        'redeemed_at'   => Carbon::now()->subHours(12),
    ]);

    DiscountRedemption::factory()->create([
        'currency_code' => 'USD',
        'status'        => 'cancelled',
        'redeemed_at'   => Carbon::now()->subDays(10),
    ]);

    // Assert: confirm the scopes and helper methods behave as expected.
    expect(DiscountRedemption::query()->forCurrency('EUR')->sole()->is($recent))->toBeTrue();
    expect(DiscountRedemption::query()->byStatus('pending')->sole()->is($recent))->toBeTrue();
    expect(DiscountRedemption::query()->recent(3)->sole()->is($recent))->toBeTrue();

    expect($recent->isRecent())->toBeTrue();
    expect($recent->getStatusColorAttribute())->toBe('warning');

    Carbon::setTestNow();
});

it('evaluates amount-based scopes and aggregations', function (): void {
    // Arrange: create a discount and user so aggregates have consistent keys.
    $discount = Discount::factory()->create();
    $user = User::factory()->create();

    $higher = DiscountRedemption::factory()->forDiscount($discount)->create([
        'user_id'      => $user->getKey(),
        'amount_saved' => 50,
    ]);
    $lower = DiscountRedemption::factory()->forDiscount($discount)->create([
        'user_id'      => $user->getKey(),
        'amount_saved' => 10,
    ]);

    DiscountRedemption::factory()->create(['amount_saved' => 5]);

    // Assert: ensure the amount scopes and aggregate helpers produce accurate results.
    expect(DiscountRedemption::query()->aboveAmount(25)->sole()->is($higher))->toBeTrue();
    expect(DiscountRedemption::query()->belowAmount(20)->get())->toHaveCount(2);

    expect(DiscountRedemption::getTotalSavedForDiscount($discount->getKey()))->toBe(60.0);
    expect(DiscountRedemption::getTotalSavedForUser($user->getKey()))->toBe(60.0);
    expect(DiscountRedemption::getTotalRedemptionsForDiscount($discount->getKey()))->toBe(2);
    expect(DiscountRedemption::getTotalRedemptionsForUser($user->getKey()))->toBe(2);
    expect(DiscountRedemption::getAverageSavedForDiscount($discount->getKey()))->toBe(30.0);
    expect(DiscountRedemption::getAverageSavedForUser($user->getKey()))->toBe(30.0);

    expect($higher->formatted_amount_saved)->toBe('50.00 EUR');
});
