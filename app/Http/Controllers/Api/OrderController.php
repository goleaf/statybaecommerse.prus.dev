<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Scopes\StatusScope;
use App\Support\Contracts\Entities\OrderContract;
use App\Traits\HandlesContentNegotiation;
use BackedEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class OrderController extends Controller
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
        // Preserve visibility for legacy "completed" rows that still appear in
        // historical datasets so the API keeps parity with the admin panels.
        OrderStatus::COMPLETED->value,
    ];

    public function show(Request $request, string $orderIdentifier): JsonResponse|View|Response
    {
        // Resolve the order lazily so we can tailor error responses and apply
        // additional ownership constraints without relying on implicit binding,
        // which currently collides with the scoped model configuration.
        /** @var Order $order */
        $order = Order::query()
            // Only remove the status scope so soft delete protections remain in
            // place, ensuring cancelled orders remain hidden by default.
            ->withoutGlobalScope(StatusScope::class)
            ->where(static function ($query) use ($orderIdentifier): void {
                $query->where('number', $orderIdentifier);

                if (ctype_digit($orderIdentifier)) {
                    // Fallback to the primary key comparison so numeric route
                    // segments continue to work alongside the public order
                    // number identifier.
                    $column = $query->qualifyColumn($query->getModel()->getKeyName());

                    $query->orWhere($column, (int) $orderIdentifier);
                }
            })
            ->firstOrFail();

        /** @var BackedEnum|string $status */
        $status = $order->status;
        $statusValue = $status instanceof BackedEnum ? $status->value : (string) $status;

        // Abort with a not found response when the order is in a terminal state
        // (such as cancelled or refunded) to avoid leaking sensitive details.
        if (! in_array($statusValue, self::VIEWABLE_STATUS_VALUES, true)) {
            abort(404, 'Order is not available.');
        }

        $user = $request->user();

        if ($user === null) {
            // The route is guarded by authentication middleware, but we still
            // guard against accidental misconfiguration that could allow guest
            // access to leaked order data.
            abort(401, 'Authentication is required to view this order.');
        }

        // Defer to the authorization policy so users with explicit permissions
        // (e.g. support agents or administrators) can access orders they do not
        // own while regular customers remain restricted to their purchases.
        if ($user->cannot('view', $order)) {
            abort(403, 'You are not allowed to view this order.');
        }

        $order->loadMissing(['items']);

        $payload = OrderContract::forOrder($order);

        return $this->respondWithContract($request, $payload);
    }
}
