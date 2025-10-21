<?php

declare(strict_types=1);

use App\Models\OrderItem;
use App\Support\Stats\Inline\ProductSeries;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

RefreshDatabaseState::$migrated = true;

uses()->group('inline-stats');

beforeEach(function (): void {
    Schema::dropIfExists('order_items');
    Schema::dropIfExists('products');

    Schema::create('products', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    Schema::create('order_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('order_id')->nullable();
        $table->unsignedBigInteger('product_id');
        $table->integer('quantity');
        $table->decimal('unit_price', 10, 2)->default(0);
        $table->decimal('price', 10, 2)->nullable();
        $table->decimal('discount_amount', 10, 2)->default(0);
        $table->decimal('total', 10, 2)->default(0);
        $table->timestamps();
    });

    config(['cache.default' => 'array']);
    Cache::flush();

    Carbon::setTestNow(Carbon::create(2024, 12, 31, 0, 0, 0));
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('builds a 30 day quantity series for a product', function (): void {
    $productId = 42;

    OrderItem::withoutEvents(function () use ($productId): void {
        OrderItem::unguarded(fn () => OrderItem::create([
            'product_id' => $productId,
            'quantity'   => 5,
            'unit_price' => 0,
            'price'      => 0,
            'total'      => 0,
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]));

        OrderItem::unguarded(fn () => OrderItem::create([
            'product_id' => $productId,
            'quantity'   => 3,
            'unit_price' => 0,
            'price'      => 0,
            'total'      => 0,
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]));

        OrderItem::unguarded(fn () => OrderItem::create([
            'product_id' => $productId,
            'quantity'   => 7,
            'unit_price' => 0,
            'price'      => 0,
            'total'      => 0,
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10),
        ]));
    });

    $series = ProductSeries::last30Days($productId);

    expect($series['labels'])->toHaveCount(30)
        ->and($series['values'])->toHaveCount(30)
        ->and(array_sum($series['values']))->toBe(15)
        ->and($series['values'][19])->toBe(7)
        ->and(ProductSeries::last30Days($productId))->toEqual($series);
});
