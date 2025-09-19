<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\SliderAnalytics\Widgets\SliderViewsTimeline;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SliderViewsTimelineWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create test sliders
        $this->createTestSliders();
    }

    private function createTestSliders(): void
    {
        // Create active sliders
        Slider::factory()->create([
            'title' => 'Active Slider 1',
            'is_active' => true,
            'created_at' => now()->subDays(5),
        ]);

        Slider::factory()->create([
            'title' => 'Active Slider 2',
            'is_active' => true,
            'created_at' => now()->subDays(3),
        ]);

        Slider::factory()->create([
            'title' => 'Inactive Slider',
            'is_active' => false,
            'created_at' => now()->subDays(1),
        ]);
    }

    public function test_slider_views_timeline_widget_can_be_instantiated(): void
    {
        $widget = new SliderViewsTimeline();
        $this->assertInstanceOf(SliderViewsTimeline::class, $widget);
    }

    public function test_slider_views_timeline_widget_has_correct_heading(): void
    {
        $widget = new SliderViewsTimeline();
        $reflection = new \ReflectionClass($widget);
        $headingProperty = $reflection->getProperty('heading');
        $headingProperty->setAccessible(true);
        $this->assertEquals('Slider Views Timeline', $headingProperty->getValue($widget));
    }

    public function test_slider_views_timeline_widget_has_correct_sort(): void
    {
        $reflection = new \ReflectionClass(SliderViewsTimeline::class);
        $sortProperty = $reflection->getProperty('sort');
        $sortProperty->setAccessible(true);
        $this->assertEquals(6, $sortProperty->getValue());
    }

    public function test_slider_views_timeline_widget_has_correct_column_span(): void
    {
        $widget = new SliderViewsTimeline();
        $reflection = new \ReflectionClass($widget);
        $columnSpanProperty = $reflection->getProperty('columnSpan');
        $columnSpanProperty->setAccessible(true);
        $this->assertEquals([
            'md' => 2,
            'xl' => 1,
        ], $columnSpanProperty->getValue($widget));
    }

    public function test_slider_views_timeline_widget_can_render(): void
    {
        Livewire::test(SliderViewsTimeline::class)
            ->assertOk();
    }

    public function test_slider_views_timeline_widget_shows_heading(): void
    {
        Livewire::test(SliderViewsTimeline::class)
            ->assertSee('Slider Views Timeline');
    }

    public function test_slider_views_timeline_widget_has_chart_data(): void
    {
        $widget = new SliderViewsTimeline();
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertCount(2, $data['datasets']);  // Views and Clicks
        $this->assertCount(30, $data['labels']);  // 30 days
    }

    public function test_slider_views_timeline_widget_has_correct_chart_type(): void
    {
        $widget = new SliderViewsTimeline();
        $this->assertEquals('line', $widget->getType());
    }

    public function test_slider_views_timeline_widget_has_chart_options(): void
    {
        $widget = new SliderViewsTimeline();
        $options = $widget->getOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('responsive', $options);
        $this->assertArrayHasKey('scales', $options);
        $this->assertArrayHasKey('plugins', $options);
        $this->assertTrue($options['responsive']);
    }

    public function test_slider_views_timeline_widget_datasets_have_correct_structure(): void
    {
        $widget = new SliderViewsTimeline();
        $data = $widget->getData();

        $datasets = $data['datasets'];

        // Check Views dataset
        $viewsDataset = $datasets[0];
        $this->assertEquals('Views', $viewsDataset['label']);
        $this->assertArrayHasKey('data', $viewsDataset);
        $this->assertArrayHasKey('backgroundColor', $viewsDataset);
        $this->assertArrayHasKey('borderColor', $viewsDataset);
        $this->assertArrayHasKey('borderWidth', $viewsDataset);
        $this->assertArrayHasKey('fill', $viewsDataset);
        $this->assertArrayHasKey('tension', $viewsDataset);

        // Check Clicks dataset
        $clicksDataset = $datasets[1];
        $this->assertEquals('Clicks', $clicksDataset['label']);
        $this->assertArrayHasKey('data', $clicksDataset);
        $this->assertArrayHasKey('backgroundColor', $clicksDataset);
        $this->assertArrayHasKey('borderColor', $clicksDataset);
        $this->assertArrayHasKey('borderWidth', $clicksDataset);
        $this->assertArrayHasKey('fill', $clicksDataset);
        $this->assertArrayHasKey('tension', $clicksDataset);
    }

    public function test_slider_views_timeline_widget_handles_page_filters(): void
    {
        $widget = new SliderViewsTimeline();

        // Test with start date filter
        $widget->pageFilters = ['startDate' => now()->subDays(7)];
        $data = $widget->getData();
        $this->assertIsArray($data);

        // Test with end date filter
        $widget->pageFilters = ['endDate' => now()];
        $data = $widget->getData();
        $this->assertIsArray($data);

        // Test with slider ID filter
        $slider = Slider::first();
        $widget->pageFilters = ['sliderId' => $slider->id];
        $data = $widget->getData();
        $this->assertIsArray($data);

        // Test with status filter
        $widget->pageFilters = ['status' => 'active'];
        $data = $widget->getData();
        $this->assertIsArray($data);
    }

    public function test_slider_views_timeline_widget_data_consistency(): void
    {
        $widget = new SliderViewsTimeline();
        $data = $widget->getData();

        // Check that all datasets have the same number of data points
        $viewsData = $data['datasets'][0]['data'];
        $clicksData = $data['datasets'][1]['data'];

        $this->assertCount(30, $viewsData);
        $this->assertCount(30, $clicksData);
        $this->assertCount(30, $data['labels']);

        // Check that clicks are generally lower than views
        $totalViews = array_sum($viewsData);
        $totalClicks = array_sum($clicksData);
        $this->assertGreaterThanOrEqual($totalClicks, $totalViews);
    }

    public function test_slider_views_timeline_widget_chart_colors(): void
    {
        $widget = new SliderViewsTimeline();
        $data = $widget->getData();

        $viewsDataset = $data['datasets'][0];
        $clicksDataset = $data['datasets'][1];

        // Check Views colors
        $this->assertStringContainsString('rgba(59, 130, 246', $viewsDataset['backgroundColor']);
        $this->assertStringContainsString('rgb(59, 130, 246)', $viewsDataset['borderColor']);

        // Check Clicks colors
        $this->assertStringContainsString('rgba(16, 185, 129', $clicksDataset['backgroundColor']);
        $this->assertStringContainsString('rgb(16, 185, 129)', $clicksDataset['borderColor']);
    }

    public function test_slider_views_timeline_widget_extends_chart_widget(): void
    {
        $this->assertInstanceOf(\Filament\Widgets\ChartWidget::class, new SliderViewsTimeline());
    }

    public function test_slider_views_timeline_widget_uses_page_filters_trait(): void
    {
        $widget = new SliderViewsTimeline();
        $this->assertTrue(method_exists($widget, 'getPageFilters') || in_array(\Filament\Widgets\Concerns\InteractsWithPageFilters::class, class_uses($widget)));
    }
}
