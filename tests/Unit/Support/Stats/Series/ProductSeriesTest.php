<?php

declare(strict_types=1);

use App\Models\Product;
use App\Support\Stats\Series\ProductSeries; // SERIES namespace
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * This test intentionally avoids touching the real `products` table.
 * It creates only minimal `orders` + `order_items` schema that satisfy:
 *  - UserOwnedScope (via user_id + logged-in user)
 *  - Series filters (paid/completed states, payment columns, join on orders)
 */

beforeEach(function (): void {
    // Recreate minimal schema each time (SQLite-friendly)
    Schema::dropIfExists('order_items');
    Schema::dropIfExists('orders');

    // Minimal orders table with common payment fields your Series might use
    Schema::create('orders', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('status')->default('pending');          // e.g. 'paid' | 'completed'
        $table->string('payment_status')->nullable();          // e.g. 'paid'
        $table->string('payment_state')->nullable();           // sometimes used in other apps
        $table->timestamp('paid_at')->nullable();              // in case series uses paid_at
        $table->timestamps();
        $table->softDeletes();
    });

    // Minimal order_items table satisfying UserOwnedScope + product filter
    Schema::create('order_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('order_id')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();     // for UserOwnedScope
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('variant_id')->nullable();
        $table->string('status')->default('pending');          // in case series filters on item status
        $table->integer('quantity')->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->timestamps();
        $table->softDeletes();
    });

    // Deterministic cache
    try { Cache::clear(); } catch (\Throwable $e) { Cache::flush(); }

    // Stable "now" so bucket positions are deterministic
    Carbon::setTestNow(Carbon::create(2024, 12, 1, 0, 0, 0));
});

afterEach(function (): void {
    Carbon::setTestNow();
    if (Schema::hasTable('order_items')) {
        DB::table('order_items')->delete();
    }
    if (Schema::hasTable('orders')) {
        DB::table('orders')->delete();
    }
});

it('daily sales returns expected values and caches result', function (): void {
    $productId = 1234;

    // Log in a lightweight in-memory user so UserOwnedScope matches
    $user = new \App\Models\User();
    $user->setAttribute($user->getKeyName(), 999);
    Auth::login($user);

    // Unpersisted Product with the desired key (Series only needs ->getKey())
    $product = new Product();
    $product->setAttribute($product->getKeyName(), $productId);

    // Create two PAID/COMPLETED orders for this user
    $orderPaidTodayId = DB::table('orders')->insertGetId([
        'user_id'        => $user->getKey(),
        'status'         => 'completed',             // accepted by common series filters
        'payment_status' => 'paid',
        'payment_state'  => 'paid',
        'paid_at'        => Carbon::now(),           // if the series uses paid_at
        'created_at'     => Carbon::now(),
        'updated_at'     => Carbon::now(),
    ]);

    $orderPaidPastId = DB::table('orders')->insertGetId([
        'user_id'        => $user->getKey(),
        'status'         => 'completed',
        'payment_status' => 'paid',
        'payment_state'  => 'paid',
        'paid_at'        => Carbon::now()->subDays(10),
        'created_at'     => Carbon::now()->subDays(10),
        'updated_at'     => Carbon::now()->subDays(10),
    ]);

    // Seed matching order_items (same user, linked order, product, paid status)
    DB::table('order_items')->insert([
        [
            'order_id'   => $orderPaidPastId,
            'user_id'    => $user->getKey(),
            'product_id' => $productId,
            'variant_id' => null,
            'status'     => 'completed',
            'quantity'   => 7,
            'total'      => 0,
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10),
        ],
        [
            'order_id'   => $orderPaidTodayId,
            'user_id'    => $user->getKey(),
            'product_id' => $productId,
            'variant_id' => null,
            'status'     => 'completed',
            'quantity'   => 8,
            'total'      => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
    ]);

    // Your Series signature: dailySales(Product $product, int $days = 14)
    $series = ProductSeries::dailySales($product, 30);

    expect($series)->toBeArray()
        ->and($series)->toHaveKeys(['labels', 'quantities', 'revenue'])
        ->and($series['labels'])->toHaveCount(30)
        ->and($series['quantities'])->toHaveCount(30)
        ->and(array_sum($series['quantities']))->toBe(15);

    // Orientation guard (oldest→newest vs newest→oldest)
    $qAtTenDays = $series['quantities'][19] ?? $series['quantities'][10] ?? null;
    expect($qAtTenDays)->toBe(7);

    // Cache sanity: second call identical
    $series2 = ProductSeries::dailySales($product, 30);
    expect($series2)->toEqual($series);
});
