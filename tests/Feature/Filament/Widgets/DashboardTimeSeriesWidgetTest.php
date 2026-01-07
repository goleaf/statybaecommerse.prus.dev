<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Widgets;

use App\Filament\Widgets\DashboardTimeSeriesWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use Tests\TestCase;

#[CoversClass(DashboardTimeSeriesWidget::class)]
final class DashboardTimeSeriesWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['dashboard.permissions.view_charts' => 'dashboard.view_charts']);
    }

    public function test_widget_can_be_instantiated(): void
    {
        $widget = new DashboardTimeSeriesWidget;

        $this->assertInstanceOf(DashboardTimeSeriesWidget::class, $widget);
    }

    public function test_widget_has_correct_sort_order(): void
    {
        $reflection = new ReflectionClass(DashboardTimeSeriesWidget::class);
        $property = $reflection->getProperty('sort');
        $property->setAccessible(true);

        $this->assertEquals(2, $property->getValue());
    }

    public function test_widget_has_correct_column_span(): void
    {
        $widget = new DashboardTimeSeriesWidget;

        $reflection = new ReflectionClass($widget);
        $property = $reflection->getProperty('columnSpan');
        $property->setAccessible(true);

        $expectedSpan = ['md' => 2, 'xl' => 2];
        $this->assertEquals($expectedSpan, $property->getValue($widget));
    }

    public function test_authorized_user_can_view_widget(): void
    {
        Gate::define('dashboard.view_charts', fn () => true);

        $this->assertTrue(DashboardTimeSeriesWidget::canView());
    }

    public function test_unauthorized_user_cannot_view_widget(): void
    {
        Gate::define('dashboard.view_charts', fn () => false);

        $this->assertFalse(DashboardTimeSeriesWidget::canView());
    }

    public function test_widget_renders_successfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('dashboard.view_charts', fn () => true);

        Livewire::test(DashboardTimeSeriesWidget::class)
            ->assertSuccessful();
    }

    public function test_widget_has_correct_chart_type(): void
    {
        $widget = new DashboardTimeSeriesWidget;

        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getType');
        $method->setAccessible(true);

        $this->assertEquals('line', $method->invoke($widget));
    }

    public function test_widget_heading_and_description(): void
    {
        $widget = new DashboardTimeSeriesWidget;

        $this->assertEquals(trans('admin/dashboard.charts.heading'), $widget->getHeading());
        $this->assertEquals(trans('admin/dashboard.charts.description'), $widget->getDescription());
    }

    public function test_widget_chart_options_configuration(): void
    {
        $widget = new DashboardTimeSeriesWidget;

        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);

        $options = $method->invoke($widget);

        $this->assertTrue($options['responsive']);
        $this->assertFalse($options['maintainAspectRatio']);
        $this->assertEquals('index', $options['interaction']['mode']);
        $this->assertFalse($options['interaction']['intersect']);
    }

    public function test_widget_has_dual_y_axes_configuration(): void
    {
        $widget = new DashboardTimeSeriesWidget;

        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);

        $options = $method->invoke($widget);

        // Check primary y-axis (orders)
        $this->assertEquals('linear', $options['scales']['y']['type']);
        $this->assertEquals('left', $options['scales']['y']['position']);
        $this->assertTrue($options['scales']['y']['display']);

        // Check secondary y-axis (revenue)
        $this->assertEquals('linear', $options['scales']['y1']['type']);
        $this->assertEquals('right', $options['scales']['y1']['position']);
        $this->assertTrue($options['scales']['y1']['display']);
        $this->assertFalse($options['scales']['y1']['grid']['drawOnChartArea']);
    }

    public function test_widget_legend_and_tooltip_configuration(): void
    {
        $widget = new DashboardTimeSeriesWidget;

        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);

        $options = $method->invoke($widget);

        $this->assertTrue($options['plugins']['legend']['display']);
        $this->assertEquals('bottom', $options['plugins']['legend']['position']);
        $this->assertTrue($options['plugins']['tooltip']['enabled']);
    }

    public function test_widget_extends_chart_widget(): void
    {
        $widget = new DashboardTimeSeriesWidget;

        $this->assertInstanceOf(\Filament\Widgets\ChartWidget::class, $widget);
    }

    public function test_widget_data_method_returns_array(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('dashboard.view_charts', fn () => true);

        $widget = new DashboardTimeSeriesWidget;

        $reflection = new ReflectionClass($widget);
        $method = $reflection->getMethod('getData');
        $method->setAccessible(true);

        $data = $method->invoke($widget);

        $this->assertIsArray($data);
    }

    public function test_widget_permission_configuration_exists(): void
    {
        $permission = config('dashboard.permissions.view_charts');
        $this->assertNotEmpty($permission);
        $this->assertEquals('dashboard.view_charts', $permission);
    }

    public function test_widget_translation_keys_exist(): void
    {
        $this->assertNotEmpty(trans('admin/dashboard.charts.heading'));
        $this->assertNotEmpty(trans('admin/dashboard.charts.description'));
        $this->assertNotEmpty(trans('admin/dashboard.charts.orders_axis'));
        $this->assertNotEmpty(trans('admin/dashboard.charts.revenue_axis'));
    }
}
