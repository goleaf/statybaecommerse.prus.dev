<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend\Partner;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Partner;
use App\Support\Contracts\Entities\OrderContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

final class OrderDashboardController extends Controller
{
    /**
     * Status segment lookup so the UI can translate friendly segment names to concrete order status enums.
     *
     * @var array<string, list<OrderStatus>>
     */
    private const STATUS_SEGMENTS = [
        'open' => [OrderStatus::PENDING, OrderStatus::PROCESSING],
        'shipped' => [OrderStatus::SHIPPED, OrderStatus::DELIVERED],
        'cancelled' => [OrderStatus::CANCELLED, OrderStatus::REFUNDED, OrderStatus::RETURNED],
    ];

    /**
     * Render the partner order dashboard with scoped data for the authenticated partner user.
     */
    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        // Fail fast if authentication middleware was bypassed for any reason.
        if ($user === null) {
            return response()->view('frontend.partner.orders', [
                'orderPayload' => null,
                'paginator' => null,
                'activeStatus' => 'open',
                'errorCode' => Response::HTTP_UNAUTHORIZED,
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var Partner|null $partner */
        $partner = $user->active_partner;

        // Provide a guarded empty state when the user does not belong to any active partner account.
        if (! $partner instanceof Partner) {
            return response()->view('frontend.partner.orders', [
                'orderPayload' => null,
                'paginator' => null,
                'activeStatus' => 'open',
                'errorCode' => Response::HTTP_FORBIDDEN,
            ], Response::HTTP_FORBIDDEN);
        }

        // Normalise the requested segment to one of the supported keys so the UI cannot break pagination URLs.
        $segment = $request->string('status')->toString();
        if (! array_key_exists($segment, self::STATUS_SEGMENTS)) {
            $segment = 'open';
        }

        // Compose the base order query so partners are restricted to their own records with deterministic ordering.
        $query = Order::query()
            ->with(['items'])
            ->where('partner_id', $partner->getKey())
            ->orderByDesc('created_at');

        // Apply status constraints when a specific segment is selected.
        $statusEnums = self::STATUS_SEGMENTS[$segment] ?? [];
        if ($statusEnums !== []) {
            $query->whereIn('status', Collection::make($statusEnums)->map(static fn (OrderStatus $status): string => $status->value));
        }

        // Clamp pagination to a safe range so the dashboard stays responsive even on large partner accounts.
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = min(100, max(1, $perPage));

        /** @var LengthAwarePaginator<int, Order> $paginator */
        $paginator = $query->paginate($perPage, ['*'], 'page', (int) $request->integer('page', 1));

        // Repackage the paginator collection using the public contract so the Blade view works with the new response shape.
        $payload = OrderContract::forCollection($paginator->getCollection(), [
            'partner' => [
                'id' => $partner->getKey(),
                'code' => $partner->getAttribute('code'),
                'name' => $partner->getAttribute('name'),
            ],
            'filters' => [
                'status_segment' => $segment,
            ],
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);

        return response()->view('frontend.partner.orders', [
            'orderPayload' => $payload,
            'paginator' => $paginator,
            'activeStatus' => $segment,
            'errorCode' => null,
        ]);
    }
}
