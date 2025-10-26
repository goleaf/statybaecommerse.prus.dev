<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\OrderController as BaseOrderController;
use App\Support\Contracts\Entities\OrderContract;
use App\Traits\HandlesContentNegotiation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class OrderController extends BaseOrderController
{
    use HandlesContentNegotiation;

    /**
     * Define the set of order statuses that should remain visible via the API.
     *
     * Using explicit string values avoids repeatedly instantiating enums during
     * each request while still keeping the list centralised for maintenance.
     *
     * @var array<int, string>
     */
    private const VIEWABLE_STATUS_VALUES = [
        OrderStatus::PENDING->value,
        OrderStatus::PROCESSING->value,
        OrderStatus::SHIPPED->value,
        OrderStatus::DELIVERED->value,
    ];

    public function show(Request $request, string $orderIdentifier): JsonResponse|View|Response
    {
        // Reuse the shared base resolver so number/ID handling and visibility
        // checks remain in sync with other order controllers.
        $order = $this->resolveOrderForRequest($request, $orderIdentifier);

        // Apply the policy-driven ownership/permission checks before
        // serialising the order payload.
        $this->authorizeOrderView($request, $order);

        $order->loadMissing(['items']);

        $payload = OrderContract::forOrder($order);

        return $this->respondWithContract($request, $payload);
    }
}
