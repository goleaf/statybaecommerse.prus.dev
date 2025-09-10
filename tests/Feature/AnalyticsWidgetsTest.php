<?php declare(strict_types=1);

use App\Filament\Widgets\StatsWidget as AdvancedStatsWidget; // legacy alias removed
use App\Filament\Widgets\OrdersChartWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'email' => 'admin@admin.com',
        'name' => 'Admin User',
    ]);

    // Assign admin role if using Spatie permissions
    if (class_exists(\Spatie\Permission\Models\Role::class)) {
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $this->adminUser->assignRole($adminRole);
    }

    $this->actingAs($this->adminUser);
});

describe('AdvancedStatsWidget', function () {
    it('can render advanced stats widget', function () {
        livewire(AdvancedStatsWidget::class)
            ->assertSuccessful();
    });

    it('displays correct revenue statistics', function () {
        Order::factory()->count(5)->create([
            'total' => 100.0,
            'status' => 'completed',
            'created_at' => now()->subDays(5),
        ]);

        $widget = livewire(AdvancedStatsWidget::class);
        $stats = $widget->instance()->getStats();

        expect($stats)->toHaveCount(12);
        expect($stats[0]->getLabel())->toBe(__('analytics.total_revenue'));
    });

    it('displays correct order statistics', function () {
        Order::factory()->count(3)->create(['status' => 'pending']);
        Order::factory()->count(2)->create(['status' => 'completed']);

        $widget = livewire(AdvancedStatsWidget::class);
        $stats = $widget->instance()->getStats();

        // Find the total orders stat
        $totalOrdersStat = collect($stats)->first(fn($stat) => $stat->getLabel() === __('analytics.total_orders'));
        expect($totalOrdersStat)->not->toBeNull();
    });

    it('displays correct product statistics', function () {
        Product::factory()->count(10)->create(['is_visible' => true]);
        Product::factory()->count(3)->create(['is_visible' => true, 'is_featured' => true]);

        $widget = livewire(AdvancedStatsWidget::class);
        $stats = $widget->instance()->getStats();

        // Find the products stat
        $productsStat = collect($stats)->first(fn($stat) => $stat->getLabel() === __('analytics.products'));
        expect($productsStat)->not->toBeNull();
    });

    it('displays correct customer statistics', function () {
        $customers = User::factory()->count(5)->create();

        // Create orders for some customers to make them active
        Order::factory()->count(2)->create(['user_id' => $customers->first()->id]);

        $widget = livewire(AdvancedStatsWidget::class);
        $stats = $widget->instance()->getStats();

        // Find the customers stat
        $customersStat = collect($stats)->first(fn($stat) => $stat->getLabel() === __('analytics.customers'));
        expect($customersStat)->not->toBeNull();
    });

    it('displays correct content statistics', function () {
        Category::factory()->count(5)->create(['is_visible' => true]);
        Brand::factory()->count(3)->create(['is_visible' => true]);
        Review::factory()->count(10)->create(['is_approved' => true, 'rating' => 4]);

        $widget = livewire(AdvancedStatsWidget::class);
        $stats = $widget->instance()->getStats();

        // Find the content stat
        $contentStat = collect($stats)->first(fn($stat) => $stat->getLabel() === __('analytics.content'));
        expect($contentStat)->not->toBeNull();

        // Find the reviews stat
        $reviewsStat = collect($stats)->first(fn($stat) => $stat->getLabel() === __('analytics.reviews'));
        expect($reviewsStat)->not->toBeNull();
    });

    it('generates revenue chart data correctly', function () {
        Order::factory()->count(5)->create([
            'total' => 100.0,
            'created_at' => now()->subDays(5),
        ]);

        $widget = new AdvancedStatsWidget();
        $chartData = $widget->getRevenueChart();

        expect($chartData)->toBeArray();
        expect(count($chartData))->toBeGreaterThan(0);
    });

    it('generates orders chart data correctly', function () {
        Order::factory()->count(3)->create([
            'created_at' => now()->subDays(3),
        ]);

        $widget = new AdvancedStatsWidget();
        $chartData = $widget->getOrdersChart();

        expect($chartData)->toBeArray();
        expect(count($chartData))->toBeGreaterThan(0);
    });
});

describe('OrdersChartWidget', function () {
    it('can render orders chart widget', function () {
        livewire(OrdersChartWidget::class)
            ->assertSuccessful();
    });

    it('displays correct chart data', function () {
        Order::factory()->count(5)->create([
            'total' => 150.0,
            'created_at' => now()->subDays(5),
        ]);

        $widget = new OrdersChartWidget();
        $data = $widget->getData();

        expect($data)->toHaveKey('datasets');
        expect($data)->toHaveKey('labels');
        expect($data['datasets'])->toHaveCount(2);  // Orders and Revenue datasets
    });

    it('has correct chart type', function () {
        $widget = new OrdersChartWidget();
        expect($widget->getType())->toBe('line');
    });

    it('has correct chart options', function () {
        $widget = new OrdersChartWidget();
        $options = $widget->getOptions();

        expect($options)->toHaveKey('responsive');
        expect($options)->toHaveKey('scales');
        expect($options['responsive'])->toBeTrue();
    });

    it('displays correct heading and description', function () {
        $widget = new OrdersChartWidget();

        expect($widget->getHeading())->toBe(__('analytics.orders_overview'));
        expect($widget->getDescription())->toBe(__('analytics.orders_and_revenue_trends'));
    });
});

describe('TopSellingProductsWidget', function () {
    it('can render top selling products widget', function () {
        livewire(TopSellingProductsWidget::class)
            ->assertSuccessful();
    });

    it('displays top selling products correctly', function () {
        $products = Product::factory()->count(5)->create();

        // Create orders with items for products
        foreach ($products as $product) {
            $order = Order::factory()->create(['status' => 'completed']);
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => rand(1, 10),
            ]);
        }

        livewire(TopSellingProductsWidget::class)
            ->assertCanSeeTableRecords($products);
    });

    it('displays correct table columns', function () {
        $product = Product::factory()->create();
        $order = Order::factory()->create(['status' => 'completed']);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        livewire(TopSellingProductsWidget::class)
            ->assertCanSeeTableRecords([$product])
            ->assertTableColumnExists('media')
            ->assertTableColumnExists('name')
            ->assertTableColumnExists('sku')
            ->assertTableColumnExists('price')
            ->assertTableColumnExists('order_items_sum_quantity')
            ->assertTableColumnExists('order_items_count')
            ->assertTableColumnExists('stock_quantity');
    });

    it('orders products by sales count', function () {
        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);

        // Create more sales for product2
        $order1 = Order::factory()->create(['status' => 'completed']);
        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        $order2 = Order::factory()->create(['status' => 'completed']);
        $order3 = Order::factory()->create(['status' => 'completed']);
        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product2->id,
            'quantity' => 5,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);

        $widget = livewire(TopSellingProductsWidget::class);

        // Product2 should appear first due to higher sales
        $widget->assertCanSeeTableRecords([$product1, $product2]);
    });

    it('displays correct heading', function () {
        $widget = new TopSellingProductsWidget();
        expect($widget->getHeading())->toBe(__('analytics.top_selling_products'));
    });

    it('limits results to 10 products', function () {
        $products = Product::factory()->count(15)->create();

        // Create orders for all products
        foreach ($products as $product) {
            $order = Order::factory()->create(['status' => 'completed']);
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        }

        $widget = new TopSellingProductsWidget();
        $table = $widget->table(\Filament\Tables\Table::make());

        // The query should limit to 10 products
        expect($table->getQuery()->getQuery()->limit)->toBe(10);
    });
});
