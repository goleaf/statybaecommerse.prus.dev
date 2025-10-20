<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Contracts\Entities\OrderContract;
use Illuminate\Http\JsonResponse;

final class OrderController extends Controller
{
    public function show(Order $order): JsonResponse
    {
        $order->load(['items.product', 'user.roles', 'shipping']);

        return response()->json([
            'success' => true,
            'data' => [
                'order' => OrderContract::fromModel($order),
            ],
            'timestamp' => now()->toISOString(),
        ]);
    }
}
