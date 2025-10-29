<?php

declare(strict_types=1);

use App\Support\Stats\Inline\ProductSeries;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    // Fresh minimal schema each test (safe for SQLite & facades)
    Schema::dropIfExists('order_items');

    Schema::create('order_items', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('order_id')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->unsignedBigInteger('variant_id')->nullable();
        $table->integer('quantity')->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->timestamps();
    });

    // Deterministic cache
    try { Cache::clear(); } catch (Throwable $e) { Cache::flush(); }

    // Freeze time so the 30-day window is stable
    Carbon::setTestNow(Carbon::create(2024, 12, 1, 0, 0, 0));
});

afterEach(function (): void {
    Carbon::setTestNow();
    if (Schema::hasTable('order_items')) {
        DB::table('order_items')->delete();
    }
});

it('unit: daily sales returns expected values and caches result', function (): void {
    $productId = 1234;

    // Seed: 7 units 10 days ago, 8 units “today”
    DB::table('order_items')->insert([
        [
            'product_id' => $productId,
            'variant_id' => null,
            'quantity'   => 7,
            'total'      => 0,
            'created_at' => Carbon::now()->subDays(10),
            'updated_at' => Carbon::now()->subDays(10),
        ],
        [
            'product_id' => $productId,
            'variant_id' => null,
            'quantity'   => 8,
            'total'      => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ],
    ]);

    $series = ProductSeries::last30Days($productId);

    expect($series)->toBeArray()
        ->and($series)->toHaveKeys(['labels', 'values'])
        ->and($series['labels'])->toHaveCount(30)
        ->and($series['values'])->toHaveCount(30)
        ->and(array_sum($series['values']))->toBe(15);

    // Orientation guard (oldest→newest vs newest→oldest)
    $valueAtTenDays = $series['values'][19] ?? $series['values'][10] ?? null;
    expect($valueAtTenDays)->toBe(7);

    // Cached result must be identical
    expect(ProductSeries::last30Days($productId))->toEqual($series);
});
