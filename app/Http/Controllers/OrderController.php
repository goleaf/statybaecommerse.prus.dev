<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Scopes\StatusScope;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Shared order controller utilities.
 *
 * The application exposes multiple order controllers (API, storefront, partner
 * portals, etc.). Historically each controller reimplemented the same lookup,
 * status filtering, and authorization logic which led to subtle behavioural
 * drift.  This abstract base centralises those concerns so every consumer can
 * resolve an order in a consistent, well-tested way.
 */
abstract class OrderController extends Controller
{
    /**
     * List of order statuses that remain publicly visible to authenticated
     * users.
     *
     * Keeping the list here ensures API and web controllers enforce identical
     * visibility rules without copy/paste arrays.
     *
     * @var array<int, string>
     */
    protected const VIEWABLE_STATUS_VALUES = [
        OrderStatus::PENDING->value,
        OrderStatus::CONFIRMED->value,
        OrderStatus::PROCESSING->value,
        OrderStatus::SHIPPED->value,
        OrderStatus::DELIVERED->value,
        OrderStatus::COMPLETED->value,
    ];

    /**
     * Resolve an order for the incoming request using a number or numeric ID.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When the
     *                                                              order cannot
     *                                                              be located.
     */
    protected function resolveOrderForRequest(Request $request, string $orderIdentifier): Order
    {
        // Query without the status scope so we can perform explicit visibility
        // checks afterwards (e.g. to hide cancelled/refunded orders).
        /** @var Order $order */
        $order = Order::query()
            ->withoutGlobalScope(StatusScope::class)
            ->where(function (Builder $query) use ($orderIdentifier): void {
                // Match orders by their public number first.
                $query->where('number', $orderIdentifier);

                // Fallback to numeric primary keys to support internal links
                // that still rely on IDs.
                if (ctype_digit($orderIdentifier)) {
                    // Using the qualified key name avoids ambiguity when joins
                    // are introduced by downstream consumers.
                    $query->orWhere(
                        $query->getModel()->getQualifiedKeyName(),
                        (int) $orderIdentifier,
                    );
                }
            })
            ->firstOrFail();

        $this->ensureOrderIsViewable($order);

        return $order;
    }

    /**
     * Abort the request when the resolved order is not meant to be visible.
     */
    protected function ensureOrderIsViewable(Order $order): void
    {
        // The status attribute may be an enum instance or raw string depending
        // on eager loading, so normalise it before the visibility check.
        $statusValue = $order->status instanceof BackedEnum
            ? $order->status->value
            : (string) $order->status;

        if (! in_array($statusValue, static::VIEWABLE_STATUS_VALUES, true)) {
            abort(404, 'Order is not available.');
        }
    }

    /**
     * Ensure the authenticated user is allowed to access the resolved order.
     */
    protected function authorizeOrderView(Request $request, Order $order): void
    {
        $user = $request->user();

        // Even though higher-level middleware normally guarantees
        // authentication, performing an explicit check provides a defensive
        // fallback against accidental misconfiguration.
        if ($user === null) {
            abort(401, 'Authentication is required to view this order.');
        }

        // Delegate to the order policy so elevated users (support agents,
        // administrators, etc.) continue to access foreign orders while regular
        // customers remain restricted to their own purchases.
        if ($user->cannot('view', $order)) {
            abort(403, 'You are not allowed to view this order.');
        }
    }
}
