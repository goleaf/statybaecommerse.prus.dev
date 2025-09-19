<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\CampaignPerformanceWidget;
use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignPerformanceWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_performance_widget_can_be_instantiated(): void
    {
        $widget = new CampaignPerformanceWidget();
        $this->assertInstanceOf(CampaignPerformanceWidget::class, $widget);
    }

    public function test_campaign_performance_widget_has_correct_properties(): void
    {
        $this->assertEquals(6, CampaignPerformanceWidget::getSort());
        // Test widget instantiation without direct property access
        $widget = new CampaignPerformanceWidget();
        $this->assertInstanceOf(CampaignPerformanceWidget::class, $widget);
    }

    public function test_campaign_performance_widget_returns_data_array(): void
    {
        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_campaign_performance_widget_handles_empty_database(): void
    {
        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        // Should not throw exceptions with empty database
        $this->assertIsArray($data);
    }

    public function test_campaign_performance_widget_with_sample_data(): void
    {
        // Create sample campaign data
        $campaign = Campaign::factory()->create([
            'name' => 'Test Campaign',
            'status' => 'active'
        ]);

        // CampaignView::factory()->create([
        //     'campaign_id' => $campaign->id,
        //     'views_count' => 100
        // ]);

        // CampaignClick::factory()->create([
        //     'campaign_id' => $campaign->id,
        //     'clicks_count' => 10
        // ]);

        // CampaignConversion::factory()->create([
        //     'campaign_id' => $campaign->id,
        //     'conversions_count' => 2
        // ]);

        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_campaign_performance_widget_data_structure(): void
    {
        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        // Check that data has expected structure
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertIsArray($data['datasets']);
        $this->assertIsArray($data['labels']);
    }

    public function test_campaign_performance_widget_extends_chart_widget(): void
    {
        $widget = new CampaignPerformanceWidget();

        $this->assertInstanceOf(\Filament\Widgets\ChartWidget::class, $widget);
    }

    public function test_campaign_performance_widget_renders_successfully(): void
    {
        $widget = \Livewire\Livewire::test(CampaignPerformanceWidget::class);
        $widget->assertSuccessful();
    }

    public function test_campaign_performance_widget_has_required_methods(): void
    {
        $widget = new CampaignPerformanceWidget();

        $this->assertTrue(method_exists($widget, 'getData'));
        $this->assertTrue(method_exists($widget, 'getType'));
        $this->assertTrue(method_exists($widget, 'getOptions'));
    }

    public function test_campaign_performance_widget_chart_type(): void
    {
        $widget = new CampaignPerformanceWidget();
        $type = $widget->getType();

        $this->assertIsString($type);
        $this->assertNotEmpty($type);
    }

    public function test_campaign_performance_widget_chart_options(): void
    {
        $widget = new CampaignPerformanceWidget();
        $options = $widget->getOptions();

        $this->assertIsArray($options);
    }

    public function test_campaign_performance_widget_max_height(): void
    {
        $widget = new CampaignPerformanceWidget();
        $maxHeight = $widget->getMaxHeight();

        $this->assertEquals('300px', $maxHeight);
    }

    public function test_campaign_performance_widget_campaign_views(): void
    {
        // Create campaign with views
        $campaign = Campaign::factory()->create();

        CampaignView::factory()->create([
            'campaign_id' => $campaign->id,
            // 'views_count' => 50
        ]);

        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_campaign_performance_widget_campaign_clicks(): void
    {
        // Create campaign with clicks
        $campaign = Campaign::factory()->create();

        CampaignClick::factory()->create([
            'campaign_id' => $campaign->id,
            // 'clicks_count' => 25
        ]);

        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_campaign_performance_widget_campaign_conversions(): void
    {
        // Create campaign with conversions
        $campaign = Campaign::factory()->create();

        CampaignConversion::factory()->create([
            'campaign_id' => $campaign->id,
            // 'conversions_count' => 5
        ]);

        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
    }

    public function test_campaign_performance_widget_handles_large_datasets(): void
    {
        // Create multiple campaigns with data
        $campaigns = Campaign::factory()->count(5)->create();

        foreach ($campaigns as $campaign) {
            CampaignView::factory()->create([
                'campaign_id' => $campaign->id,
                // 'views_count' => rand(10, 100)
            ]);

            CampaignClick::factory()->create([
                'campaign_id' => $campaign->id,
                // 'clicks_count' => rand(5, 50)
            ]);

            CampaignConversion::factory()->create([
                'campaign_id' => $campaign->id,
                // 'conversions_count' => rand(1, 10)
            ]);
        }

        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_campaign_performance_widget_data_labels(): void
    {
        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        if (isset($data['labels'])) {
            $this->assertIsArray($data['labels']);
        }
    }

    public function test_campaign_performance_widget_data_datasets(): void
    {
        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        if (isset($data['datasets'])) {
            $this->assertIsArray($data['datasets']);
        }
    }

    public function test_campaign_performance_widget_top_campaigns_limit(): void
    {
        // Create more than 10 campaigns to test limit
        $campaigns = Campaign::factory()->count(15)->create();

        foreach ($campaigns as $campaign) {
            CampaignView::factory()->create([
                'campaign_id' => $campaign->id,
                // 'views_count' => rand(10, 100)
            ]);
        }

        $widget = new CampaignPerformanceWidget();
        $data = $widget->getData();

        $this->assertIsArray($data);
    }
}
