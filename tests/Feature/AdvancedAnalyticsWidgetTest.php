<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\AdvancedAnalyticsWidget;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedAnalyticsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_advanced_analytics_widget_can_be_instantiated(): void
    {
        $widget = new AdvancedAnalyticsWidget();
        $this->assertInstanceOf(AdvancedAnalyticsWidget::class, $widget);
    }

    public function test_advanced_analytics_widget_has_correct_properties(): void
    {
        $this->assertEquals(2, AdvancedAnalyticsWidget::getSort());
        // Test widget instantiation without direct property access
        $widget = new AdvancedAnalyticsWidget();
        $this->assertInstanceOf(AdvancedAnalyticsWidget::class, $widget);
    }

    public function test_advanced_analytics_widget_returns_data_array(): void
    {
        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_advanced_analytics_widget_handles_empty_database(): void
    {
        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        // Should not throw exceptions with empty database
        $this->assertIsArray($data);
    }

    public function test_advanced_analytics_widget_with_sample_data(): void
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

        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_advanced_analytics_widget_data_structure(): void
    {
        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        // Check that data has expected structure
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertIsArray($data['datasets']);
        $this->assertIsArray($data['labels']);
    }

    public function test_advanced_analytics_widget_extends_chart_widget(): void
    {
        $widget = new AdvancedAnalyticsWidget();

        $this->assertInstanceOf(\Filament\Widgets\ChartWidget::class, $widget);
    }

    public function test_advanced_analytics_widget_renders_successfully(): void
    {
        $widget = \Livewire\Livewire::test(AdvancedAnalyticsWidget::class);
        $widget->assertSuccessful();
    }

    public function test_advanced_analytics_widget_has_required_methods(): void
    {
        $widget = new AdvancedAnalyticsWidget();

        $this->assertTrue(method_exists($widget, 'getData'));
        $this->assertTrue(method_exists($widget, 'getType'));
        $this->assertTrue(method_exists($widget, 'getOptions'));
    }

    public function test_advanced_analytics_widget_chart_type(): void
    {
        $widget = new AdvancedAnalyticsWidget();
        $type = $widget->getType();

        $this->assertIsString($type);
        $this->assertNotEmpty($type);
    }

    public function test_advanced_analytics_widget_chart_options(): void
    {
        $widget = new AdvancedAnalyticsWidget();
        $options = $widget->getOptions();

        $this->assertIsArray($options);
    }

    public function test_advanced_analytics_widget_max_height(): void
    {
        $widget = new AdvancedAnalyticsWidget();
        $maxHeight = $widget->getMaxHeight();

        $this->assertEquals('400px', $maxHeight);
    }

    public function test_advanced_analytics_widget_revenue_data(): void
    {
        // Create orders for revenue data
        Order::factory()->create([
            'total' => 100.0,
            'status' => 'completed',
            'created_at' => now()
        ]);

        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_advanced_analytics_widget_orders_data(): void
    {
        // Create orders
        Order::factory()->count(3)->create(['created_at' => now()]);

        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_advanced_analytics_widget_users_data(): void
    {
        // Create users
        User::factory()->count(5)->create(['created_at' => now()]);

        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_advanced_analytics_widget_handles_large_datasets(): void
    {
        // Create multiple records to test performance
        User::factory()->count(20)->create();
        Product::factory()->count(20)->create();
        Order::factory()->count(20)->create();

        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_advanced_analytics_widget_data_labels(): void
    {
        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        if (isset($data['labels'])) {
            $this->assertIsArray($data['labels']);
            $this->assertNotEmpty($data['labels']);
        }
    }

    public function test_advanced_analytics_widget_data_datasets(): void
    {
        $widget = new AdvancedAnalyticsWidget();
        $data = $widget->getData();

        if (isset($data['datasets'])) {
            $this->assertIsArray($data['datasets']);
            $this->assertNotEmpty($data['datasets']);
        }
    }
}
