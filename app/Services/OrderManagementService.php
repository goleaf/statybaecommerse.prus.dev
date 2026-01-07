<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\UpdateOrderStatusAction;
use App\Data\Common\ServiceResponseData;
use App\Data\Orders\CreateOrderData;
use App\Data\Orders\OrderFilterData;
use App\Models\Order;
use App\Models\User;

/**
 * Order management service orchestrating order lifecycle operations
 *
 * Responsibilities:
 * - Order creation and validation
 * - Status management and transitions
 * - Order cancellation and refunds
 * - Order history and reporting
 */
final class OrderManagementService extends BaseService
{
    public function __construct(
        private readonly CreateOrderAction $createOrderAction,
        private readonly UpdateOrderStatusAction $updateStatusAction,
        private readonly CancelOrderAction $cancelOrderAction,
        private readonly NotificationService $notificationService,
        private readonly InventoryService $inventoryService
    ) {
        parent::__construct();
        $this->enablePerformanceLogging(500); // Orders are critical, monitor closely
    }

    /**
     * Create new order from checkout data
     */
    public function createOrder(CreateOrderData $orderData): ServiceResponseData
    {
        return $this->executeInTransaction(function () use ($orderData) {
            $this->withContext(['operation' => 'create_order', 'customer_id' => $orderData->customerId]);

            // Validate inventory availability
            $inventoryCheck = $this->inventoryService->validateAvailability($orderData->items);
            if ($inventoryCheck->isError()) {
                return $inventoryCheck;
            }

            // Create the order
            $order = $this->createOrderAction->execute($orderData);

            // Reserve inventory
            $this->inventoryService->reserveStock($orderData->items);

            // Send notifications
            $this->notificationService->createOrderNotification(
                $order->customer,
                'created',
                ['id' => $order->id, 'number' => $order->number]
            );

            $this->log('info', 'Order created successfully', [
                'order_id'     => $order->id,
                'order_number' => $order->number,
                'total_amount' => $order->grand_total_amount,
            ]);

            return $order;
        });
    }

    /**
     * Update order status with proper validation and side effects
     */
    public function updateOrderStatus(Order $order, string $newStatus, ?string $notes = null): ServiceResponseData
    {
        return $this->executeInTransaction(function () use ($order, $newStatus, $notes) {
            $this->withContext([
                'operation'  => 'update_status',
                'order_id'   => $order->id,
                'old_status' => $order->status,
                'new_status' => $newStatus,
            ]);

            // Validate ownership
            if (! $this->validateOwnership($order)) {
                return $this->error(__('orders.access_denied'));
            }

            // Validate status transition
            if (! $this->isValidStatusTransition($order->status, $newStatus)) {
                return $this->error(__('orders.invalid_status_transition'));
            }

            // Update status
            $updatedOrder = $this->updateStatusAction->execute($order, $newStatus, $notes);

            // Handle side effects based on new status
            $this->handleStatusSideEffects($updatedOrder, $newStatus);

            $this->log('info', 'Order status updated', [
                'order_id'   => $order->id,
                'old_status' => $order->status,
                'new_status' => $newStatus,
                'notes'      => $notes,
            ]);

            return $updatedOrder;
        });
    }

    /**
     * Cancel order with inventory restoration
     */
    public function cancelOrder(Order $order, string $reason): ServiceResponseData
    {
        return $this->executeInTransaction(function () use ($order, $reason) {
            $this->withContext([
                'operation' => 'cancel_order',
                'order_id'  => $order->id,
                'reason'    => $reason,
            ]);

            // Validate ownership and cancellation eligibility
            if (! $this->validateOwnership($order)) {
                return $this->error(__('orders.access_denied'));
            }

            if (! $this->canCancelOrder($order)) {
                return $this->error(__('orders.cannot_cancel'));
            }

            // Cancel the order
            $cancelledOrder = $this->cancelOrderAction->execute($order, $reason);

            // Restore inventory
            $this->inventoryService->restoreStock($order->items);

            // Send notification
            $this->notificationService->createOrderNotification(
                $order->customer,
                'cancelled',
                ['id' => $order->id, 'number' => $order->number]
            );

            $this->log('info', 'Order cancelled', [
                'order_id' => $order->id,
                'reason'   => $reason,
            ]);

            return $cancelledOrder;
        });
    }

    /**
     * Get user's orders with filtering and pagination
     */
    public function getUserOrders(User $user, OrderFilterData $filters): ServiceResponseData
    {
        return $this->execute(function () use ($user, $filters) {
            $this->withContext([
                'operation' => 'get_user_orders',
                'user_id'   => $user->id,
            ]);

            $query = Order::query()
                ->where('customer_id', $user->id)
                ->with(['items.product', 'shippingAddress', 'billingAddress'])
                ->latest();

            // Apply filters
            $filters->apply($query);

            $orders = $query->paginate($filters->perPage ?? 15);

            return $orders;
        });
    }

    /**
     * Get order details with ownership validation
     */
    public function getOrderDetails(int $orderId, ?User $user = null): ServiceResponseData
    {
        return $this->execute(function () use ($orderId, $user) {
            $order = Order::with([
                'items.product',
                'shippingAddress',
                'billingAddress',
                'customer',
            ])->find($orderId);

            if (! $order) {
                return $this->error(__('orders.not_found'), null, 404);
            }

            // Validate ownership if user provided
            if ($user && ! $this->validateOwnership($order, $user->id)) {
                return $this->error(__('orders.access_denied'), null, 403);
            }

            return $order;
        });
    }

    /**
     * Validate if status transition is allowed
     */
    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $allowedTransitions = [
            'pending'    => ['confirmed', 'cancelled'],
            'confirmed'  => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped'    => ['delivered'],
            'delivered'  => [],
            'cancelled'  => [],
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    /**
     * Handle side effects when order status changes
     */
    private function handleStatusSideEffects(Order $order, string $newStatus): void
    {
        match ($newStatus) {
            'confirmed' => $this->handleOrderConfirmed($order),
            'shipped'   => $this->handleOrderShipped($order),
            'delivered' => $this->handleOrderDelivered($order),
            'cancelled' => $this->handleOrderCancelled($order),
            default     => null,
        };
    }

    private function handleOrderConfirmed(Order $order): void
    {
        // Deduct inventory from reserved to actual
        $this->inventoryService->confirmReservation($order->items);

        // Send confirmation notification
        $this->notificationService->createOrderNotification(
            $order->customer,
            'confirmed',
            ['id' => $order->id, 'number' => $order->number]
        );
    }

    private function handleOrderShipped(Order $order): void
    {
        // Send shipping notification with tracking info
        $this->notificationService->createOrderNotification(
            $order->customer,
            'shipped',
            ['id' => $order->id, 'number' => $order->number]
        );
    }

    private function handleOrderDelivered(Order $order): void
    {
        // Mark as delivered and send notification
        $this->notificationService->createOrderNotification(
            $order->customer,
            'delivered',
            ['id' => $order->id, 'number' => $order->number]
        );
    }

    private function handleOrderCancelled(Order $order): void
    {
        // Inventory restoration is handled in cancelOrder method
        // Additional cleanup can be added here
    }

    /**
     * Check if order can be cancelled
     */
    private function canCancelOrder(Order $order): bool
    {
        return in_array($order->status, ['pending', 'confirmed', 'processing']);
    }
}
