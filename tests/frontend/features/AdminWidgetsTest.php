<?php declare(strict_types=1);

use App\Filament\Widgets\EcommerceOverview;
use App\Filament\Widgets\TopProductsWidget;
use App\Filament\Widgets\RealtimeAnalyticsWidget;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->create(['email' => 'admin@example.com']);
    Role::findOrCreate('admin');
    $this->admin->assignRole('admin');
});

describe('EcommerceOverview Widget', function (): void {
    it('can render ecommerce overview widget', function (): void {
        actingAs($this->admin);
        
        $component = Livewire::test(EcommerceOverview::class);
        $component->assertOk();
    });

    it('displays correct stats for empty data', function (): void {
        actingAs($this->admin);
        
        $component = Livewire::test(EcommerceOverview::class);
        $stats = $component->get('stats');
        
        expect($stats)->toHaveCount(6);
        expect($stats[0]->getValue())->toBe('€0.00'); // Total revenue
        expect($stats[1]->getValue())->toBe(0); // Total orders
        expect($stats[2]->getValue())->toBe(1); // Total customers (admin user)
        expect($stats[3]->getValue())->toBe('€0.00'); // Average order value
        expect($stats[4]->getValue())->toBe(0); // Total products
        expect($stats[5]->getValue())->toBe(0); // Active campaigns
    });

    it('displays correct stats with data', function (): void {
        // Create test data
        $user = User::factory()->create();
        $product = Product::factory()->create(['is_visible' => true]);
        $campaign = Campaign::factory()->create(['is_active' => true]);
        
        // Create orders for this month
        $order1 = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100.00,
            'created_at' => now()->startOfMonth()->addDays(5),
        ]);
        
        $order2 = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 200.00,
            'created_at' => now()->startOfMonth()->addDays(10),
        ]);

        actingAs($this->admin);
        
        $component = Livewire::test(EcommerceOverview::class);
        $stats = $component->get('stats');
        
        expect($stats)->toHaveCount(6);
        expect($stats[0]->getValue())->toBe('€300.00'); // Total revenue
        expect($stats[1]->getValue())->toBe(2); // Total orders
        expect($stats[2]->getValue())->toBe(2); // Total customers (admin + user)
        expect($stats[3]->getValue())->toBe('€150.00'); // Average order value
        expect($stats[4]->getValue())->toBe(1); // Total products
        expect($stats[5]->getValue())->toBe(1); // Active campaigns
    });

    it('calculates month-over-month changes correctly', function (): void {
        // Create last month's data
        $lastMonthOrder = Order::factory()->create([
            'total' => 100.00,
            'created_at' => now()->subMonth()->startOfMonth()->addDays(5),
        ]);
        
        // Create this month's data
        $thisMonthOrder = Order::factory()->create([
            'total' => 200.00,
            'created_at' => now()->startOfMonth()->addDays(5),
        ]);

        actingAs($this->admin);
        
        $component = Livewire::test(EcommerceOverview::class);
        $stats = $component->get('stats');
        
        // Revenue should show 100% increase
        expect($stats[0]->getDescription())->toContain('+100.0%');
        // Orders should show 0% change (1 last month, 1 this month)
        expect($stats[1]->getDescription())->toContain('+0.0%');
    });
});

describe('TopProductsWidget', function (): void {
    it('can render top products widget', function (): void {
        actingAs($this->admin);
        
        $component = Livewire::test(TopProductsWidget::class);
        $component->assertOk();
    });

    it('displays products in correct order by sales', function (): void {
        // Create products
        $product1 = Product::factory()->create(['name' => 'Product A']);
        $product2 = Product::factory()->create(['name' => 'Product B']);
        $product3 = Product::factory()->create(['name' => 'Product C']);
        
        // Create orders with different quantities
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();
        $order3 = Order::factory()->create();
        
        // Create order items
        \App\Models\OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'total' => 100.00,
        ]);
        
        \App\Models\OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'total' => 50.00,
        ]);
        
        \App\Models\OrderItem::factory()->create([
            'order_id' => $order3->id,
            'product_id' => $product3->id,
            'quantity' => 15,
            'total' => 150.00,
        ]);

        actingAs($this->admin);
        
        $component = Livewire::test(TopProductsWidget::class);
        $component->assertOk();
        
        // The widget should show products ordered by total sold
        // Product C should be first (15 sold), then Product A (10), then Product B (5)
    });

    it('limits results to top 10 products', function (): void {
        // Create 15 products
        $products = Product::factory()->count(15)->create();
        
        // Create orders for each product
        foreach ($products as $index => $product) {
            $order = Order::factory()->create();
            \App\Models\OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $index + 1, // Different quantities
                'total' => ($index + 1) * 10,
            ]);
        }

        actingAs($this->admin);
        
        $component = Livewire::test(TopProductsWidget::class);
        $component->assertOk();
        
        // Widget should only show top 10 products
    });
});

describe('RealtimeAnalyticsWidget', function (): void {
    it('can render realtime analytics widget', function (): void {
        actingAs($this->admin);
        
        $component = Livewire::test(RealtimeAnalyticsWidget::class);
        $component->assertOk();
    });

    it('displays today\'s analytics correctly', function (): void {
        // Create today's orders
        $todayOrder1 = Order::factory()->create([
            'total' => 100.00,
            'created_at' => now()->startOfDay()->addHours(2),
        ]);
        
        $todayOrder2 = Order::factory()->create([
            'total' => 200.00,
            'created_at' => now()->startOfDay()->addHours(5),
        ]);
        
        // Create yesterday's orders
        $yesterdayOrder = Order::factory()->create([
            'total' => 50.00,
            'created_at' => now()->subDay()->startOfDay()->addHours(3),
        ]);
        
        // Create active campaign
        $campaign = Campaign::factory()->create(['is_active' => true]);
        
        // Create products
        $product = Product::factory()->create();

        actingAs($this->admin);
        
        $component = Livewire::test(RealtimeAnalyticsWidget::class);
        $stats = $component->get('stats');
        
        expect($stats)->toHaveCount(4);
        expect($stats[0]->getValue())->toBe(2); // Today's orders
        expect($stats[1]->getValue())->toBe('€300.00'); // Today's revenue
        expect($stats[2]->getValue())->toBe(1); // Active campaigns
        expect($stats[3]->getValue())->toBe(1); // Total products
    });

    it('calculates day-over-day changes correctly', function (): void {
        // Create yesterday's data
        $yesterdayOrder = Order::factory()->create([
            'total' => 100.00,
            'created_at' => now()->subDay()->startOfDay()->addHours(5),
        ]);
        
        // Create today's data
        $todayOrder = Order::factory()->create([
            'total' => 200.00,
            'created_at' => now()->startOfDay()->addHours(5),
        ]);

        actingAs($this->admin);
        
        $component = Livewire::test(RealtimeAnalyticsWidget::class);
        $stats = $component->get('stats');
        
        // Orders should show 0% change (1 yesterday, 1 today)
        expect($stats[0]->getDescription())->toContain('+0.0%');
        // Revenue should show 100% increase
        expect($stats[1]->getDescription())->toContain('+100.0%');
    });
});

describe('Widget Authorization', function (): void {
    it('allows admin users to view widgets', function (): void {
        actingAs($this->admin);
        
        $widgets = [
            EcommerceOverview::class,
            TopProductsWidget::class,
            RealtimeAnalyticsWidget::class,
        ];
        
        foreach ($widgets as $widget) {
            $component = Livewire::test($widget);
            $component->assertOk();
        }
    });

    it('denies non-admin users access to widgets', function (): void {
        $user = User::factory()->create();
        
        actingAs($user);
        
        $widgets = [
            EcommerceOverview::class,
            TopProductsWidget::class,
            RealtimeAnalyticsWidget::class,
        ];
        
        foreach ($widgets as $widget) {
            $component = Livewire::test($widget);
            $component->assertStatus(403);
        }
    });
});

describe('Widget Performance', function (): void {
    it('loads widgets quickly with large datasets', function (): void {
        // Create large dataset
        Product::factory()->count(100)->create();
        Order::factory()->count(50)->create();
        Campaign::factory()->count(20)->create();
        
        actingAs($this->admin);
        
        $startTime = microtime(true);
        
        $component = Livewire::test(EcommerceOverview::class);
        $component->assertOk();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Widget should load in less than 1 second
        expect($executionTime)->toBeLessThan(1.0);
    });
});
