<?php

declare(strict_types=1);

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\UpdateOrderStatusAction;
use App\Data\Orders\CreateOrderData;
use App\Data\Orders\OrderItemData;
use App\Models\Order;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\NotificationService;
use App\Services\OrderManagementService;
use Spatie\LaravelData\DataCollection;

beforeEach(function () {
    $this->createOrderAction = Mockery::mock(CreateOrderAction::class);
    $this->updateStatusAction = Mockery::mock(UpdateOrderStatusAction::class);
    $this->cancelOrderAction = Mockery::mock(CancelOrderAction::class);
    $this->notificationService = Mockery::mock(NotificationService::class);
    $this->inventoryService = Mockery::mock(InventoryService::class);

    $this->service = new OrderManagementService(
        $this->createOrderAction,
        $this->updateStatusAction,
        $this->cancelOrderAction,
        $this->notificationService,
        $this->inventoryService
    );
});

test('creates order successfully with valid data', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    $orderData = new CreateOrderData(
        customerId: $user->id,
        shippingAddressId: 1,
        billingAddressId: 1,
        shippingOptionId: 1,
        paymentMethodId: 1,
        paymentMethod: 'Credit Card',
        subtotal: 100.00,
        items: DataCollection::wrap([
            new OrderItemData(
                productId: 1,
                quantity: 2,
                unitPrice: 50.00,
                name: 'Test Product',
                sku: 'TEST-001'
            ),
        ])
    );

    $order = Order::factory()->make(['id' => 1, 'customer_id' => $user->id]);

    // Mock expectations
    $this->inventoryService
        ->shouldReceive('validateAvailability')
        ->once()
        ->with($orderData->items)
        ->andReturn(app(\App\Data\Common\ServiceResponseData::class)::success());

    $this->createOrderAction
        ->shouldReceive('execute')
        ->once()
        ->with($orderData)
        ->andReturn($order);

    $this->inventoryService
        ->shouldReceive('reserveStock')
        ->once()
        ->with($orderData->items);

    $this->notificationService
        ->shouldReceive('createOrderNotification')
        ->once();

    // Act
    $result = $this->service->createOrder($orderData);

    // Assert
    expect($result->isSuccess())->toBeTrue();
    expect($result->data)->toBe($order);
});

test('fails to create order when inventory unavailable', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    $orderData = new CreateOrderData(
        customerId: $user->id,
        shippingAddressId: 1,
        billingAddressId: 1,
        shippingOptionId: 1,
        paymentMethodId: 1,
        paymentMethod: 'Credit Card',
        subtotal: 100.00,
        items: DataCollection::wrap([
            new OrderItemData(
                productId: 1,
                quantity: 2,
                unitPrice: 50.00,
                name: 'Test Product',
                sku: 'TEST-001'
            ),
        ])
    );

    // Mock inventory failure
    $this->inventoryService
        ->shouldReceive('validateAvailability')
        ->once()
        ->with($orderData->items)
        ->andReturn(app(\App\Data\Common\ServiceResponseData::class)::error('Insufficient stock'));

    // Act
    $result = $this->service->createOrder($orderData);

    // Assert
    expect($result->isError())->toBeTrue();
    expect($result->message)->toBe('Insufficient stock');
});

test('updates order status with valid transition', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    $order = Order::factory()->create([
        'customer_id' => $user->id,
        'status'      => 'pending',
    ]);

    $updatedOrder = $order->replicate();
    $updatedOrder->status = 'confirmed';

    $this->updateStatusAction
        ->shouldReceive('execute')
        ->once()
        ->with($order, 'confirmed', null)
        ->andReturn($updatedOrder);

    $this->inventoryService
        ->shouldReceive('confirmReservation')
        ->once();

    $this->notificationService
        ->shouldReceive('createOrderNotification')
        ->once();

    // Act
    $result = $this->service->updateOrderStatus($order, 'confirmed');

    // Assert
    expect($result->isSuccess())->toBeTrue();
    expect($result->data->status)->toBe('confirmed');
});

test('fails to update order status with invalid transition', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    $order = Order::factory()->create([
        'customer_id' => $user->id,
        'status'      => 'delivered',
    ]);

    // Act
    $result = $this->service->updateOrderStatus($order, 'pending');

    // Assert
    expect($result->isError())->toBeTrue();
    expect($result->message)->toContain('invalid_status_transition');
});

test('cancels order successfully', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    $order = Order::factory()->create([
        'customer_id' => $user->id,
        'status'      => 'pending',
    ]);

    $cancelledOrder = $order->replicate();
    $cancelledOrder->status = 'cancelled';

    $this->cancelOrderAction
        ->shouldReceive('execute')
        ->once()
        ->with($order, 'Customer request')
        ->andReturn($cancelledOrder);

    $this->inventoryService
        ->shouldReceive('restoreStock')
        ->once();

    $this->notificationService
        ->shouldReceive('createOrderNotification')
        ->once();

    // Act
    $result = $this->service->cancelOrder($order, 'Customer request');

    // Assert
    expect($result->isSuccess())->toBeTrue();
    expect($result->data->status)->toBe('cancelled');
});

test('fails to cancel non-cancellable order', function () {
    // Arrange
    $user = User::factory()->create();
    $this->actingAs($user);

    $order = Order::factory()->create([
        'customer_id' => $user->id,
        'status'      => 'delivered',
    ]);

    // Act
    $result = $this->service->cancelOrder($order, 'Customer request');

    // Assert
    expect($result->isError())->toBeTrue();
    expect($result->message)->toContain('cannot_cancel');
});
