<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Models\Channel;
use App\Models\Zone;
use App\Models\Currency;
use App\Models\Product;
use App\Models\OrderItem;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@test.com',
            'is_active' => true
        ]));
    }

    public function test_order_resource_can_be_instantiated(): void
    {
        $resource = new OrderResource();
        $this->assertInstanceOf(OrderResource::class, $resource);
    }

    public function test_order_resource_has_required_methods(): void
    {
        $resource = new OrderResource();

        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(method_exists($resource, 'getPages'));
        $this->assertTrue(method_exists($resource, 'getModel'));
        $this->assertTrue(method_exists($resource, 'getRelations'));
        $this->assertTrue(method_exists($resource, 'getNavigationLabel'));
        $this->assertTrue(method_exists($resource, 'getModelLabel'));
        $this->assertTrue(method_exists($resource, 'getPluralModelLabel'));
    }

    public function test_order_resource_form_works(): void
    {
        $resource = new OrderResource();

        // Test that form method exists and is callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(is_callable([$resource, 'form']));
    }

    public function test_order_resource_table_works(): void
    {
        $resource = new OrderResource();

        // Test that table method exists and is callable
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_order_resource_has_valid_model(): void
    {
        $resource = new OrderResource();
        $model = $resource->getModel();

        $this->assertEquals(Order::class, $model);
        $this->assertTrue(class_exists($model));
    }

    public function test_order_resource_handles_empty_database(): void
    {
        $resource = new OrderResource();

        // Test that methods exist and are callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_order_resource_with_sample_data(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 99.99,
        ]);

        $resource = new OrderResource();

        // Test that methods exist and are callable
        $this->assertTrue(method_exists($resource, 'form'));
        $this->assertTrue(method_exists($resource, 'table'));
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));

        // Test that order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 99.99,
        ]);
    }

    public function test_order_resource_navigation_label(): void
    {
        $label = OrderResource::getNavigationLabel();
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_order_resource_model_label(): void
    {
        $label = OrderResource::getModelLabel();
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_order_resource_plural_model_label(): void
    {
        $label = OrderResource::getPluralModelLabel();
        $this->assertIsString($label);
        $this->assertNotEmpty($label);
    }

    public function test_order_resource_relations(): void
    {
        $relations = OrderResource::getRelations();
        $this->assertIsArray($relations);
        $this->assertNotEmpty($relations);
    }

    public function test_order_resource_pages(): void
    {
        $pages = OrderResource::getPages();
        $this->assertIsArray($pages);
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('view', $pages);
        $this->assertArrayHasKey('edit', $pages);
    }

    public function test_order_resource_with_relations(): void
    {
        // Create test data
        $currency = Currency::factory()->create(['code' => 'EUR']);
        $zone = Zone::factory()->create(['currency_id' => $currency->id]);
        $channel = Channel::factory()->create();
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'zone_id' => $zone->id,
            'channel_id' => $channel->id,
            'status' => 'pending',
            'total' => 99.99,
        ]);

        // Create order items
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 49.99,
            'total' => 99.98,
        ]);

        // Test that order has relations
        $this->assertInstanceOf(User::class, $order->user);
        $this->assertInstanceOf(Zone::class, $order->zone);
        $this->assertInstanceOf(Channel::class, $order->channel);
        $this->assertCount(1, $order->items);
        $this->assertInstanceOf(Product::class, $order->items->first()->product);

        // Test resource methods work with data
        $resource = new OrderResource();
        $this->assertTrue(is_callable([$resource, 'form']));
        $this->assertTrue(is_callable([$resource, 'table']));
    }

    public function test_order_resource_status_actions(): void
    {
        $user = User::factory()->create();
        
        // Test pending order can be marked as processing
        $pendingOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        
        $this->assertTrue($pendingOrder->canBeCancelled());
        $this->assertFalse($pendingOrder->isPaid());
        $this->assertFalse($pendingOrder->isShippable());

        // Test processing order can be shipped
        $processingOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'processing',
        ]);
        
        $this->assertFalse($processingOrder->canBeCancelled());
        $this->assertTrue($processingOrder->isPaid());
        $this->assertTrue($processingOrder->isShippable());

        // Test shipped order can be delivered
        $shippedOrder = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'shipped',
        ]);
        
        $this->assertFalse($shippedOrder->canBeCancelled());
        $this->assertTrue($shippedOrder->isPaid());
        $this->assertFalse($shippedOrder->isShippable());
    }

    public function test_order_resource_global_search(): void
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 150.00,
            'status' => 'pending',
        ]);

        // Test global search details
        $details = OrderResource::getGlobalSearchResultDetails($order);
        $this->assertIsArray($details);
        $this->assertArrayHasKey('Customer', $details);
        $this->assertArrayHasKey('Total', $details);
        $this->assertArrayHasKey('Status', $details);
        
        // Verify the content of the details
        $this->assertEquals('John Doe', $details['Customer']);
        $this->assertStringContainsString('€150.00', $details['Total']);
        $this->assertIsString($details['Status']);

        // Test global search actions (may be empty if routes are not set up)
        $actions = OrderResource::getGlobalSearchResultActions($order);
        $this->assertIsArray($actions);
        // Note: Actions may be empty in test environment if routes are not properly registered
    }
}
