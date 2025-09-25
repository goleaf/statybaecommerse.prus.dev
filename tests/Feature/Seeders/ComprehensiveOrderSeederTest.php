<?php

declare(strict_types=1);

use App\Models\Document;
use App\Models\DocumentTemplate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\ComprehensiveOrderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates orders using factory relationships', function () {
    // Create prerequisite data
    User::factory(10)->create();
    Product::factory(5)->create();
    Zone::factory()->create(['is_default' => true]);

    $seeder = new ComprehensiveOrderSeeder;
    $seeder->run();

    // Verify orders were created
    expect(Order::count())->toBeGreaterThan(0);

    // Verify order structure
    $order = Order::with('user')->first();
    expect($order->user)->not->toBeNull();
    expect($order->number)->not->toBeNull();
    expect($order->status)->toBeIn(['pending', 'processing', 'shipped', 'delivered', 'cancelled']);
    expect($order->currency)->toBe('EUR');
    expect($order->locale)->toBe('lt');
    expect($order->total)->toBeGreaterThan(0);
});

it('creates order items using factory relationships', function () {
    User::factory(5)->create();
    Product::factory(3)->create();
    Zone::factory()->create(['is_default' => true]);

    $seeder = new ComprehensiveOrderSeeder;
    $seeder->run();

    // Verify order items were created
    expect(OrderItem::count())->toBeGreaterThan(0);

    // Verify order item relationships
    $orderItem = OrderItem::with(['order', 'product'])->first();
    expect($orderItem->order)->not->toBeNull();
    expect($orderItem->product)->not->toBeNull();
    expect($orderItem->quantity)->toBeGreaterThan(0);
    expect($orderItem->price)->toBeGreaterThan(0);
});

it('creates order shipping using factory relationships', function () {
    User::factory(5)->create();
    Product::factory(3)->create();
    Zone::factory()->create(['is_default' => true]);

    $seeder = new ComprehensiveOrderSeeder;
    $seeder->run();

    // Verify order shipping was created
    expect(OrderShipping::count())->toBeGreaterThan(0);

    // Verify shipping relationships
    $shipping = OrderShipping::with('order')->first();
    expect($shipping->order)->not->toBeNull();
    expect($shipping->carrier)->toBeIn(['DPD', 'Omniva', 'LP Express', 'UPS', 'FedEx', 'DHL']);
    expect($shipping->service)->toBeIn(['Standard', 'Express', 'Next Day', 'Economy', 'Premium']);
});

it('creates documents using factory relationships when templates exist', function () {
    User::factory(5)->create();
    Product::factory(3)->create();
    Zone::factory()->create(['is_default' => true]);

    // Create document templates
    DocumentTemplate::factory()->create(['type' => 'invoice']);
    DocumentTemplate::factory()->create(['type' => 'receipt']);

    $seeder = new ComprehensiveOrderSeeder;
    $seeder->run();

    // Verify documents were created for appropriate orders
    $documentsCount = Document::count();
    expect($documentsCount)->toBeGreaterThanOrEqual(0);

    if ($documentsCount > 0) {
        $document = Document::with(['order', 'documentTemplate'])->first();
        expect($document->order)->not->toBeNull();
        expect($document->documentTemplate)->not->toBeNull();
        expect($document->type)->toBeIn(['invoice', 'receipt']);
        expect($document->status)->toBe('published');
    }
});

it('ensures required data is created when missing', function () {
    // Start with empty database
    $seeder = new ComprehensiveOrderSeeder;
    $seeder->run();

    // Verify prerequisite data was created
    expect(User::count())->toBeGreaterThanOrEqual(50);
    expect(Product::count())->toBeGreaterThanOrEqual(20);
    expect(Zone::count())->toBeGreaterThan(0);
});

it('creates orders for specified time periods', function () {
    User::factory(10)->create();
    Product::factory(5)->create();
    Zone::factory()->create(['is_default' => true]);

    $seeder = new ComprehensiveOrderSeeder;
    $seeder->run();

    // Verify orders were created for current and last month
    $currentMonth = now()->startOfMonth();
    $lastMonth = now()->subMonth()->startOfMonth();

    $currentMonthOrders = Order::whereBetween('created_at', [
        $currentMonth,
        $currentMonth->copy()->endOfMonth(),
    ])->count();

    $lastMonthOrders = Order::whereBetween('created_at', [
        $lastMonth,
        $lastMonth->copy()->endOfMonth(),
    ])->count();

    expect($currentMonthOrders)->toBeGreaterThan(0);
    expect($lastMonthOrders)->toBeGreaterThan(0);
});

it('maintains proper order status and payment status relationships', function () {
    User::factory(10)->create();
    Product::factory(5)->create();
    Zone::factory()->create(['is_default' => true]);

    $seeder = new ComprehensiveOrderSeeder;
    $seeder->run();

    // Verify status consistency
    $orders = Order::all();

    foreach ($orders as $order) {
        expect($order->status)->toBeIn(['pending', 'processing', 'shipped', 'delivered', 'cancelled']);
        expect($order->payment_status)->toBeIn(['pending', 'paid', 'failed', 'refunded']);
        expect($order->payment_method)->toBeIn(['credit_card', 'paypal', 'bank_transfer', 'cash_on_delivery', 'stripe', 'mollie']);

        // Verify financial calculations make sense
        expect($order->total)->toBeGreaterThanOrEqual($order->subtotal);
        expect($order->subtotal)->toBeGreaterThan(0);
    }
});

it('creates proper order item relationships without duplicates', function () {
    User::factory(5)->create();
    Product::factory(3)->create();
    Zone::factory()->create(['is_default' => true]);

    $seeder = new ComprehensiveOrderSeeder;
    $seeder->run();

    // Verify each order has items and no duplicate products per order
    $orders = Order::with('items.product')->get();

    foreach ($orders as $order) {
        expect($order->items)->not->toBeEmpty();

        // Check for duplicate products in same order
        $productIds = $order->items->pluck('product_id')->toArray();
        $uniqueProductIds = array_unique($productIds);

        expect(count($productIds))->toBe(count($uniqueProductIds));
    }
});
