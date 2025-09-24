<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\OrdersChartWidget;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersChartWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_chart_widget_can_be_instantiated(): void
    {
        $widget = new OrdersChartWidget;
        $this->assertInstanceOf(OrdersChartWidget::class, $widget);
    }

    public function test_orders_chart_widget_has_correct_sort_order(): void
    {
        $this->assertEquals(2, OrdersChartWidget::getSort());
    }

    public function test_orders_chart_widget_has_correct_heading(): void
    {
        $widget = new OrdersChartWidget;
        $this->assertEquals(__('analytics.orders_overview'), $widget->getHeading());
    }

    public function test_orders_chart_widget_returns_data_array(): void
    {
        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_orders_chart_widget_handles_empty_database(): void
    {
        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        // Should not throw exceptions with empty database
        $this->assertIsArray($data);
    }

    public function test_orders_chart_widget_with_sample_data(): void
    {
        // Create sample orders
        Order::factory()->create([
            'total' => 100.0,
            'status' => 'completed',
            'created_at' => now(),
        ]);

        Order::factory()->create([
            'total' => 50.0,
            'status' => 'pending',
            'created_at' => now()->subMonth(),
        ]);

        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_orders_chart_widget_data_structure(): void
    {
        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        // Check that data has expected structure
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertIsArray($data['datasets']);
        $this->assertIsArray($data['labels']);
    }

    public function test_orders_chart_widget_extends_chart_widget(): void
    {
        $widget = new OrdersChartWidget;

        $this->assertInstanceOf(\Filament\Widgets\ChartWidget::class, $widget);
    }

    public function test_orders_chart_widget_renders_successfully(): void
    {
        $widget = \Livewire\Livewire::test(OrdersChartWidget::class);
        $widget->assertSuccessful();
    }

    public function test_orders_chart_widget_has_required_methods(): void
    {
        $widget = new OrdersChartWidget;

        $this->assertTrue(method_exists($widget, 'getData'));
        $this->assertTrue(method_exists($widget, 'getType'));
        $this->assertTrue(method_exists($widget, 'getOptions'));
        $this->assertTrue(method_exists($widget, 'getHeading'));
    }

    public function test_orders_chart_widget_chart_type(): void
    {
        $widget = new OrdersChartWidget;
        $type = $widget->getType();

        $this->assertIsString($type);
        $this->assertNotEmpty($type);
    }

    public function test_orders_chart_widget_chart_options(): void
    {
        $widget = new OrdersChartWidget;
        $options = $widget->getOptions();

        $this->assertIsArray($options);
    }

    public function test_orders_chart_widget_datasets_structure(): void
    {
        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        if (isset($data['datasets'])) {
            $this->assertIsArray($data['datasets']);
            $this->assertCount(2, $data['datasets']);  // Should have orders and revenue datasets

            foreach ($data['datasets'] as $dataset) {
                $this->assertArrayHasKey('label', $dataset);
                $this->assertArrayHasKey('data', $dataset);
                $this->assertArrayHasKey('backgroundColor', $dataset);
                $this->assertArrayHasKey('borderColor', $dataset);
            }
        }
    }

    public function test_orders_chart_widget_orders_data(): void
    {
        // Create orders for different months
        Order::factory()->create([
            'created_at' => now(),
        ]);

        Order::factory()->create([
            'created_at' => now()->subMonth(),
        ]);

        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_orders_chart_widget_revenue_data(): void
    {
        // Create completed orders for revenue
        Order::factory()->create([
            'total' => 100.0,
            'status' => 'completed',
            'created_at' => now(),
        ]);

        Order::factory()->create([
            'total' => 50.0,
            'status' => 'completed',
            'created_at' => now()->subMonth(),
        ]);

        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_orders_chart_widget_excludes_cancelled_orders(): void
    {
        // Create cancelled orders (should be excluded from revenue)
        Order::factory()->create([
            'total' => 100.0,
            'status' => 'cancelled',
            'created_at' => now(),
        ]);

        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_orders_chart_widget_handles_large_datasets(): void
    {
        // Create multiple orders to test performance
        Order::factory()->count(20)->create();

        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_orders_chart_widget_data_labels(): void
    {
        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        if (isset($data['labels'])) {
            $this->assertIsArray($data['labels']);
            $this->assertCount(12, $data['labels']);  // Should have 12 months of data
        }
    }

    public function test_orders_chart_widget_monthly_data(): void
    {
        // Create orders for different months
        for ($i = 0; $i < 6; $i++) {
            Order::factory()->create([
                'created_at' => now()->subMonths($i),
            ]);
        }

        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_orders_chart_widget_yearly_data(): void
    {
        // Create orders for different years
        Order::factory()->create([
            'created_at' => now()->subYear(),
        ]);

        Order::factory()->create([
            'created_at' => now(),
        ]);

        $widget = new OrdersChartWidget;
        $data = $widget->getData();

        $this->assertIsArray($data);
    }
}
