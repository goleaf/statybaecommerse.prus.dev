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
        // Pending orders remain visible so customers can double-check newly
        // placed purchases before fulfilment begins.
        OrderStatus::PENDING->value,
        // The platform retired the dedicated "confirmed" enum in favour of the
        // canonical processing state, so we keep processing in the list to
        // cover the previous lifecycle step without referencing the removed
        // constant.
        OrderStatus::PROCESSING->value,
        // Shipments and delivered orders should stay accessible for tracking
        // links and proof-of-delivery receipts.
        OrderStatus::SHIPPED->value,
        OrderStatus::DELIVERED->value,
        // Completed captures legacy data rows that predate the delivered
        // status rename and should still be customer-visible.
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
        // The status attribute may be materialised as an enum instance (when
        // coming directly from casts) or downgraded to a scalar string (after
        // serialization or attribute array access). Use `getAttribute()` to
        // inspect the raw value so the downstream visibility check can operate
        // on a consistent scalar representation without upsetting static
        // analysers.
        /** @var BackedEnum|string|null $statusAttribute */
        $statusAttribute = $order->getAttribute('status');

        $statusValue = $statusAttribute instanceof BackedEnum ? $statusAttribute->value : (string) $statusAttribute;

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
