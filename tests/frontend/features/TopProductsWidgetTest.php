<?php declare(strict_types=1);

namespace Tests\Feature\Widgets;

use App\Filament\Widgets\TopProductsWidget;
use App\Models\AnalyticsEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class TopProductsWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user and authenticate
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);
        $this->actingAs($admin);
    }

    public function test_widget_can_be_rendered(): void
    {
        $component = Livewire::test(TopProductsWidget::class);

        $component->assertOk();
    }

    public function test_widget_heading_is_translated(): void
    {
        $component = Livewire::test(TopProductsWidget::class);

        $heading = $component->instance()->getHeading();

        $this->assertNotEmpty($heading);
        $this->assertIsString($heading);
    }

    public function test_widget_shows_published_products_only(): void
    {
        // Create products with different statuses
        $publishedProduct = Product::factory()->create([
            'name' => 'Published Product',
            'status' => 'published',
            'is_visible' => true,
            'price' => 99.99,
            'stock_quantity' => 10,
        ]);

        $draftProduct = Product::factory()->create([
            'name' => 'Draft Product',
            'status' => 'draft',
            'is_visible' => false,
            'price' => 49.99,
            'stock_quantity' => 5,
        ]);

        $component = Livewire::test(TopProductsWidget::class);

        // The widget should only show published products
        $widget = $component->instance();
        $table = $widget->table(new \Filament\Tables\Table($widget));
        $tableData = $table->getQuery()->get();

        $this->assertTrue($tableData->contains('id', $publishedProduct->id));
        $this->assertFalse($tableData->contains('id', $draftProduct->id));
    }

    public function test_widget_displays_analytics_data(): void
    {
        // Create a published product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'status' => 'published',
            'is_visible' => true,
            'price' => 199.99,
            'stock_quantity' => 20,
        ]);

        // Create analytics events for the product
        AnalyticsEvent::factory()->count(5)->create([
            'event_type' => 'product_view',
            'properties' => ['product_id' => $product->id],
            'created_at' => now()->subDays(3),
        ]);

        AnalyticsEvent::factory()->count(2)->create([
            'event_type' => 'add_to_cart',
            'properties' => ['product_id' => $product->id],
            'created_at' => now()->subDays(2),
        ]);

        $component = Livewire::test(TopProductsWidget::class);

        $component->assertOk();

        // Check that the product appears in the table
        $widget = $component->instance();
        $table = $widget->table(new \Filament\Tables\Table($widget));
        $tableData = $table->getQuery()->get();
        $productData = $tableData->where('id', $product->id)->first();

        $this->assertNotNull($productData);
        $this->assertEquals(5, $productData->views_count);
        $this->assertEquals(2, $productData->cart_adds_count);
    }

    public function test_widget_displays_sales_data(): void
    {
        // Create a published product
        $product = Product::factory()->create([
            'name' => 'Sales Product',
            'status' => 'published',
            'is_visible' => true,
            'price' => 299.99,
            'stock_quantity' => 15,
        ]);

        // Create completed orders with this product
        $order1 = Order::factory()->create(['status' => 'completed']);
        $order2 = Order::factory()->create(['status' => 'completed']);
        $pendingOrder = Order::factory()->create(['status' => 'pending']);

        OrderItem::factory()->create([
            'order_id' => $order1->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => $product->price,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order2->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        // This should not be counted (pending order)
        OrderItem::factory()->create([
            'order_id' => $pendingOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => $product->price,
        ]);

        $component = Livewire::test(TopProductsWidget::class);

        $component->assertOk();

        // Check that sales data is calculated correctly (only completed orders)
        $widget = $component->instance();
        $table = $widget->table(new \Filament\Tables\Table($widget));
        $tableData = $table->getQuery()->get();
        $productData = $tableData->where('id', $product->id)->first();

        $this->assertNotNull($productData);
        $this->assertEquals(5, $productData->total_sold);  // 3 + 2 from completed orders only
    }

    public function test_widget_handles_products_without_analytics(): void
    {
        // Create a product without any analytics events
        $product = Product::factory()->create([
            'name' => 'No Analytics Product',
            'status' => 'published',
            'is_visible' => true,
            'price' => 149.99,
            'stock_quantity' => 8,
        ]);

        $component = Livewire::test(TopProductsWidget::class);

        $component->assertOk();

        // Product should still appear in the table with zero analytics
        $widget = $component->instance();
        $table = $widget->table(new \Filament\Tables\Table($widget));
        $tableData = $table->getQuery()->get();
        $productData = $tableData->where('id', $product->id)->first();

        $this->assertNotNull($productData);
        $this->assertEquals(0, $productData->views_count ?? 0);
        $this->assertEquals(0, $productData->cart_adds_count ?? 0);
    }

    public function test_widget_sorts_by_popularity(): void
    {
        // Create multiple products with different popularity levels
        $product1 = Product::factory()->create([
            'name' => 'Low Popularity',
            'status' => 'published',
            'is_visible' => true,
            'price' => 99.99,
        ]);

        $product2 = Product::factory()->create([
            'name' => 'High Popularity',
            'status' => 'published',
            'is_visible' => true,
            'price' => 199.99,
        ]);

        $product3 = Product::factory()->create([
            'name' => 'Medium Popularity',
            'status' => 'published',
            'is_visible' => true,
            'price' => 149.99,
        ]);

        // Create analytics events - product2 should be most popular
        AnalyticsEvent::factory()->count(10)->create([
            'event_type' => 'product_view',
            'properties' => ['product_id' => $product2->id],
            'created_at' => now()->subDays(2),
        ]);

        AnalyticsEvent::factory()->count(5)->create([
            'event_type' => 'product_view',
            'properties' => ['product_id' => $product3->id],
            'created_at' => now()->subDays(2),
        ]);

        AnalyticsEvent::factory()->count(1)->create([
            'event_type' => 'product_view',
            'properties' => ['product_id' => $product1->id],
            'created_at' => now()->subDays(2),
        ]);

        $component = Livewire::test(TopProductsWidget::class);

        $component->assertOk();

        // Check that products are sorted by popularity (views + sales)
        $widget = $component->instance();
        $table = $widget->table(new \Filament\Tables\Table($widget));
        $tableData = $table->getQuery()->get();

        // First product should be the most popular (product2)
        $firstProduct = $tableData->first();
        $this->assertEquals($product2->id, $firstProduct->id);
    }

    public function test_widget_displays_correct_columns(): void
    {
        $product = Product::factory()->create([
            'name' => 'Column Test Product',
            'status' => 'published',
            'is_visible' => true,
            'price' => 99.99,
            'stock_quantity' => 15,
        ]);

        $component = Livewire::test(TopProductsWidget::class);

        $component->assertOk();

        // Test that all expected columns are present
        $widget = $component->instance();
        $table = $widget->table(new \Filament\Tables\Table($widget));
        $columns = $table->getColumns();

        $columnNames = array_keys($columns);

        $this->assertContains('images', $columnNames);
        $this->assertContains('name', $columnNames);
        $this->assertContains('views_count', $columnNames);
        $this->assertContains('cart_adds_count', $columnNames);
        $this->assertContains('total_sold', $columnNames);
        $this->assertContains('price', $columnNames);
        $this->assertContains('stock_quantity', $columnNames);
    }

    public function test_widget_filters_recent_analytics_only(): void
    {
        $product = Product::factory()->create([
            'name' => 'Recent Analytics Product',
            'status' => 'published',
            'is_visible' => true,
            'price' => 199.99,
        ]);

        // Create old analytics events (should not be counted)
        AnalyticsEvent::factory()->count(5)->create([
            'event_type' => 'product_view',
            'properties' => ['product_id' => $product->id],
            'created_at' => now()->subDays(10),  // Older than 7 days
        ]);

        // Create recent analytics events (should be counted)
        AnalyticsEvent::factory()->count(3)->create([
            'event_type' => 'product_view',
            'properties' => ['product_id' => $product->id],
            'created_at' => now()->subDays(3),  // Within last 7 days
        ]);

        $component = Livewire::test(TopProductsWidget::class);

        $component->assertOk();

        // Check that only recent analytics are counted
        $widget = $component->instance();
        $table = $widget->table(new \Filament\Tables\Table($widget));
        $tableData = $table->getQuery()->get();
        $productData = $tableData->where('id', $product->id)->first();

        $this->assertNotNull($productData);
        $this->assertEquals(3, $productData->views_count);  // Only recent views
    }

    public function test_widget_handles_empty_state(): void
    {
        // Don't create any products

        $component = Livewire::test(TopProductsWidget::class);

        $component->assertOk();

        // Widget should handle empty state gracefully
        $widget = $component->instance();
        $table = $widget->table(new \Filament\Tables\Table($widget));
        $tableData = $table->getQuery()->get();

        $this->assertTrue($tableData->isEmpty());
    }
}
