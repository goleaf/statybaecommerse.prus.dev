<?php

declare(strict_types=1);

use App\Models\Order;
use App\Support\Stats\Inline\CustomerSeries;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Note:
 * - Do not add `uses(Tests\TestCase::class)` here. Pest already applies it to this folder.
 * - This test creates only the minimal `orders` table it needs.
 */

beforeEach(function (): void {
    // Re-create a minimal orders table each time (SQLite-friendly)
    Schema::dropIfExists('orders');

    Schema::create('orders', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('status')->default('pending');
        $table->boolean('is_active')->default(true);
        $table->decimal('total', 10, 2)->default(0);
        $table->timestamps();
        $table->softDeletes();
    });

    // Deterministic in-memory cache
    try {
        Cache::clear();   // Laravel 11/12
    } catch (Throwable $e) {
        Cache::flush();   // Fallback for older
    }

    // Freeze time so month buckets are stable
    Carbon::setTestNow(Carbon::create(2024, 12, 1, 0, 0, 0));
});

afterEach(function (): void {
    Carbon::setTestNow();
    // Clean rows (avoid TRUNCATE for SQLite)
    if (Schema::hasTable('orders')) {
        DB::table('orders')->delete();
    }
});

it('unit: builds a 12 month revenue series for a customer', function (): void {
    $customerId = 7;

    Order::withoutEvents(function () use ($customerId): void {
        Order::unguarded(fn () => Order::create([
            'customer_id' => $customerId,
            'user_id'     => $customerId,
            'status'      => 'pending',
            'total'       => 120.50,
            'created_at'  => Carbon::now()->subMonths(1),
            'updated_at'  => Carbon::now()->subMonths(1),
        ]));

        Order::unguarded(fn () => Order::create([
            'customer_id' => $customerId,
            'user_id'     => $customerId,
            'status'      => 'processing',
            'total'       => 300.00,
            'created_at'  => Carbon::now()->subMonths(6),
            'updated_at'  => Carbon::now()->subMonths(6),
        ]));
    });

    $series = CustomerSeries::ordersLast12m($customerId);

    expect($series)->toBeArray()
        ->and($series)->toHaveKeys(['labels', 'values'])
        ->and($series['labels'])->toHaveCount(12)
        ->and($series['values'])->toHaveCount(12)
        ->and(round(array_sum($series['values']), 2))->toBe(420.50)
        // cache sanity: second call identical
        ->and(CustomerSeries::ordersLast12m($customerId))->toEqual($series);
});
