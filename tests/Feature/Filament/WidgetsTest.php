<?php declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Widgets\EnhancedEcommerceOverview;
use App\Filament\Widgets\OrdersChartWidget;
use App\Filament\Widgets\RealtimeAnalyticsWidget;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the super_admin role if it doesn't exist
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);

        // Give the user admin permissions
        $this->adminUser->assignRole('super_admin');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_widget_renders(): void
    {
        $this->actingAs($this->adminUser);

        // Create test data
        Order::factory()->count(5)->create([
            'status' => 'completed',
            'total' => 100.0,  // €100.00
            'created_at' => now(),
        ]);

        User::factory()->count(3)->create([
            'created_at' => now(),
        ]);

        Product::factory()->count(10)->create([
            'created_at' => now(),
        ]);

        Review::factory()->count(8)->create([
            'rating' => 4.5,
            'created_at' => now(),
        ]);

        $component = Livewire::test(EnhancedEcommerceOverview::class);

        $component->assertOk();

        // Test that stats are calculated
        $stats = $component->instance()->getStats();

        expect($stats)->toHaveCount(6);
        expect($stats[0]->getValue())->toContain('€');
        expect($stats[1]->getValue())->toBe('5');
        expect($stats[2]->getValue())->toBe('17');  // All users created across tests
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_calculates_revenue_correctly(): void
    {
        $this->actingAs($this->adminUser);

        // Create orders with specific amounts
        Order::factory()->create([
            'status' => 'completed',
            'total' => 150.0,  // €150.00
        ]);

        Order::factory()->create([
            'status' => 'completed',
            'total' => 250.0,  // €250.00
        ]);

        Order::factory()->create([
            'status' => 'pending',
            'total' => 100.0,  // Should not be counted
        ]);

        $widget = new EnhancedEcommerceOverview();
        $revenue = $widget->getTotalRevenue();

        expect($revenue)->toBe('€400.00');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_calculates_orders_correctly(): void
    {
        $this->actingAs($this->adminUser);

        Order::factory()->count(3)->create(['status' => 'completed']);
        Order::factory()->count(2)->create(['status' => 'pending']);
        Order::factory()->count(1)->create(['status' => 'cancelled']);

        $widget = new EnhancedEcommerceOverview();
        $orders = $widget->getTotalOrders();

        expect($orders)->toBe('6');  // All orders regardless of status
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_calculates_customers_correctly(): void
    {
        $this->actingAs($this->adminUser);

        User::factory()->count(5)->create();
        User::factory()->count(2)->create();

        $widget = new EnhancedEcommerceOverview();
        $customers = $widget->getTotalCustomers();

        expect($customers)->toBe('8');  // 5 + 2 users + 1 admin user
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_calculates_average_order_value(): void
    {
        $this->actingAs($this->adminUser);

        Order::factory()->create([
            'status' => 'completed',
            'total' => 100.0,  // €100.00
        ]);

        Order::factory()->create([
            'status' => 'completed',
            'total' => 200.0,  // €200.00
        ]);

        $widget = new EnhancedEcommerceOverview();
        $aov = $widget->getAverageOrderValue();

        expect($aov)->toBe('€150.00');  // (100 + 200) / 2
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_calculates_average_rating(): void
    {
        $this->actingAs($this->adminUser);

        Review::factory()->create(['rating' => 4.0]);
        Review::factory()->create(['rating' => 5.0]);
        Review::factory()->create(['rating' => 3.0]);

        $widget = new EnhancedEcommerceOverview();
        $rating = $widget->getAverageRating();

        expect($rating)->toBe('4.0/5');  // (4 + 5 + 3) / 3 = 4.0
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function orders_chart_widget_renders(): void
    {
        $this->actingAs($this->adminUser);

        // Create test orders
        Order::factory()->count(3)->create([
            'total' => 10000,
            'created_at' => now()->subDays(5),
        ]);

        Order::factory()->count(2)->create([
            'total' => 15000,
            'created_at' => now()->subDays(10),
        ]);

        $component = Livewire::test(OrdersChartWidget::class);

        $component->assertOk();

        // Test chart data structure
        $data = $component->instance()->getData();

        expect($data)
            ->toHaveKey('datasets')
            ->toHaveKey('labels');

        expect($data['datasets'])->toHaveCount(2);  // Orders and Revenue
        expect($data['datasets'][0])->toHaveKey('label');
        expect($data['datasets'][0])->toHaveKey('data');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function orders_chart_widget_has_correct_heading(): void
    {
        $this->actingAs($this->adminUser);

        $widget = new OrdersChartWidget();
        $heading = $widget->getHeading();

        expect($heading)->toBe(__('analytics.orders_overview'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function orders_chart_widget_has_description(): void
    {
        $this->actingAs($this->adminUser);

        $widget = new OrdersChartWidget();
        $description = $widget->getDescription();

        expect($description)->not()->toBeNull();
        expect($description)->toContain('30 dienų');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function realtime_analytics_widget_renders(): void
    {
        $this->actingAs($this->adminUser);

        $component = Livewire::test(RealtimeAnalyticsWidget::class);

        $component->assertOk();

        // Test chart data structure
        $data = $component->instance()->getData();

        expect($data)
            ->toHaveKey('datasets')
            ->toHaveKey('labels');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function realtime_analytics_widget_has_correct_heading(): void
    {
        $this->actingAs($this->adminUser);

        $widget = new RealtimeAnalyticsWidget();
        $heading = $widget->getHeading();

        expect($heading)->toBe(__('admin.widgets.realtime_analytics'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function realtime_analytics_widget_has_polling_interval(): void
    {
        $this->actingAs($this->adminUser);

        $widget = new RealtimeAnalyticsWidget();

        // Test that polling interval is set
        expect($widget->pollingInterval)->toBe('10s');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function widgets_respect_permissions(): void
    {
        // Create a user without dashboard permissions
        $regularUser = User::factory()->create([
            'email' => 'user@test.com',
        ]);

        $this->actingAs($regularUser);

        // Test OrdersChartWidget permission check
        $canView = OrdersChartWidget::canView();

        // This should return false for users without proper permissions
        expect($canView)->toBeFalse();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_handles_empty_data(): void
    {
        $this->actingAs($this->adminUser);

        // No data in database
        $widget = new EnhancedEcommerceOverview();

        $revenue = $widget->getTotalRevenue();
        $orders = $widget->getTotalOrders();
        $customers = $widget->getTotalCustomers();
        $aov = $widget->getAverageOrderValue();
        $products = $widget->getTotalProducts();
        $rating = $widget->getAverageRating();

        expect($revenue)->toBe('€0.00');
        expect($orders)->toBe('0');
        expect($customers)->toBe('1');  // Admin user created in setUp
        expect($aov)->toBe('€0.00');
        expect($products)->toBe('0');
        expect($rating)->toBe('0.0/5');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_calculates_monthly_changes(): void
    {
        $this->actingAs($this->adminUser);

        // Current month orders
        Order::factory()->count(5)->create([
            'status' => 'completed',
            'total' => 100.0,
            'created_at' => now(),
        ]);

        // Previous month orders
        Order::factory()->count(3)->create([
            'status' => 'completed',
            'total' => 100.0,
            'created_at' => now()->subMonth(),
        ]);

        $widget = new EnhancedEcommerceOverview();
        $revenueChange = $widget->getRevenueChange();
        $ordersChange = $widget->getOrdersChange();

        // Should show positive change
        expect($revenueChange)->toContain('+');
        expect($ordersChange)->toContain('+');
        expect($revenueChange)->toContain('%');
        expect($ordersChange)->toContain('%');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_shows_correct_icons_for_trends(): void
    {
        $this->actingAs($this->adminUser);

        // Current month better than previous
        Order::factory()->count(5)->create([
            'status' => 'completed',
            'created_at' => now(),
        ]);

        Order::factory()->count(3)->create([
            'status' => 'completed',
            'created_at' => now()->subMonth(),
        ]);

        $widget = new EnhancedEcommerceOverview();
        $revenueIcon = $widget->getRevenueIcon();
        $ordersIcon = $widget->getOrdersIcon();

        expect($revenueIcon)->toBe('heroicon-m-arrow-trending-up');
        expect($ordersIcon)->toBe('heroicon-m-arrow-trending-up');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function enhanced_ecommerce_overview_shows_correct_colors_for_trends(): void
    {
        $this->actingAs($this->adminUser);

        // Current month better than previous
        Order::factory()->count(5)->create([
            'status' => 'completed',
            'total' => 1000, // Higher total for current month
            'created_at' => now(),
        ]);

        Order::factory()->count(3)->create([
            'status' => 'completed',
            'total' => 500, // Lower total for previous month
            'created_at' => now()->subMonth(),
        ]);

        $widget = new EnhancedEcommerceOverview();
        $revenueColor = $widget->getRevenueColor();
        $ordersColor = $widget->getOrdersColor();

        expect($revenueColor)->toBe('success');
        expect($ordersColor)->toBe('success');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function widgets_have_correct_sort_order(): void
    {
        expect(EnhancedEcommerceOverview::$sort)->toBeNull();
        expect(RealtimeAnalyticsWidget::$sort)->toBe(2);
        expect(OrdersChartWidget::$sort)->toBe(4);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function orders_chart_widget_filters_data_by_date_range(): void
    {
        $this->actingAs($this->adminUser);

        // Orders within last 30 days
        Order::factory()->count(3)->create([
            'total' => 10000,
            'created_at' => now()->subDays(15),
        ]);

        // Orders older than 30 days (should be excluded)
        Order::factory()->count(2)->create([
            'total' => 10000,
            'created_at' => now()->subDays(45),
        ]);

        $widget = new OrdersChartWidget();
        $data = $widget->getData();

        // Should only include data from last 30 days
        $totalDataPoints = array_sum($data['datasets'][0]['data']);
        expect($totalDataPoints)->toBe(3);  // Only the 3 recent orders
    }
}
