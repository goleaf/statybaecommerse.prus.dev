<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\UltimateStatsWidget;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Collection;
use App\Models\Review;
use App\Models\Campaign;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UltimateStatsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_ultimate_stats_widget_can_be_instantiated(): void
    {
        $widget = new UltimateStatsWidget();
        $this->assertInstanceOf(UltimateStatsWidget::class, $widget);
    }

    public function test_ultimate_stats_widget_returns_stats_array(): void
    {
        $widget = new UltimateStatsWidget();
        $stats = $widget->getStats();
        
        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }

    public function test_ultimate_stats_widget_handles_empty_database(): void
    {
        $widget = new UltimateStatsWidget();
        $stats = $widget->getStats();
        
        // Should not throw exceptions with empty database
        $this->assertIsArray($stats);
    }

    public function test_ultimate_stats_widget_with_sample_data(): void
    {
        // Create sample data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        // $collection = Collection::factory()->create(); // Commented out - column issues
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100.00,
            'status' => 'completed'
        ]);
        
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
            'is_approved' => true
        ]);
        
        $campaign = Campaign::factory()->create([
            'status' => 'active'
        ]);
        
        $systemSetting = SystemSetting::factory()->create([
            'key' => 'test_setting',
            'value' => 'test_value',
            'is_public' => true
        ]);

        $widget = new UltimateStatsWidget();
        $stats = $widget->getStats();
        
        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
        
        // Check that stats contain expected data
        $statLabels = array_map(fn($stat) => $stat->getLabel(), $stats);
        $this->assertContains('Total Revenue', $statLabels);
        $this->assertContains('Total Orders', $statLabels);
        $this->assertContains('Total Customers', $statLabels);
        $this->assertContains('Total Products', $statLabels);
    }

    public function test_ultimate_stats_widget_revenue_calculation(): void
    {
        // Create orders with different statuses
        Order::factory()->create([
            'total' => 100.00,
            'status' => 'completed'
        ]);
        
        Order::factory()->create([
            'total' => 50.00,
            'status' => 'completed'
        ]);
        
        Order::factory()->create([
            'total' => 25.00,
            'status' => 'cancelled' // Should not be included
        ]);

        $widget = new UltimateStatsWidget();
        $stats = $widget->getStats();
        
        // Find the revenue stat
        $revenueStat = collect($stats)->first(fn($stat) => $stat->getLabel() === __('translations.total_revenue'));
        $this->assertNotNull($revenueStat);
        $this->assertStringContainsString('150.00', $revenueStat->getValue());
    }

    public function test_ultimate_stats_widget_growth_calculations(): void
    {
        // Create old orders (last month)
        Order::factory()->create([
            'total' => 100.00,
            'created_at' => now()->subMonth()
        ]);
        
        // Create recent orders (this month)
        Order::factory()->create([
            'total' => 200.00,
            'created_at' => now()
        ]);

        $widget = new UltimateStatsWidget();
        $stats = $widget->getStats();
        
        // Check that growth indicators are present
        $revenueStat = collect($stats)->first(fn($stat) => $stat->getLabel() === __('translations.total_revenue'));
        $this->assertNotNull($revenueStat);
        $this->assertNotNull($revenueStat->getDescription());
    }

    public function test_ultimate_stats_widget_chart_data(): void
    {
        $widget = new UltimateStatsWidget();
        
        // Test revenue chart
        $revenueChart = $widget->getRevenueChart();
        $this->assertIsArray($revenueChart);
        $this->assertCount(7, $revenueChart); // 7 days of data
        
        // Test orders chart
        $ordersChart = $widget->getOrdersChart();
        $this->assertIsArray($ordersChart);
        $this->assertCount(7, $ordersChart); // 7 days of data
    }

    public function test_ultimate_stats_widget_column_span(): void
    {
        $widget = new UltimateStatsWidget();
        $this->assertEquals('full', $widget->getColumnSpan());
    }

    public function test_ultimate_stats_widget_sort_order(): void
    {
        $this->assertEquals(-1, UltimateStatsWidget::getSort());
    }
}
