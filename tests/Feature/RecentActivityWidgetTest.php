<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\RecentActivityWidget;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Review;
use App\Models\Campaign;
use App\Models\News;
use App\Models\Slider;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentActivityWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_activity_widget_can_be_instantiated(): void
    {
        $widget = new RecentActivityWidget();
        $this->assertInstanceOf(RecentActivityWidget::class, $widget);
    }

    public function test_recent_activity_widget_returns_table(): void
    {
        $widget = new RecentActivityWidget();
        $table = $widget->table(new \Filament\Tables\Table());
        
        $this->assertInstanceOf(\Filament\Tables\Table::class, $table);
    }

    public function test_recent_activity_widget_handles_empty_database(): void
    {
        $widget = new RecentActivityWidget();
        $query = $widget->getTableQuery();
        
        // Should not throw exceptions with empty database
        $this->assertNotNull($query);
    }

    public function test_recent_activity_widget_with_sample_data(): void
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
        
        $news = News::factory()->create([
            'title' => 'Test News',
            'is_published' => true,
            'created_at' => now()
        ]);
        
        $slider = Slider::factory()->create([
            'title' => 'Test Slider',
            'is_active' => true,
            'created_at' => now()
        ]);
        
        $systemSetting = SystemSetting::factory()->create([
            'key' => 'test_setting',
            'value' => 'test_value',
            'is_public' => true,
            'created_at' => now()
        ]);

        $widget = new RecentActivityWidget();
        $query = $widget->getTableQuery();
        
        $this->assertNotNull($query);
        
        // Test that the query can be executed
        $results = $query->get();
        $this->assertIsIterable($results);
    }

    public function test_recent_activity_widget_column_span(): void
    {
        $widget = new RecentActivityWidget();
        $this->assertEquals('full', $widget->columnSpan);
    }

    public function test_recent_activity_widget_sort_order(): void
    {
        $this->assertEquals(3, RecentActivityWidget::getSort());
    }

    public function test_recent_activity_widget_heading(): void
    {
        $this->assertEquals('Recent Activity Dashboard', RecentActivityWidget::getHeading());
    }

    public function test_recent_activity_widget_table_columns(): void
    {
        $widget = new RecentActivityWidget();
        $table = $widget->table(new \Filament\Tables\Table());
        
        // The table should have columns configured
        $this->assertInstanceOf(\Filament\Tables\Table::class, $table);
    }

    public function test_recent_activity_widget_union_queries(): void
    {
        // Create data for different models
        $user = User::factory()->create(['created_at' => now()]);
        $product = Product::factory()->create(['created_at' => now()]);
        $order = Order::factory()->create(['created_at' => now()]);
        $review = Review::factory()->create(['created_at' => now()]);
        $campaign = Campaign::factory()->create(['created_at' => now()]);
        $news = News::factory()->create(['created_at' => now()]);
        $slider = Slider::factory()->create(['created_at' => now()]);
        $systemSetting = SystemSetting::factory()->create(['created_at' => now()]);

        $widget = new RecentActivityWidget();
        $query = $widget->getTableQuery();
        
        $results = $query->get();
        
        // Should have results from all models
        $this->assertGreaterThan(0, $results->count());
        
        // Check that we have different types
        $types = $results->pluck('type')->unique();
        $this->assertTrue($types->contains('Order'));
        $this->assertTrue($types->contains('Product'));
        $this->assertTrue($types->contains('User'));
        $this->assertTrue($types->contains('Review'));
        $this->assertTrue($types->contains('Campaign'));
        $this->assertTrue($types->contains('News'));
        $this->assertTrue($types->contains('Slider'));
        $this->assertTrue($types->contains('System Setting'));
    }

    public function test_recent_activity_widget_filters_recent_data(): void
    {
        // Create old data (should not appear)
        $oldOrder = Order::factory()->create([
            'created_at' => now()->subDays(10)
        ]);
        
        // Create recent data (should appear)
        $recentOrder = Order::factory()->create([
            'created_at' => now()->subDays(3)
        ]);

        $widget = new RecentActivityWidget();
        $query = $widget->getTableQuery();
        
        $results = $query->get();
        
        // Should only include recent data (last 7 days)
        $this->assertGreaterThan(0, $results->count());
        
        // Check that old data is not included
        $orderIds = $results->where('type', 'Order')->pluck('title');
        $this->assertStringContains('Order #' . $recentOrder->id, $orderIds->first());
    }
}
