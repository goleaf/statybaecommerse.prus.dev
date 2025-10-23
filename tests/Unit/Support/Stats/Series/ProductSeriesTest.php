<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Stats\Series;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\Cache\CacheKeys;
use App\Support\Stats\Series\ProductSeries;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

final class ProductSeriesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('customers');

        Schema::create('products', static function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('simple');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            $table->decimal('weight', 12, 4)->nullable();
            $table->decimal('length', 12, 4)->nullable();
            $table->decimal('width', 12, 4)->nullable();
            $table->decimal('height', 12, 4)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('manage_stock')->default(false);
            $table->string('status')->default('draft');
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customers', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
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
            $table->string('status')->default('completed');
            $table->string('payment_status')->nullable();
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
        });

        Schema::create('order_items', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
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
    }

    /**
     * Ensure the product sales helper returns an ordered dataset and stores it in cache.
     */
    public function test_daily_sales_returns_expected_values_and_caches_result(): void
    {
        Cache::flush();

        CarbonImmutable::setTestNow(CarbonImmutable::create(2024, 6, 15, 12));

        $product = Product::factory()->create([
            'price' => 20,
        ]);

        $firstDay = CarbonImmutable::now()->subDays(2)->startOfDay();
        $secondDay = CarbonImmutable::now()->subDay()->startOfDay();

        $firstOrder = Order::factory()->completed()->create([
            'created_at' => $firstDay,
            'updated_at' => $firstDay,
        ]);

        OrderItem::factory()
            ->forOrder($firstOrder)
            ->forProduct($product)
            ->create([
                'quantity'   => 2,
                'created_at' => $firstDay,
                'updated_at' => $firstDay,
            ]);

        $secondOrder = Order::factory()->completed()->create([
            'created_at' => $secondDay,
            'updated_at' => $secondDay,
        ]);

        OrderItem::factory()
            ->forOrder($secondOrder)
            ->forProduct($product)
            ->create([
                'quantity'   => 3,
                'created_at' => $secondDay,
                'updated_at' => $secondDay,
            ]);

        $ignoredOrder = Order::factory()->completed()->create([
            'created_at' => CarbonImmutable::now()->subDays(5),
            'updated_at' => CarbonImmutable::now()->subDays(5),
        ]);

        OrderItem::factory()
            ->forOrder($ignoredOrder)
            ->forProduct($product)
            ->create([
                'quantity'   => 5,
                'created_at' => CarbonImmutable::now()->subDays(5),
                'updated_at' => CarbonImmutable::now()->subDays(5),
            ]);

        $series = ProductSeries::dailySales($product, 3);

        $expectedLabels = [
            $firstDay->isoFormat('MMM D'),
            $secondDay->isoFormat('MMM D'),
            CarbonImmutable::now()->isoFormat('MMM D'),
        ];

        $expectedQuantities = [2, 3, 0];
        $expectedRevenue = [40.0, 60.0, 0.0];

        self::assertSame($expectedLabels, $series['labels']);
        self::assertSame($expectedQuantities, $series['quantities']);
        self::assertSame($expectedRevenue, $series['revenue']);

        $cacheKey = CacheKeys::productSalesSeries($product->getKey(), 3);
        self::assertTrue(Cache::has($cacheKey));

        CarbonImmutable::setTestNow();
    }
}
