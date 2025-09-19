<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SliderAnalyticsTest extends TestCase
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
        // Create active sliders with various features
        Slider::factory()->create([
            'title' => 'Active Slider with Image',
            'is_active' => true,
            'button_text' => 'Learn More',
            'button_url' => 'https://example.com',
            'description' => 'This is a test slider',
            'background_color' => '#ff0000',
            'text_color' => '#ffffff',
        ]);

        Slider::factory()->create([
            'title' => 'Active Slider without Image',
            'is_active' => true,
            'button_text' => null,
            'button_url' => null,
            'description' => null,
        ]);

        Slider::factory()->create([
            'title' => 'Inactive Slider',
            'is_active' => false,
            'button_text' => 'Click Here',
            'button_url' => '/internal-link',
        ]);

        Slider::factory()->create([
            'title' => 'Slider with Background',
            'is_active' => true,
            'background_color' => '#00ff00',
            'text_color' => '#000000',
        ]);
    }

    public function test_can_access_slider_analytics_page(): void
    {
        $response = $this->get('/admin/slider-analytics');
        $response->assertStatus(200);
    }

    public function test_slider_analytics_page_has_correct_title(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Slider Analytics Dashboard');
    }

    public function test_slider_analytics_has_filter_actions(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Filter Analytics')
            ->assertSee('Export Analytics')
            ->assertSee('Refresh Data');
    }

    public function test_slider_analytics_shows_overview_stats(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Total Sliders')
            ->assertSee('Active Sliders')
            ->assertSee('Inactive Sliders');
    }

    public function test_slider_analytics_shows_performance_chart(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Slider Performance Over Time');
    }

    public function test_slider_analytics_shows_engagement_metrics(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Slider Engagement Metrics');
    }

    public function test_slider_analytics_shows_top_performing_sliders(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Top Performing Sliders');
    }

    public function test_slider_analytics_shows_click_through_rates(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Click-Through Rate Analysis');
    }

    public function test_slider_analytics_shows_views_timeline(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Slider Views Timeline');
    }

    public function test_slider_analytics_shows_comparison_table(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Slider Performance Comparison');
    }

    public function test_slider_analytics_shows_recommendations(): void
    {
        $this->get('/admin/slider-analytics')
            ->assertSee('Slider Optimization Recommendations');
    }

    public function test_slider_analytics_navigation_works(): void
    {
        $this->get('/admin')
            ->assertSee('Slider Analytics');
    }

    public function test_slider_analytics_requires_authentication(): void
    {
        auth()->logout();
        
        $this->get('/admin/slider-analytics')
            ->assertRedirect('/admin/login');
    }

    public function test_slider_analytics_has_correct_navigation_icon(): void
    {
        $this->assertTrue(method_exists(\App\Filament\Pages\SliderAnalytics::class, 'getNavigationIcon'));
    }

    public function test_slider_analytics_has_correct_navigation_sort(): void
    {
        $this->assertTrue(method_exists(\App\Filament\Pages\SliderAnalytics::class, 'getNavigationSort'));
    }

    public function test_slider_analytics_has_correct_slug(): void
    {
        $this->assertTrue(method_exists(\App\Filament\Pages\SliderAnalytics::class, 'getSlug'));
    }

    public function test_slider_analytics_widgets_exist(): void
    {
        $widgets = [
            \App\Filament\Pages\SliderAnalytics\Widgets\SliderOverviewStats::class,
            \App\Filament\Pages\SliderAnalytics\Widgets\SliderPerformanceChart::class,
            \App\Filament\Pages\SliderAnalytics\Widgets\SliderEngagementMetrics::class,
            \App\Filament\Pages\SliderAnalytics\Widgets\TopPerformingSliders::class,
            \App\Filament\Pages\SliderAnalytics\Widgets\SliderClickThroughRates::class,
            \App\Filament\Pages\SliderAnalytics\Widgets\SliderViewsTimeline::class,
            \App\Filament\Pages\SliderAnalytics\Widgets\SliderComparisonTable::class,
            \App\Filament\Pages\SliderAnalytics\Widgets\SliderRecommendations::class,
        ];

        foreach ($widgets as $widget) {
            $this->assertTrue(class_exists($widget), "Widget {$widget} does not exist");
        }
    }

    public function test_slider_analytics_page_has_correct_properties(): void
    {
        $page = new \App\Filament\Pages\SliderAnalytics();
        
        $this->assertEquals('Slider Analytics', $page->getTitle());
        $this->assertEquals('Slider Performance Analytics', $page->getHeading());
        $this->assertEquals('heroicon-o-chart-bar', $page->getNavigationIcon());
        $this->assertEquals(3, $page->getNavigationSort());
        $this->assertEquals('slider-analytics', $page->getSlug());
    }
}