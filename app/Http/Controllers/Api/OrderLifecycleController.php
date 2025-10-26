<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderRefundRequest;
use App\Http\Requests\Api\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Support\ListQuery\ListQueryDefinition;
use App\Support\ListQuery\ListQueryValidator;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

/**
 * OrderLifecycleController
 *
 * HTTP controller that exposes a JSON API for browsing and managing orders
 * while enforcing the domain lifecycle constraints (view/update/cancel/refund).
 */
final class OrderLifecycleController extends Controller
{
    /**
     * Display a listing of orders with pagination, sorting, and a controlled filter allow-list.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Order::class);

        // Define the list query behaviour so consumers cannot arbitrarily sort/filter columns.
        $definition = new ListQueryDefinition(
            filters: [
                'number' => [
                    'type'   => 'string',
                    'column' => 'orders.number',
                ],
                'status' => [
                    'type'    => 'string',
                    'column'  => 'orders.status',
                    'allowed' => array_map(static fn (OrderStatus $status): string => $status->value, OrderStatus::cases()),
                ],
                'payment_status' => [
                    'type'    => 'string',
                    'column'  => 'orders.payment_status',
                    'allowed' => array_map(static fn (PaymentStatus $status): string => $status->value, PaymentStatus::cases()),
                ],
                'user_id' => [
                    'type'   => 'int',
                    'column' => 'orders.user_id',
                ],
                'created_from' => [
                    'type'     => 'datetime',
                    'callback' => static function (Builder $builder, DateTimeInterface $date): void {
                        // Ensure the range filter respects inclusive boundaries for repeatable exports.
                        $builder->where('orders.created_at', '>=', $date);
                    },
                ],
                'created_to' => [
                    'type'     => 'datetime',
                    'callback' => static function (Builder $builder, DateTimeInterface $date): void {
                        // Inclusive end of range keeps dashboard widgets aligned with exports.
                        $builder->where('orders.created_at', '<=', $date);
                    },
                ],
            ],
            sortable: [
                'created_at' => [
                    'column'            => 'orders.created_at',
                    'default_direction' => 'desc',
                ],
                'number' => [
                    'column' => 'orders.number',
                ],
                'total' => [
                    'column' => 'orders.total',
                ],
            ],
            defaultSort: 'created_at',
            defaultDirection: 'desc',
            defaultPerPage: 15,
            maxPerPage: 50,
        );

        $listQuery = ListQueryValidator::fromRequest($request, $definition);

        $orders = Order::query()
            ->with($this->relationsToEagerLoad())
            ->tap(fn (Builder $builder) => $listQuery->apply($builder))
            ->paginate($listQuery->perPage(), ['*'], 'page', $listQuery->page());

        return OrderResource::collection($orders);
    }

    /**
     * Display a specific order using the shared eager-loading strategy.
     */
    public function show(string $orderIdentifier): OrderResource
    {
        $order = $this->resolveOrder($orderIdentifier);

        Gate::authorize('view', $order);

        $order->loadMissing($this->relationsToEagerLoad());

        return new OrderResource($order);
    }

    /**
     * Update an order's mutable attributes while respecting policy checks.
     */
    public function update(OrderUpdateRequest $request, string $orderIdentifier): OrderResource
    {
        $order = $this->resolveOrder($orderIdentifier);

        Gate::authorize('update', $order);

        $payload = $request->validated();

        // Explicitly map the subset of attributes we want to expose to the API client.
        $order->fill(Arr::only($payload, ['status', 'payment_status', 'notes']));

        if (array_key_exists('transactions', $payload)) {
            // Allow support tooling to reconcile payment gateway responses.
            $order->transactions = $payload['transactions'];
        }

        $order->save();

        $order->loadMissing($this->relationsToEagerLoad());

        return new OrderResource($order);
    }

    /**
     * Soft-delete an order record and respond with a 204 No Content payload.
     */
    public function destroy(string $orderIdentifier): Response
    {
        $order = $this->resolveOrder($orderIdentifier);

        Gate::authorize('delete', $order);

        $order->delete();

        return response()->noContent();
    }

    /**
     * Cancel an order before fulfilment begins and return an empty 204 response.
     */
    public function cancel(string $orderIdentifier): Response
    {
        $order = $this->resolveOrder($orderIdentifier);

        Gate::authorize('cancel', $order);

        if (! $order->canBeCancelled()) {
            abort(422, 'Order can only be cancelled before fulfilment.');
        }

        $order->status = OrderStatus::CANCELLED;

        if ($order->payment_status instanceof PaymentStatus && $order->payment_status === PaymentStatus::PAID) {
            // Flag any paid order as refunded so downstream finance reports stay in sync.
            $order->payment_status = PaymentStatus::REFUNDED;
        }

        $order->save();

        return response()->noContent();
    }

    /**
     * Refund an order and surface the updated payload as a JSON resource.
     */
    public function refund(OrderRefundRequest $request, string $orderIdentifier): OrderResource
    {
        $order = $this->resolveOrder($orderIdentifier);

        Gate::authorize('refund', $order);

        $payload = $request->validated();

        $order->status = OrderStatus::REFUNDED;
        $order->payment_status = PaymentStatus::REFUNDED;

        // Append a structured refund record to the existing transactions array.
        $transactions = (array) ($order->transactions ?? []);
        $transactions[] = [
            'type'         => 'refund',
            'amount'       => (float) ($payload['amount'] ?? $order->total),
            'currency'     => $order->currency,
            'reason'       => $payload['reason'] ?? null,
            'processed_at' => now()->toISOString(),
        ];

        $order->transactions = $transactions;

        if (array_key_exists('notes', $payload)) {
            // Attach operator notes so follow-up audits can trace who actioned the refund.
            $order->notes = trim((string) $payload['notes']);
        }

        $order->save();

        $order->loadMissing($this->relationsToEagerLoad());

        return new OrderResource($order);
    }

    /**
     * Resolve an order by number or primary key so we can handle mixed identifiers gracefully.
     */
    private function resolveOrder(string $identifier): Order
    {
        $order = Order::query()
            ->where('number', $identifier)
            ->when(ctype_digit($identifier), static function (Builder $query) use ($identifier): void {
                $query->orWhere($query->getModel()->getKeyName(), (int) $identifier);
            })
            ->first();

        if ($order === null) {
            throw (new ModelNotFoundException)->setModel(Order::class, [$identifier]);
        }

        return $order;
    }

    /**
     * Provide the shared relation list so every endpoint returns consistent payloads.
     *
     * @return array<int, string>
     */
    private function relationsToEagerLoad(): array
    {
        // Payment history lives within the transactions JSON column, so no dedicated relation is required here.
        return [
            'items', // Order lines: expose sku/quantity/price breakdowns.
            'items.product', // Surface related product metadata without additional queries for large lists.
            'shipping', // Shipment details including tracking meta for fulfilment dashboards.
        ];
    }
}
