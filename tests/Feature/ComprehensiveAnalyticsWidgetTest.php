<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\ComprehensiveAnalyticsWidget;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\WishlistItem;
use App\Models\AnalyticsEvent;
use App\Models\CampaignView;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\UserBehavior;
use App\Models\RecommendationAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveAnalyticsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_comprehensive_analytics_widget_can_be_instantiated(): void
    {
        $widget = new ComprehensiveAnalyticsWidget();
        $this->assertInstanceOf(ComprehensiveAnalyticsWidget::class, $widget);
    }

    public function test_comprehensive_analytics_widget_returns_data_array(): void
    {
        $widget = new ComprehensiveAnalyticsWidget();
        $data = $widget->getData();
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
    }

    public function test_comprehensive_analytics_widget_handles_empty_database(): void
    {
        $widget = new ComprehensiveAnalyticsWidget();
        $data = $widget->getData();
        
        // Should not throw exceptions with empty database
        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
    }

    public function test_comprehensive_analytics_widget_with_sample_data(): void
    {
        // Create sample data
        $user = User::factory()->create();
        $product = Product::factory()->create();
        // $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100.00,
            'status' => 'completed',
            'created_at' => now()
        ]);
        
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 5,
            'created_at' => now()
        ]);
        
        // $wishlistItem = WishlistItem::factory()->create([
        //     'user_id' => $user->id,
        //     'product_id' => $product->id,
        //     'created_at' => now()
        // ]);
        
        $analyticsEvent = AnalyticsEvent::factory()->create([
            'event_type' => 'page_view',
            'created_at' => now()
        ]);
        
        // $campaignView = CampaignView::factory()->create([
        //     'views_count' => 10,
        //     'created_at' => now()
        // ]);
        
        // $campaignClick = CampaignClick::factory()->create([
        //     'clicks_count' => 5,
        //     'created_at' => now()
        // ]);
        
        // $campaignConversion = CampaignConversion::factory()->create([
        //     'conversions_count' => 2,
        //     'created_at' => now()
        // ]);
        
        // $userBehavior = UserBehavior::factory()->create([
        //     'user_id' => $user->id,
        //     'created_at' => now()
        // ]);
        
        // $recommendationAnalytics = RecommendationAnalytics::factory()->create([
        //     'recommendations_count' => 3,
        //     'created_at' => now()
        // ]);

        $widget = new ComprehensiveAnalyticsWidget();
        $data = $widget->getData();
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertArrayHasKey('labels', $data);
        
        // Check that we have the expected number of datasets
        $this->assertCount(15, $data['datasets']);
        
        // Check that we have 30 days of labels
        $this->assertCount(30, $data['labels']);
    }

    public function test_comprehensive_analytics_widget_chart_type(): void
    {
        $widget = new ComprehensiveAnalyticsWidget();
        $this->assertEquals('line', $widget->getType());
    }

    public function test_comprehensive_analytics_widget_options(): void
    {
        $widget = new ComprehensiveAnalyticsWidget();
        $options = $widget->getOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('responsive', $options);
        $this->assertArrayHasKey('plugins', $options);
        $this->assertArrayHasKey('scales', $options);
    }

    public function test_comprehensive_analytics_widget_column_span(): void
    {
        $widget = new ComprehensiveAnalyticsWidget();
        $this->assertEquals('full', $widget->getColumnSpan());
    }

    public function test_comprehensive_analytics_widget_sort_order(): void
    {
        $this->assertEquals(1, ComprehensiveAnalyticsWidget::getSort());
    }

    public function test_comprehensive_analytics_widget_heading(): void
    {
        $widget = new ComprehensiveAnalyticsWidget();
        $this->assertEquals('Comprehensive Analytics Dashboard', $widget->getHeading());
    }

    public function test_comprehensive_analytics_widget_max_height(): void
    {
        $widget = new ComprehensiveAnalyticsWidget();
        $this->assertEquals('500px', $widget->getMaxHeight());
    }

    public function test_comprehensive_analytics_widget_data_structure(): void
    {
        $widget = new ComprehensiveAnalyticsWidget();
        $data = $widget->getData();
        
        // Check datasets structure
        $this->assertIsArray($data['datasets']);
        foreach ($data['datasets'] as $dataset) {
            $this->assertArrayHasKey('label', $dataset);
            $this->assertArrayHasKey('data', $dataset);
            $this->assertArrayHasKey('borderColor', $dataset);
            $this->assertArrayHasKey('backgroundColor', $dataset);
            $this->assertArrayHasKey('fill', $dataset);
            $this->assertArrayHasKey('tension', $dataset);
            $this->assertArrayHasKey('yAxisID', $dataset);
        }
        
        // Check labels structure
        $this->assertIsArray($data['labels']);
        $this->assertCount(30, $data['labels']); // 30 days
    }
}
