<?php

declare(strict_types=1);

use App\Models\Order;
use App\Support\Stats\Inline\CustomerSeries;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

RefreshDatabaseState::$migrated = true;

uses()->group('inline-stats');

beforeEach(function (): void {
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

    config(['cache.default' => 'array']);
    Cache::flush();

    Carbon::setTestNow(Carbon::create(2024, 12, 1, 0, 0, 0));
});

afterEach(function (): void {
    Carbon::setTestNow();
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
            'total'       => 300,
            'created_at'  => Carbon::now()->subMonths(6),
            'updated_at'  => Carbon::now()->subMonths(6),
        ]));
    });

    $series = CustomerSeries::ordersLast12m($customerId);

    expect($series['labels'])->toHaveCount(12)
        ->and($series['values'])->toHaveCount(12)
        ->and(round(array_sum($series['values']), 2))->toBe(420.50)
        ->and(CustomerSeries::ordersLast12m($customerId))->toEqual($series);
});
