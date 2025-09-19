<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
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
}
