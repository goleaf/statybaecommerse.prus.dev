<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Contracts\Entities\OrderContract;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class OrderController extends Controller
{
    use HandlesContentNegotiation;

    public function show(Request $request, string $orderNumber): JsonResponse|View|Response
    {
        // Resolve the order lazily so we can tailor error responses and apply
        // additional ownership constraints without relying on implicit binding,
        // which currently collides with the scoped model configuration.
        /** @var Order $order */
        $order = Order::query()
            ->withoutGlobalScopes()
            ->where('number', $orderNumber)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $allowedStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed'];

        if (! in_array((string) $order->status, $allowedStatuses, true)) {
            abort(404, 'Order is not available.');
        }

        $user = $request->user();

        if ($user !== null && $order->user_id !== $user->getKey()) {
            abort(403, 'You are not allowed to view this order.');
        }

        $order->loadMissing(['items']);

        $payload = OrderContract::forOrder($order);

        return $this->respondWithContract($request, $payload);
    }
}
