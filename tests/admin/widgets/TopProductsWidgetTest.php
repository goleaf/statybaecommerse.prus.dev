<?php declare(strict_types=1);

use App\Filament\Widgets\TopProductsWidget;
use App\Models\AnalyticsEvent;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->widget = new TopProductsWidget();
});

it('can instantiate top products widget', function () {
    expect($this->widget)->toBeInstanceOf(TopProductsWidget::class);
});

it('has correct heading', function () {
    expect($this->widget->getHeading())->toBe(__('admin.widgets.top_products'));
});

it('can configure table', function () {
    $table = $this->widget->table(new Table($this->widget));

    expect($table)->toBeInstanceOf(Table::class);
});

it('returns only visible products', function () {
    // Create test data
    $visibleProduct = Product::factory()->create(['is_visible' => true]);
    $hiddenProduct = Product::factory()->create(['is_visible' => false]);

    // Create analytics events for both products
    AnalyticsEvent::factory()->create([
        'event_type' => 'product_view',
        'properties' => ['product_id' => $visibleProduct->id],
        'created_at' => now()->subDays(3)
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'product_view',
        'properties' => ['product_id' => $hiddenProduct->id],
        'created_at' => now()->subDays(3)
    ]);

    $table = $this->widget->table(new Table($this->widget));
    $query = $table->getQuery();
    $results = $query->get();

    // Should only include visible products
    expect($results->pluck('id')->toArray())
        ->toContain($visibleProduct->id)
        ->and($results->pluck('id')->toArray())
        ->not()
        ->toContain($hiddenProduct->id);
});

it('counts product views correctly', function () {
    $product = Product::factory()->create(['is_visible' => true]);

    // Create analytics events for product views
    AnalyticsEvent::factory()->count(3)->create([
        'event_type' => 'product_view',
        'properties' => ['product_id' => $product->id],
        'created_at' => now()->subDays(3)
    ]);

    // Create old analytics event (should not count)
    AnalyticsEvent::factory()->create([
        'event_type' => 'product_view',
        'properties' => ['product_id' => $product->id],
        'created_at' => now()->subDays(10)
    ]);

    $table = $this->widget->table(new Table($this->widget));
    $query = $table->getQuery();
    $result = $query->where('products.id', $product->id)->first();

    expect($result->views_count)->toBe(3);
});

it('counts cart adds correctly', function () {
    $product = Product::factory()->create(['is_visible' => true]);

    // Create analytics events for cart adds
    AnalyticsEvent::factory()->count(2)->create([
        'event_type' => 'add_to_cart',
        'properties' => ['product_id' => $product->id],
        'created_at' => now()->subDays(2)
    ]);

    // Create old analytics event (should not count)
    AnalyticsEvent::factory()->create([
        'event_type' => 'add_to_cart',
        'properties' => ['product_id' => $product->id],
        'created_at' => now()->subDays(10)
    ]);

    $table = $this->widget->table(new Table($this->widget));
    $query = $table->getQuery();
    $result = $query->where('products.id', $product->id)->first();

    expect($result->cart_adds_count)->toBe(2);
});

it('calculates total sold correctly', function () {
    $product = Product::factory()->create(['is_visible' => true]);

    // Create completed order
    $completedOrder = Order::factory()->create(['status' => 'completed']);
    OrderItem::factory()->create([
        'order_id' => $completedOrder->id,
        'product_id' => $product->id,
        'quantity' => 5
    ]);

    // Create pending order (should not count)
    $pendingOrder = Order::factory()->create(['status' => 'pending']);
    OrderItem::factory()->create([
        'order_id' => $pendingOrder->id,
        'product_id' => $product->id,
        'quantity' => 3
    ]);

    $table = $this->widget->table(new Table($this->widget));
    $query = $table->getQuery();
    $result = $query->where('products.id', $product->id)->first();

    // Should only count completed orders
    expect($result->total_sold)->toBe(5);
});

it('orders products by combined score', function () {
    $product1 = Product::factory()->create(['is_visible' => true, 'name' => 'Product 1']);
    $product2 = Product::factory()->create(['is_visible' => true, 'name' => 'Product 2']);

    // Product 1: 2 views + 1 sale = 3 total score
    AnalyticsEvent::factory()->count(2)->create([
        'event_type' => 'product_view',
        'properties' => ['product_id' => $product1->id],
        'created_at' => now()->subDays(2)
    ]);

    $order1 = Order::factory()->create(['status' => 'completed']);
    OrderItem::factory()->create([
        'order_id' => $order1->id,
        'product_id' => $product1->id,
        'quantity' => 1
    ]);

    // Product 2: 1 view + 3 sales = 4 total score (should be first)
    AnalyticsEvent::factory()->create([
        'event_type' => 'product_view',
        'properties' => ['product_id' => $product2->id],
        'created_at' => now()->subDays(2)
    ]);

    $order2 = Order::factory()->create(['status' => 'completed']);
    OrderItem::factory()->create([
        'order_id' => $order2->id,
        'product_id' => $product2->id,
        'quantity' => 3
    ]);

    $table = $this->widget->table(new Table($this->widget));
    $query = $table->getQuery();
    $results = $query->get();

    // Product 2 should be first (higher combined score)
    expect($results->first()->id)->toBe($product2->id);
});

it('handles products without analytics data', function () {
    $product = Product::factory()->create(['is_visible' => true]);

    $table = $this->widget->table(new Table($this->widget));
    $query = $table->getQuery();
    $result = $query->where('products.id', $product->id)->first();

    expect($result->views_count)
        ->toBe(0)
        ->and($result->cart_adds_count)
        ->toBe(0)
        ->and($result->total_sold)
        ->toBe(0);
});

it('has correct column span', function () {
    $reflection = new ReflectionClass($this->widget);
    $property = $reflection->getProperty('columnSpan');
    $property->setAccessible(true);

    expect($property->getValue($this->widget))->toBe('full');
});

it('has correct sort order', function () {
    $reflection = new ReflectionClass($this->widget);
    $property = $reflection->getProperty('sort');
    $property->setAccessible(true);

    expect($property->getValue($this->widget))->toBe(3);
});

it('includes product view action', function () {
    $table = $this->widget->table(new Table($this->widget));

    // Check that the table has record actions configured
    $actions = $table->getRecordActions();
    expect($actions)->not()->toBeEmpty();

    // Ensure the Edit action exists
    $actionNames = array_map(fn($a) => method_exists($a, 'getName') ? $a->getName() : null, $actions);
    expect($actionNames)->toContain('edit');
});

it('uses correct pagination options', function () {
    $table = $this->widget->table(new Table($this->widget));

    // Check pagination is enabled
    expect($table->isPaginated())->toBeTrue();
});

it('uses media library for product images', function () {
    $table = $this->widget->table(new Table($this->widget));
    $columns = $table->getColumns();

    // Check that SpatieMediaLibraryImageColumn is used
    $imageColumn = collect($columns)->first(fn($column) => $column->getName() === 'images');
    expect($imageColumn)->not()->toBeNull();
});
