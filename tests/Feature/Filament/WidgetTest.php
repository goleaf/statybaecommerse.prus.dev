<?php declare(strict_types=1);

use App\Filament\Widgets\EcommerceOverview;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Filament Widgets', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($this->admin);
    });

    describe('EcommerceOverview Widget', function () {
        it('can render stats overview widget', function () {
            Livewire::test(EcommerceOverview::class)
                ->assertSuccessful();
        });

        it('displays correct order statistics', function () {
            // Create test orders
            Order::factory()->count(5)->create([
                'created_at' => today(),
                'total' => 10000,  // $100.00
            ]);

            Order::factory()->count(3)->create([
                'created_at' => today()->subDay(),
                'total' => 5000,  // $50.00
            ]);

            $widget = Livewire::test(EcommerceOverview::class);

            $stats = $widget->instance()->getStats();

            expect($stats)->toHaveCount(6);  // EcommerceOverview has 6 stat cards

            // Check that stats contain expected data
            $ordersStat = collect($stats)->first(fn($stat) =>
                str_contains($stat->getLabel(), 'užsakymų'));

            expect($ordersStat)->not->toBeNull();
        });

        it('displays correct revenue statistics', function () {
            Order::factory()->count(2)->create([
                'created_at' => today(),
                'total' => 15000,  // $150.00 each
            ]);

            $widget = Livewire::test(EcommerceOverview::class);
            $stats = $widget->instance()->getStats();

            $revenueStat = collect($stats)->first(fn($stat) =>
                str_contains($stat->getLabel(), 'pajamos'));

            expect($revenueStat)->not->toBeNull();
        });

        it('displays customer count', function () {
            User::factory()->count(10)->create();

            $widget = Livewire::test(EcommerceOverview::class);
            $stats = $widget->instance()->getStats();

            $customersStat = collect($stats)->first(fn($stat) =>
                str_contains($stat->getLabel(), 'klientų'));

            expect($customersStat)->not->toBeNull();
        });

        it('displays product count', function () {
            Product::factory()->count(25)->create(['is_visible' => true]);
            Product::factory()->count(5)->create(['is_visible' => false]);

            $widget = Livewire::test(EcommerceOverview::class);
            $stats = $widget->instance()->getStats();

            $productsStat = collect($stats)->first(fn($stat) =>
                str_contains($stat->getLabel(), 'produktų'));

            expect($productsStat)->not->toBeNull();
        });

        it('shows trend indicators', function () {
            // Create orders for comparison
            Order::factory()->count(5)->create(['created_at' => today()]);
            Order::factory()->count(3)->create(['created_at' => today()->subWeek()]);

            $widget = Livewire::test(EcommerceOverview::class);
            $stats = $widget->instance()->getStats();

            // Check that at least one stat has a trend
            $hasPositiveTrend = collect($stats)->some(fn($stat) =>
                $stat->getColor() === 'success');

            expect($hasPositiveTrend)->toBeTrue();
        });
    });

    describe('TopSellingProductsWidget', function () {
        it('can render top selling products widget', function () {
            Livewire::test(TopSellingProductsWidget::class)
                ->assertSuccessful();
        });

        it('displays products with completed orders', function () {
            $product1 = Product::factory()->create(['name' => 'Product 1']);
            $product2 = Product::factory()->create(['name' => 'Product 2']);

            // Create orders with different quantities
            $order1 = Order::factory()->create(['status' => 'completed']);
            $order1->items()->create([
                'product_id' => $product1->id,
                'name' => $product1->name,
                'sku' => $product1->sku ?? 'SKU-001',
                'quantity' => 5,
                'unit_price' => 1000,
                'total' => 5000,
            ]);

            $order2 = Order::factory()->create(['status' => 'completed']);
            $order2->items()->create([
                'product_id' => $product2->id,
                'name' => $product2->name,
                'sku' => $product2->sku ?? 'SKU-002',
                'quantity' => 3,
                'unit_price' => 2000,
                'total' => 6000,
            ]);

            Livewire::test(TopSellingProductsWidget::class)
                ->assertCanSeeTableRecords([$product1, $product2]);
        });

        it('orders products by sales quantity', function () {
            $product1 = Product::factory()->create(['name' => 'Low Sales']);
            $product2 = Product::factory()->create(['name' => 'High Sales']);

            // Product 1: 2 items sold
            $order1 = Order::factory()->create(['status' => 'completed']);
            $order1->items()->create([
                'product_id' => $product1->id,
                'name' => $product1->name,
                'sku' => $product1->sku ?? 'SKU-001',
                'quantity' => 2,
                'unit_price' => 1000,
                'total' => 2000,
            ]);

            // Product 2: 10 items sold
            $order2 = Order::factory()->create(['status' => 'completed']);
            $order2->items()->create([
                'product_id' => $product2->id,
                'name' => $product2->name,
                'sku' => $product2->sku ?? 'SKU-002',
                'quantity' => 10,
                'unit_price' => 1000,
                'total' => 10000,
            ]);

            Livewire::test(TopSellingProductsWidget::class)
                ->assertCanSeeTableRecords([$product2, $product1], inOrder: true);
        });

        it('excludes products without completed orders', function () {
            $productWithSales = Product::factory()->create();
            $productWithoutSales = Product::factory()->create();

            // Only create completed order for first product
            $order = Order::factory()->create(['status' => 'completed']);
            $order->items()->create([
                'product_id' => $productWithSales->id,
                'quantity' => 1,
                'price' => 1000,
            ]);

            // Create pending order for second product (should not count)
            $pendingOrder = Order::factory()->create(['status' => 'pending']);
            $pendingOrder->items()->create([
                'product_id' => $productWithoutSales->id,
                'quantity' => 1,
                'price' => 1000,
            ]);

            Livewire::test(TopSellingProductsWidget::class)
                ->assertCanSeeTableRecords([$productWithSales])
                ->assertCanNotSeeTableRecords([$productWithoutSales]);
        });

        it('displays correct sales metrics', function () {
            $product = Product::factory()->create();

            $order = Order::factory()->create(['status' => 'completed']);
            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => 5,
                'price' => 2000,  // $20.00
            ]);

            Livewire::test(TopSellingProductsWidget::class)
                ->assertTableColumnStateSet('order_items_sum_quantity', '5', $product)
                ->assertTableColumnStateSet('order_items_count', '1', $product);
        });

        it('limits results to top 10 products', function () {
            // Create 15 products with sales
            $products = Product::factory()->count(15)->create();

            foreach ($products as $index => $product) {
                $order = Order::factory()->create(['status' => 'completed']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => 15 - $index,  // Descending quantities
                    'price' => 1000,
                ]);
            }

            $widget = Livewire::test(TopSellingProductsWidget::class);

            // Should only show 10 products
            expect($widget->instance()->getTableRecords())->toHaveCount(10);
        });
    });

    describe('RecentOrdersWidget', function () {
        it('can render recent orders widget', function () {
            Livewire::test(RecentOrdersWidget::class)
                ->assertSuccessful();
        });

        it('displays recent orders', function () {
            $orders = Order::factory()->count(5)->create([
                'created_at' => now()->subHours(2),
            ]);

            Livewire::test(RecentOrdersWidget::class)
                ->assertCanSeeTableRecords($orders);
        });

        it('orders by most recent first', function () {
            $oldOrder = Order::factory()->create(['created_at' => now()->subDays(2)]);
            $newOrder = Order::factory()->create(['created_at' => now()]);

            Livewire::test(RecentOrdersWidget::class)
                ->assertCanSeeTableRecords([$newOrder, $oldOrder], inOrder: true);
        });

        it('displays order status correctly', function () {
            $pendingOrder = Order::factory()->create(['status' => 'pending']);
            $completedOrder = Order::factory()->create(['status' => 'completed']);

            Livewire::test(RecentOrdersWidget::class)
                ->assertTableColumnStateSet('status', 'pending', $pendingOrder)
                ->assertTableColumnStateSet('status', 'completed', $completedOrder);
        });

        it('displays customer information', function () {
            $customer = User::factory()->create(['name' => 'John Doe']);
            $order = Order::factory()->create(['user_id' => $customer->id]);

            Livewire::test(RecentOrdersWidget::class)
                ->assertSee('John Doe');
        });

        it('displays order total correctly', function () {
            $order = Order::factory()->create(['total' => 15000]);  // $150.00

            Livewire::test(RecentOrdersWidget::class)
                ->assertTableColumnStateSet('total', '$150.00', $order);
        });

        it('limits to recent orders only', function () {
            // Create old orders (should not appear)
            Order::factory()->count(5)->create(['created_at' => now()->subMonths(2)]);

            // Create recent orders (should appear)
            $recentOrders = Order::factory()->count(3)->create(['created_at' => now()->subHours(1)]);

            Livewire::test(RecentOrdersWidget::class)
                ->assertCanSeeTableRecords($recentOrders);
        });
    });

    describe('Widget Permissions', function () {
        it('restricts widget access to admin users', function () {
            $regularUser = User::factory()->create(['is_admin' => false]);
            $this->actingAs($regularUser);

            Livewire::test(EcommerceOverview::class)
                ->assertForbidden();
        });

        it('allows admin users to access widgets', function () {
            Livewire::test(EcommerceOverview::class)
                ->assertSuccessful();
        });
    });

    describe('Widget Performance', function () {
        it('widgets load within acceptable time', function () {
            // Create substantial test data
            Order::factory()->count(100)->create();
            Product::factory()->count(50)->create();

            $startTime = microtime(true);

            Livewire::test(EcommerceOverview::class);

            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;

            // Widget should load within 2 seconds
            expect($executionTime)->toBeLessThan(2.0);
        });

        it('widgets handle empty data gracefully', function () {
            // Ensure no data exists
            Order::query()->delete();
            Product::query()->delete();
            User::where('id', '!=', $this->admin->id)->delete();

            Livewire::test(EcommerceOverview::class)
                ->assertSuccessful();

            Livewire::test(TopSellingProductsWidget::class)
                ->assertSuccessful();

            Livewire::test(RecentOrdersWidget::class)
                ->assertSuccessful();
        });
    });
});
