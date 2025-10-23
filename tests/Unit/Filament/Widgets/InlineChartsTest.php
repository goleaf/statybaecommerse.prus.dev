<?php

declare(strict_types=1);

use App\Filament\Widgets\InlineCharts\CustomerLtv12MonthsChart;
use App\Filament\Widgets\InlineCharts\ProductSales30DaysChart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses()->group('inline-widgets');

RefreshDatabaseState::$migrated = true;

beforeEach(function (): void {
    $previousConnection = DB::getDefaultConnection();
    $previousConfig = config('database.connections.inline_charts_sqlite');

    test()->inlineChartsPreviousConnection = $previousConnection;
    test()->inlineChartsPreviousConfig = $previousConfig;

    config([
        'database.default' => 'inline_charts_sqlite',
        'database.connections.inline_charts_sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ]);

    DB::purge('inline_charts_sqlite');
    DB::setDefaultConnection('inline_charts_sqlite');
    Schema::connection('inline_charts_sqlite')->dropIfExists('order_items');
    Schema::connection('inline_charts_sqlite')->dropIfExists('orders');
    Schema::connection('inline_charts_sqlite')->dropIfExists('products');
    Schema::connection('inline_charts_sqlite')->dropIfExists('customers');

    Schema::connection('inline_charts_sqlite')->create('products', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('sku')->unique();
        $table->decimal('price', 10, 2)->default(0);
        $table->string('status')->default('draft');
        $table->boolean('is_visible')->default(true);
        $table->timestamps();
    });

    Schema::connection('inline_charts_sqlite')->create('orders', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('status')->default('pending');
        $table->boolean('is_active')->default(true);
        $table->decimal('total', 10, 2)->default(0);
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::connection('inline_charts_sqlite')->create('order_items', function (Blueprint $table): void {
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

    Schema::connection('inline_charts_sqlite')->create('customers', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    config(['cache.default' => 'array']);
    Cache::flush();

    Carbon::setTestNow(Carbon::create(2024, 12, 15, 0, 0, 0));
});

afterEach(function (): void {
    Carbon::setTestNow();
    DB::disconnect('inline_charts_sqlite');
    DB::purge('inline_charts_sqlite');

    $previousConnection = test()->inlineChartsPreviousConnection ?? 'sqlite';
    DB::setDefaultConnection($previousConnection);
    config(['database.default' => $previousConnection]);

    $previousConfig = test()->inlineChartsPreviousConfig ?? null;

    if ($previousConfig === null) {
        config()->offsetUnset('database.connections.inline_charts_sqlite');
    } else {
        config(['database.connections.inline_charts_sqlite' => $previousConfig]);
    }
});

it('returns dataset data for the product inline chart widget', function (): void {
    $product = Product::withoutEvents(function () {
        return Product::unguarded(fn () => Product::create([
            'name'       => 'Widget Product',
            'slug'       => 'widget-product',
            'sku'        => 'WID-001',
            'price'      => 25,
            'status'     => 'published',
            'is_visible' => true,
        ]));
    });

    OrderItem::withoutEvents(function () use ($product): void {
        OrderItem::unguarded(fn () => OrderItem::create([
            'product_id' => $product->getKey(),
            'quantity'   => 4,
            'unit_price' => 0,
            'price'      => 0,
            'total'      => 0,
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]));
    });

    $widget = new ProductSales30DaysChart;
    $widget->record = $product;

    $data = (function (): array {
        return $this->getData();
    })->call($widget);

    expect($data['datasets'][0]['data'])
        ->toHaveCount(30)
        ->and(array_sum($data['datasets'][0]['data']))->toBe(4)
        ->and($data['labels'])->toHaveCount(30);
});

it('returns dataset data for the customer inline chart widget', function (): void {
    $customer = Customer::unguarded(fn () => Customer::create([
        'name'      => 'Inline Customer',
        'email'     => 'inline@example.com',
        'is_active' => true,
    ]));

    Order::withoutEvents(function () use ($customer): void {
        Order::unguarded(fn () => Order::create([
            'customer_id' => $customer->getKey(),
            'user_id'     => $customer->getKey(),
            'status'      => 'processing',
            'total'       => 199.95,
            'created_at'  => Carbon::now()->subMonths(2),
            'updated_at'  => Carbon::now()->subMonths(2),
        ]));
    });

    $widget = new CustomerLtv12MonthsChart;
    $widget->record = $customer;

    $data = (function (): array {
        return $this->getData();
    })->call($widget);

    expect($data['datasets'][0]['data'])
        ->toHaveCount(12)
        ->and(round(array_sum($data['datasets'][0]['data']), 2))->toBe(199.95)
        ->and($data['labels'])->toHaveCount(12);
});
