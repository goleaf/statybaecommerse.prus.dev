<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\DashboardOverviewWidget;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use App\Models\Campaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_overview_widget_can_be_instantiated(): void
    {
        $widget = new DashboardOverviewWidget();
        $this->assertInstanceOf(DashboardOverviewWidget::class, $widget);
    }

    public function test_dashboard_overview_widget_has_correct_properties(): void
    {
        $this->assertEquals(0, DashboardOverviewWidget::getSort());
        // Test widget instantiation without direct property access
        $widget = new DashboardOverviewWidget();
        $this->assertInstanceOf(DashboardOverviewWidget::class, $widget);
    }

    public function test_dashboard_overview_widget_returns_stats_array(): void
    {
        $widget = new DashboardOverviewWidget();
        $stats = $widget->getStats();
        
        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }

    public function test_dashboard_overview_widget_handles_empty_database(): void
    {
        $widget = new DashboardOverviewWidget();
        $stats = $widget->getStats();
        
        // Should not throw exceptions with empty database
        $this->assertIsArray($stats);
    }

    public function test_dashboard_overview_widget_with_sample_data(): void
    {
        // Create sample data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100.00,
            'status' => 'completed',
            'created_at' => now()
        ]);
        
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
            'is_approved' => true,
            'created_at' => now()
        ]);
        
        $campaign = Campaign::factory()->create([
            'name' => 'Test Campaign',
            'status' => 'active',
            'created_at' => now()
        ]);

        $widget = new DashboardOverviewWidget();
        $stats = $widget->getStats();
        
        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }

    public function test_dashboard_overview_widget_stats_contain_expected_data(): void
    {
        $widget = new DashboardOverviewWidget();
        $stats = $widget->getStats();
        
        // Check that stats array contains Stat objects
        foreach ($stats as $stat) {
            $this->assertInstanceOf(\Filament\Widgets\StatsOverviewWidget\Stat::class, $stat);
        }
    }

    public function test_dashboard_overview_widget_extends_base_widget(): void
    {
        $widget = new DashboardOverviewWidget();
        
        $this->assertInstanceOf(\Filament\Widgets\StatsOverviewWidget::class, $widget);
    }

    public function test_dashboard_overview_widget_renders_successfully(): void
    {
        $widget = \Livewire\Livewire::test(DashboardOverviewWidget::class);
        $widget->assertSuccessful();
    }

    public function test_dashboard_overview_widget_has_required_methods(): void
    {
        $widget = new DashboardOverviewWidget();
        
        $this->assertTrue(method_exists($widget, 'getStats'));
    }

    public function test_dashboard_overview_widget_stats_have_labels(): void
    {
        $widget = new DashboardOverviewWidget();
        $stats = $widget->getStats();
        
        foreach ($stats as $stat) {
            $this->assertNotNull($stat->getLabel());
            $this->assertIsString($stat->getLabel());
        }
    }

    public function test_dashboard_overview_widget_stats_have_values(): void
    {
        $widget = new DashboardOverviewWidget();
        $stats = $widget->getStats();
        
        foreach ($stats as $stat) {
            $this->assertNotNull($stat->getValue());
        }
    }

    public function test_dashboard_overview_widget_handles_large_datasets(): void
    {
        // Create multiple records to test performance
        User::factory()->count(10)->create();
        Product::factory()->count(10)->create();
        Order::factory()->count(10)->create();
        
        $widget = new DashboardOverviewWidget();
        $stats = $widget->getStats();
        
        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }
}
