<?php declare(strict_types=1);

namespace Tests\Feature\Frontend;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_orders_index(): void
    {
        $user = User::factory()->create();
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('frontend.orders.index'));

        $response->assertOk();
        $response->assertViewIs('frontend.orders.index');
        $response->assertViewHas('orders');
    }

    public function test_unauthenticated_user_cannot_view_orders_index(): void
    {
        $response = $this->get(route('frontend.orders.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $order->items()->create(OrderItem::factory()->make()->toArray());

        $response = $this->actingAs($user)->get(route('frontend.orders.show', $order));

        $response->assertOk();
        $response->assertViewIs('frontend.orders.show');
        $response->assertViewHas('order', $order);
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('frontend.orders.show', $order));

        $response->assertForbidden();
    }

    public function test_authenticated_user_can_view_create_order_form(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('frontend.orders.create'));

        $response->assertOk();
        $response->assertViewIs('frontend.orders.create');
        $response->assertViewHas('products');
    }

    public function test_user_can_create_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100.00]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
            'billing_address' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+37060000000',
            ],
            'shipping_address' => [
                'name' => 'John Doe',
                'address' => '123 Main St',
                'city' => 'Vilnius',
                'postal_code' => '01001',
            ],
            'notes' => 'Test order',
            'payment_method' => 'credit_card',
        ];

        $response = $this->actingAs($user)->post(route('frontend.orders.store'), $orderData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => 200.00, // 100.00 * 2
            'currency' => 'EUR',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertCount(1, $order->items);
        $this->assertEquals($product->id, $order->items->first()->product_id);
        $this->assertEquals(2, $order->items->first()->quantity);
    }

    public function test_user_can_create_order_with_variant(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100.00]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 150.00,
        ]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                ],
            ],
            'billing_address' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            'shipping_address' => [
                'name' => 'John Doe',
                'address' => '123 Main St',
            ],
        ];

        $response = $this->actingAs($user)->post(route('frontend.orders.store'), $orderData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(150.00, $order->total);
        $this->assertEquals($variant->id, $order->items->first()->product_variant_id);
    }

    public function test_order_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('frontend.orders.store'), []);

        $response->assertSessionHasErrors(['items', 'billing_address', 'shipping_address']);
    }

    public function test_order_creation_validates_product_exists(): void
    {
        $user = User::factory()->create();

        $orderData = [
            'items' => [
                [
                    'product_id' => 999,
                    'quantity' => 1,
                ],
            ],
            'billing_address' => ['name' => 'John Doe'],
            'shipping_address' => ['name' => 'John Doe'],
        ];

        $response = $this->actingAs($user)->post(route('frontend.orders.store'), $orderData);

        $response->assertSessionHasErrors(['items.0.product_id']);
    }

    public function test_user_can_edit_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->get(route('frontend.orders.edit', $order));

        $response->assertOk();
        $response->assertViewIs('frontend.orders.edit');
        $response->assertViewHas('order', $order);
    }

    public function test_user_cannot_edit_other_users_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('frontend.orders.edit', $order));

        $response->assertForbidden();
    }

    public function test_user_cannot_edit_non_cancellable_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'shipped']);

        $response = $this->actingAs($user)->get(route('frontend.orders.edit', $order));

        $response->assertForbidden();
    }

    public function test_user_can_update_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        $product = Product::factory()->create(['price' => 200.00]);

        $updateData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
            'billing_address' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ],
            'shipping_address' => [
                'name' => 'Jane Doe',
                'address' => '456 Oak St',
            ],
            'notes' => 'Updated order',
        ];

        $response = $this->actingAs($user)->put(route('frontend.orders.update', $order), $updateData);

        $response->assertRedirect(route('frontend.orders.show', $order));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals(200.00, $order->total);
        $this->assertEquals('Updated order', $order->notes);
        $this->assertCount(1, $order->items);
        $this->assertEquals($product->id, $order->items->first()->product_id);
    }

    public function test_user_can_delete_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->delete(route('frontend.orders.destroy', $order));

        $response->assertRedirect(route('frontend.orders.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('orders', ['id' => $order->id]);
    }

    public function test_user_cannot_delete_other_users_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->delete(route('frontend.orders.destroy', $order));

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_non_cancellable_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'shipped']);

        $response = $this->actingAs($user)->delete(route('frontend.orders.destroy', $order));

        $response->assertForbidden();
    }

    public function test_user_can_cancel_own_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->patch(route('frontend.orders.cancel', $order));

        $response->assertRedirect(route('frontend.orders.show', $order));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    public function test_user_cannot_cancel_other_users_order(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->patch(route('frontend.orders.cancel', $order));

        $response->assertForbidden();
    }

    public function test_user_cannot_cancel_non_cancellable_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'shipped']);

        $response = $this->actingAs($user)->patch(route('frontend.orders.cancel', $order));

        $response->assertForbidden();
    }

    public function test_orders_index_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        Order::factory()->create(['user_id' => $user->id, 'status' => 'processing']);

        $response = $this->actingAs($user)->get(route('frontend.orders.index', ['status' => 'pending']));

        $response->assertOk();
        $orders = $response->viewData('orders');
        $this->assertCount(1, $orders);
        $this->assertEquals('pending', $orders->first()->status);
    }

    public function test_orders_index_can_be_searched(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id, 'number' => 'ORD-123456']);
        Order::factory()->create(['user_id' => $user->id, 'number' => 'ORD-789012']);

        $response = $this->actingAs($user)->get(route('frontend.orders.index', ['search' => '123456']));

        $response->assertOk();
        $orders = $response->viewData('orders');
        $this->assertCount(1, $orders);
        $this->assertEquals('ORD-123456', $orders->first()->number);
    }

    public function test_order_creation_rolls_back_on_error(): void
    {
        $user = User::factory()->create();
        
        // Mock a database error by using invalid data that will cause an exception
        $orderData = [
            'items' => [
                [
                    'product_id' => 999, // Non-existent product
                    'quantity' => 1,
                ],
            ],
            'billing_address' => ['name' => 'John Doe'],
            'shipping_address' => ['name' => 'John Doe'],
        ];

        $response = $this->actingAs($user)->post(route('frontend.orders.store'), $orderData);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        
        // Verify no order was created
        $this->assertDatabaseMissing('orders', ['user_id' => $user->id]);
    }
}

