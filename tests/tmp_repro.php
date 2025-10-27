<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customer;
use App\Models\Order;
use App\Support\Cache\CacheKeys;
use App\Support\Stats\Series\CustomerSeries;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

Schema::disableForeignKeyConstraints();
Schema::dropIfExists('order_items');
Schema::dropIfExists('orders');
Schema::dropIfExists('customers');
Schema::create('customers', function (Blueprint $table): void {
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

Schema::create('orders', function (Blueprint $table): void {
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
    $table->index('created_at', 'orders_created_at_index');
});

Schema::enableForeignKeyConstraints();

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

var_export($series);

echo "\n";

$cacheKey = CacheKeys::customerOrdersSeries($customer->getKey(), 3);

var_dump(Cache::has($cacheKey));
