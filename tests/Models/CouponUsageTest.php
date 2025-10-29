<?php

declare(strict_types=1);

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use App\Notifications\CouponUsageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

// Explicitly bind the base Laravel TestCase so database transactions and model factories
// remain available when this suite runs in isolation via targeted filters.
uses(Tests\TestCase::class, RefreshDatabase::class);

describe('CouponUsage model', function (): void {
    it('defines expected fillable attributes', function (): void {
        // Arrange: instantiate a fresh model instance without persisting it.
        $usage = new CouponUsage;

        // Assert: verify the fillable whitelist protects against mass-assignment issues.
        expect($usage->getFillable())->toBe([
            'coupon_id',
            'user_id',
            'order_id',
            'discount_amount',
            'used_at',
            'metadata',
        ]);
    });

    it('casts critical attributes correctly', function (): void {
        // Arrange: freeze time to produce deterministic assertions.
        Carbon::setTestNow('2025-01-15 12:00:00');
        $usage = CouponUsage::factory()->create([
            'discount_amount' => '19.50',
            'metadata'        => ['channel' => 'web'],
        ]);

        // Assert: the casted values should be native PHP types for downstream logic.
        expect($usage->discount_amount)->toBe('19.50');
        expect($usage->used_at)->toBeInstanceOf(Carbon::class);
        expect($usage->metadata)->toBe(['channel' => 'web']);
        Carbon::setTestNow();
    });

    it('sets used_at automatically on creation when missing', function (): void {
        // Arrange: suspend time for deterministic checks and create a usage without used_at.
        Carbon::setTestNow('2025-02-01 09:30:00');
        $usage = CouponUsage::factory()->create(['used_at' => null]);

        // Assert: the attribute should default to the current time defined via the booted hook.
        expect($usage->used_at?->toDateTimeString())->toBe('2025-02-01 09:30:00');
        Carbon::setTestNow();
    });

    it('exposes coupon, user, and order relationships', function (): void {
        // Arrange: build related records and associate them with the coupon usage instance.
        $coupon = Coupon::factory()->create();
        $user = User::factory()->create();
        $order = Order::factory()->create();
        $usage = CouponUsage::factory()->create([
            'coupon_id' => $coupon->id,
            'user_id'   => $user->id,
            'order_id'  => $order->id,
        ]);

        // Assert: ensure each relationship resolves to the expected model instance.
        expect($usage->coupon)->toBeInstanceOf(Coupon::class);
        expect($usage->user)->toBeInstanceOf(User::class);
        expect($usage->order)->toBeInstanceOf(Order::class);
    });

    it('scopes usages that occur today', function (): void {
        // Arrange: pin the clock and seed a mix of records inside and outside today.
        Carbon::setTestNow('2025-03-10 08:00:00');
        $todayUsage = CouponUsage::factory()->create(['used_at' => now()]);
        CouponUsage::factory()->create(['used_at' => now()->subDay()]);

        // Act: query through the scope under test.
        $results = CouponUsage::usedToday()->get();

        // Assert: only the usage created today should be returned.
        expect($results)->toHaveCount(1);
        // `sole()` communicates intent to both phpstan and fellow readers that exactly
        // one record should exist, avoiding nullable `first()` handling noise.
        expect($results->sole()->is($todayUsage))->toBeTrue();
        Carbon::setTestNow();
    });

    it('scopes usages that occur within the current week', function (): void {
        // Arrange: freeze time and create usages both within and outside the target window.
        Carbon::setTestNow('2025-04-16 12:00:00');
        $withinWeek = CouponUsage::factory()->create(['used_at' => now()->subDay()]);
        CouponUsage::factory()->create(['used_at' => now()->subWeeks(2)]);

        // Act: execute the weekly scope.
        $results = CouponUsage::usedThisWeek()->get();

        // Assert: confirm only the qualifying usage is retrieved.
        expect($results)->toHaveCount(1);
        expect($results->sole()->is($withinWeek))->toBeTrue();
        Carbon::setTestNow();
    });

    it('scopes usages that occur within the current month', function (): void {
        // Arrange: freeze time to control the month boundaries.
        Carbon::setTestNow('2025-05-20 10:00:00');
        $withinMonth = CouponUsage::factory()->create(['used_at' => now()->subDays(3)]);
        CouponUsage::factory()->create(['used_at' => now()->subMonths(1)]);

        // Act: execute the monthly scope.
        $results = CouponUsage::usedThisMonth()->get();

        // Assert: verify only the recent usage appears in the result set.
        expect($results)->toHaveCount(1);
        expect($results->sole()->is($withinMonth))->toBeTrue();
        Carbon::setTestNow();
    });

    it('scopes usages by recent days window', function (): void {
        // Arrange: freeze time and create records around the threshold boundary.
        Carbon::setTestNow('2025-06-01 09:00:00');
        $recent = CouponUsage::factory()->create(['used_at' => now()->subDays(2)]);
        CouponUsage::factory()->create(['used_at' => now()->subDays(10)]);

        // Act: pull results through the recent scope with a custom range.
        $results = CouponUsage::recent(5)->get();

        // Assert: ensure only the usage within the five day window is returned.
        expect($results)->toHaveCount(1);
        expect($results->sole()->is($recent))->toBeTrue();
        Carbon::setTestNow();
    });

    it('registers usage updates metadata and increments coupon counter', function (): void {
        // Arrange: create dependent records and fake notifications to inspect behaviour.
        Notification::fake();
        $coupon = Coupon::factory()->create(['used_count' => 0]);
        $usage = CouponUsage::factory()->create(['coupon_id' => $coupon->id]);

        // Act: trigger the registerUsage helper with supplemental metadata.
        $usage->registerUsage(['channel' => 'checkout']);
        $coupon->refresh();

        // Assert: metadata persists, coupon counter increments, and notifications fire.
        expect($usage->metadata)->toBe(['channel' => 'checkout']);
        expect($coupon->used_count)->toBe(1);
        Notification::assertSentTo($usage->user, CouponUsageNotification::class);
    });

    it('formats discount and timestamps consistently', function (): void {
        // Arrange: freeze time and craft a usage with a predictable discount.
        Carbon::setTestNow('2025-07-05 14:15:00');
        $usage = CouponUsage::factory()->create([
            'discount_amount' => '25.50',
            'used_at'         => now(),
        ]);

        // Assert: confirm the computed accessors expose friendly formatted strings.
        expect($usage->formatted_discount)->toContain('25.50');
        expect($usage->formatted_discount)->toContain('€');
        expect($usage->formatted_used_at)->toBe('2025-07-05 14:15:00');
        Carbon::setTestNow();
    });

    it('computes usage period labels for different time frames', function (): void {
        // Arrange: freeze time and create usages across multiple periods.
        Carbon::setTestNow('2025-08-12 12:00:00');
        $todayUsage = CouponUsage::factory()->create(['used_at' => now()]);
        $weekUsage = CouponUsage::factory()->create(['used_at' => now()->subDay()]);
        $monthUsage = CouponUsage::factory()->create(['used_at' => now()->subDays(10)]);
        $oldUsage = CouponUsage::factory()->create(['used_at' => now()->subMonths(3)]);

        // Assert: ensure each record resolves to the appropriate translation key.
        expect($todayUsage->usage_period)->toBe(__('admin.coupon_usages.periods.today'));
        expect($weekUsage->usage_period)->toBe(__('admin.coupon_usages.periods.this_week'));
        expect($monthUsage->usage_period)->toBe(__('admin.coupon_usages.periods.this_month'));
        expect($oldUsage->usage_period)->toBe(__('admin.coupon_usages.periods.older'));
        Carbon::setTestNow();
    });

    it('duplicates a usage for a different order without persisting it', function (): void {
        // Arrange: create a base usage and a new target order to duplicate into.
        $originalUsage = CouponUsage::factory()->create();
        $newOrder = Order::factory()->create();

        // Act: duplicate the usage for the new order.
        $duplicate = $originalUsage->duplicateForOrder($newOrder);

        // Assert: ensure the duplicate is unsaved and references the new order.
        expect($duplicate->exists)->toBeFalse();
        expect($duplicate->order_id)->toBe($newOrder->id);
        expect($duplicate->id)->toBeNull();
    });
});
