<?php

declare(strict_types=1);

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingOption;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('creates order successfully with valid data', function () {
    // Arrange
    $product = Product::factory()->create(['price' => 50.00]);
    $shippingAddress = Address::factory()->create(['user_id' => $this->user->id]);
    $billingAddress = Address::factory()->create(['user_id' => $this->user->id]);
    $shippingOption = ShippingOption::factory()->create();

    $orderData = [
        'customer_id'         => $this->user->id,
        'shipping_address_id' => $shippingAddress->id,
        'billing_address_id'  => $billingAddress->id,
        'shipping_option_id'  => $shippingOption->id,
        'payment_method_id'   => 1,
        'payment_method'      => 'Credit Card',
        'subtotal'            => 100.00,
        'items'               => [
            [
                'product_id' => $product->id,
                'quantity'   => 2,
                'unit_price' => 50.00,
                'name'       => $product->name,
                'sku'        => $product->sku,
            ],
        ],
    ];

    // Act
    $response = $this->actingAs($this->user)
        ->postJson('/api/orders', $orderData);

    // Assert
    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => __('orders.created_successfully'),
        ]);

    $this->assertDatabaseHas('orders', [
        'customer_id'     => $this->user->id,
        'subtotal_amount' => 100.00,
    ]);
});

test('fails to create order with invalid data', function () {
    // Act
    $response = $this->actingAs($this->user)
        ->postJson('/api/orders', [
            'customer_id' => $this->user->id,
            // Missing required fields
        ]);

    // Assert
    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'shipping_address_id',
            'billing_address_id',
            'items',
        ]);
});

test('gets user orders with filtering', function () {
    // Arrange
    Order::factory()->count(3)->create(['customer_id' => $this->user->id]);
    Order::factory()->count(2)->create(['customer_id' => $this->user->id, 'status' => 'completed']);

    // Act
    $response = $this->actingAs($this->user)
        ->getJson('/api/orders?status=completed');

    // Assert
    $response->assertStatus(200)
        ->assertJson(['success' => true])
        ->assertJsonCount(2, 'data.data');
});

test('shows order details for owner', function () {
    // Arrange
    $order = Order::factory()->create(['customer_id' => $this->user->id]);

    // Act
    $response = $this->actingAs($this->user)
        ->getJson("/api/orders/{$order->id}");

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data'    => [
                'id'          => $order->id,
                'customer_id' => $this->user->id,
            ],
        ]);
});

test('denies access to other users orders', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $order = Order::factory()->create(['customer_id' => $otherUser->id]);

    // Act
    $response = $this->actingAs($this->user)
        ->getJson("/api/orders/{$order->id}");

    // Assert
    $response->assertStatus(403);
});

test('updates order status successfully', function () {
    // Arrange
    $order = Order::factory()->create([
        'customer_id' => $this->user->id,
        'status'      => 'pending',
    ]);

    // Act
    $response = $this->actingAs($this->user)
        ->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'confirmed',
            'notes'  => 'Payment confirmed',
        ]);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => __('orders.status_updated'),
        ]);

    $this->assertDatabaseHas('orders', [
        'id'     => $order->id,
        'status' => 'confirmed',
    ]);
});

test('cancels order successfully', function () {
    // Arrange
    $order = Order::factory()->create([
        'customer_id' => $this->user->id,
        'status'      => 'pending',
    ]);

    // Act
    $response = $this->actingAs($this->user)
        ->postJson("/api/orders/{$order->id}/cancel", [
            'reason' => 'Customer changed mind',
        ]);

    // Assert
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => __('messages.orders'),
        ]);

    $this->assertDatabaseHas('orders', [
        'id'     => $order->id,
        'status' => 'cancelled',
    ]);
});
