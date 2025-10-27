<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Stats\Series;

use App\Models\Customer;
use App\Models\Order;
use App\Support\Cache\CacheKeys;
use App\Support\Stats\Series\CustomerSeries;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class CustomerSeriesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::create('customers', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', static function (Blueprint $table): void {
            $table->id();
            $table->string('number')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('status')->default('completed');
            $table->string('payment_status')->nullable();
            $table->string('payment_state')->default('created');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->json('billing_address')->nullable();
            $table->json('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // Ensure the same index name used in production exists for SQLite's INDEXED BY hint
            $table->index('created_at', 'orders_created_at_index');
        });

        Schema::create('order_items', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Ensure the customer order helper returns the expected totals and caches the payload.
     */
    public function test_daily_orders_returns_expected_values_and_caches_result(): void
    {
        Cache::flush();

        CarbonImmutable::setTestNow(CarbonImmutable::create(2024, 6, 15, 12));

        $customer = Customer::factory()->create();

        $firstDay = CarbonImmutable::now()->subDays(2)->startOfDay();
        $secondDay = CarbonImmutable::now()->subDay()->startOfDay();

        $firstOrder = Order::factory()->completed()->create([
            'created_at' => $firstDay,
            'updated_at' => $firstDay,
            'total'      => 120.50,
        ]);

        Order::withoutTimestamps(function () use ($firstOrder, $customer): void {
            $firstOrder->forceFill(['customer_id' => $customer->getKey()])->save();
        });

        $secondOrder = Order::factory()->completed()->create([
            'created_at' => $secondDay,
            'updated_at' => $secondDay,
            'total'      => 80.00,
        ]);

        Order::withoutTimestamps(function () use ($secondOrder, $customer): void {
            $secondOrder->forceFill(['customer_id' => $customer->getKey()])->save();
        });

        $ignoredOrder = Order::factory()->completed()->create([
            'created_at' => CarbonImmutable::now()->subDays(5),
            'updated_at' => CarbonImmutable::now()->subDays(5),
            'total'      => 45.00,
        ]);

        Order::withoutTimestamps(function () use ($ignoredOrder, $customer): void {
            $ignoredOrder->forceFill(['customer_id' => $customer->getKey()])->save();
        });

        $series = CustomerSeries::dailyOrders($customer, 3);

        $expectedLabels = [
            $firstDay->isoFormat('MMM D'),
            $secondDay->isoFormat('MMM D'),
            CarbonImmutable::now()->isoFormat('MMM D'),
        ];

        $expectedOrders = [1, 1, 0];
        $expectedRevenue = [120.5, 80.0, 0.0];

        self::assertSame($expectedLabels, $series['labels']);
        self::assertSame($expectedOrders, $series['orders']);
        self::assertSame($expectedRevenue, $series['revenue']);

        $cacheKey = CacheKeys::customerOrdersSeries($customer->getKey(), 3);
        self::assertTrue(Cache::has($cacheKey));

        CarbonImmutable::setTestNow();
    }
}
