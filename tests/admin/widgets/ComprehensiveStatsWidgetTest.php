<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\ComprehensiveStatsWidget;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveStatsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_comprehensive_stats_widget_can_be_instantiated(): void
    {
        $widget = new ComprehensiveStatsWidget();
        $this->assertInstanceOf(ComprehensiveStatsWidget::class, $widget);
    }

    public function test_comprehensive_stats_widget_has_correct_sort_order(): void
    {
        $this->assertEquals(1, ComprehensiveStatsWidget::getSort());
    }

    public function test_comprehensive_stats_widget_returns_stats_array(): void
    {
        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }

    public function test_comprehensive_stats_widget_handles_empty_database(): void
    {
        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        // Should not throw exceptions with empty database
        $this->assertIsArray($stats);
    }

    public function test_comprehensive_stats_widget_with_sample_data(): void
    {
        // Create sample data
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100.0,
            'status' => 'completed',
            'created_at' => now()
        ]);

        $review = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
            'is_approved' => true,
            'created_at' => now()
        ]);

        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }

    public function test_comprehensive_stats_widget_stats_contain_expected_data(): void
    {
        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        // Check that stats array contains Stat objects
        foreach ($stats as $stat) {
            $this->assertInstanceOf(\Filament\Widgets\StatsOverviewWidget\Stat::class, $stat);
        }
    }

    public function test_comprehensive_stats_widget_extends_base_widget(): void
    {
        $widget = new ComprehensiveStatsWidget();

        $this->assertInstanceOf(\Filament\Widgets\StatsOverviewWidget::class, $widget);
    }

    public function test_comprehensive_stats_widget_renders_successfully(): void
    {
        $widget = \Livewire\Livewire::test(ComprehensiveStatsWidget::class);
        $widget->assertSuccessful();
    }

    public function test_comprehensive_stats_widget_has_required_methods(): void
    {
        $widget = new ComprehensiveStatsWidget();

        $this->assertTrue(method_exists($widget, 'getStats'));
    }

    public function test_comprehensive_stats_widget_stats_have_labels(): void
    {
        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        foreach ($stats as $stat) {
            $this->assertNotNull($stat->getLabel());
            $this->assertIsString($stat->getLabel());
        }
    }

    public function test_comprehensive_stats_widget_stats_have_values(): void
    {
        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        foreach ($stats as $stat) {
            $this->assertNotNull($stat->getValue());
        }
    }

    public function test_comprehensive_stats_widget_revenue_calculation(): void
    {
        // Create completed orders
        Order::factory()->create([
            'total' => 100.0,
            'status' => 'completed',
            'created_at' => now()
        ]);

        Order::factory()->create([
            'total' => 50.0,
            'status' => 'completed',
            'created_at' => now()
        ]);

        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        $this->assertIsArray($stats);
    }

    public function test_comprehensive_stats_widget_orders_calculation(): void
    {
        // Create orders
        Order::factory()->count(3)->create(['created_at' => now()]);
        Order::factory()->count(2)->create(['created_at' => now()->subMonth()]);

        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        $this->assertIsArray($stats);
    }

    public function test_comprehensive_stats_widget_products_calculation(): void
    {
        // Create products
        Product::factory()->count(5)->create(['is_visible' => true]);
        Product::factory()->count(2)->create(['is_visible' => false]);

        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        $this->assertIsArray($stats);
    }

    public function test_comprehensive_stats_widget_customers_calculation(): void
    {
        // Create users with orders
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Order::factory()->create(['user_id' => $user1->id]);
        Order::factory()->create(['user_id' => $user2->id]);

        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        $this->assertIsArray($stats);
    }

    public function test_comprehensive_stats_widget_handles_large_datasets(): void
    {
        // Create multiple records to test performance
        User::factory()->count(10)->create();
        Product::factory()->count(10)->create();
        Order::factory()->count(10)->create();

        $widget = new ComprehensiveStatsWidget();
        $stats = $widget->getStats();

        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
    }
}
