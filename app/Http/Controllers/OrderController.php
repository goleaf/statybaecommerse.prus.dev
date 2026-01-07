<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\Orders\CreateOrderData;
use App\Data\Orders\OrderFilterData;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thin controller delegating to OrderManagementService
 *
 * Responsibilities:
 * - HTTP request/response handling
 * - Input validation
 * - Response formatting
 * - Authentication/authorization
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderManagementService $orderService
    ) {
        parent::__construct();
    }

    /**
     * Create new order
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $orderData = CreateOrderData::from($request->validated());

        $result = $this->orderService->createOrder($orderData);

        if ($result->isError()) {
            return response()->json([
                'success' => false,
                'message' => $result->message,
                'errors'  => $result->errors,
            ], $result->code);
        }

        return response()->json([
            'success' => true,
            'message' => $this->t('orders.created_successfully'),
            'data'    => $result->data,
        ], 201);
    }

    /**
     * Get user's orders with filtering
     */
    public function index(Request $request): JsonResponse
    {
        $filters = OrderFilterData::from($request->query());

        $result = $this->orderService->getUserOrders(
            $request->user(),
            $filters
        );

        if ($result->isError()) {
            return response()->json([
                'success' => false,
                'message' => $result->message,
            ], $result->code);
        }

        return response()->json([
            'success' => true,
            'data'    => $result->data,
        ]);
    }

    /**
     * Get order details
     */
    public function show(Request $request, int $orderId): JsonResponse
    {
        $result = $this->orderService->getOrderDetails($orderId, $request->user());

        if ($result->isError()) {
            return response()->json([
                'success' => false,
                'message' => $result->message,
            ], $result->code);
        }

        return response()->json([
            'success' => true,
            'data'    => $result->data,
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $result = $this->orderService->updateOrderStatus(
            $order,
            $request->validated('status'),
            $request->validated('notes')
        );

        if ($result->isError()) {
            return response()->json([
                'success' => false,
                'message' => $result->message,
            ], $result->code);
        }

        return response()->json([
            'success' => true,
            'message' => $this->t('orders.status_updated'),
            'data'    => $result->data,
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $result = $this->orderService->cancelOrder(
            $order,
            $request->input('reason')
        );

        if ($result->isError()) {
            return response()->json([
                'success' => false,
                'message' => $result->message,
            ], $result->code);
        }

        return response()->json([
            'success' => true,
            'message' => $this->t('orders.cancelled_successfully'),
            'data'    => $result->data,
        ]);
    }
}
