<?php declare(strict_types=1);

namespace Tests\Unit\Widgets;

use App\Filament\Widgets\RealtimeAnalyticsWidget;
use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RealtimeAnalyticsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_has_correct_heading(): void
    {
        $widget = new RealtimeAnalyticsWidget();

        $this->assertEquals(__('admin.widgets.realtime_analytics'), $widget->getHeading());
    }

    public function test_widget_has_correct_sort_order(): void
    {
        $this->assertEquals(2, RealtimeAnalyticsWidget::getSort());
    }

    public function test_widget_spans_full_column(): void
    {
        $widget = new RealtimeAnalyticsWidget();

        $this->assertEquals('full', $widget->getColumnSpan());
    }

    public function test_widget_can_be_instantiated(): void
    {
        $widget = new RealtimeAnalyticsWidget();

        $this->assertInstanceOf(RealtimeAnalyticsWidget::class, $widget);
    }

    public function test_widget_extends_chart_widget(): void
    {
        $widget = new RealtimeAnalyticsWidget();

        $this->assertInstanceOf(\Filament\Widgets\ChartWidget::class, $widget);
    }
}
