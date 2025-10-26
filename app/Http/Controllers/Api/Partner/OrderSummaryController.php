<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Partner;

use App\Enums\OrderStatus;
use App\Models\ApiKey;
use App\Models\Order;
use App\Support\DateParser;
use App\Support\DateRange;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class OrderSummaryController
{
    public function __invoke(Request $request): JsonResponse
    {
        // Extract the requested timeframe from the query string while gracefully falling
        // back to the last 30 days when integrators omit explicit filters.
        [$fromInput, $toInput] = DateRange::extract($request->query(), 'from', 'to');

        $now = CarbonImmutable::now();
        $fromCandidate = DateParser::parse($fromInput) ?? $now->subDays(30);
        $toCandidate = DateParser::parse($toInput) ?? $now;

        $from = CarbonImmutable::make($fromCandidate)?->startOfDay() ?? $now->subDays(30)->startOfDay();
        $to = CarbonImmutable::make($toCandidate)?->endOfDay() ?? $now->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
        }

        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('partner_api_key');

        // Partners tied to an API key should only observe their own order activity.
        $partnerId = $apiKey instanceof ApiKey ? $apiKey->getAttribute('partner_id') : null;

        $baseQuery = Order::query()
            ->when($partnerId !== null, static fn (Builder $query): Builder => $query->where('partner_id', $partnerId))
            ->createdBetween($from, $to);

        $ordersCount = (clone $baseQuery)->count();
        $revenue = (float) (clone $baseQuery)->sum('total');
        $averageOrderValue = $ordersCount > 0 ? round($revenue / $ordersCount, 2) : 0.0;

        // Calculate status distributions so partners can assess pipeline health quickly.
        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->whereNotNull('status')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusBreakdown = Collection::make(OrderStatus::cases())
            ->map(static function (OrderStatus $status) use ($statusCounts): array {
                return [
                    'status' => $status->value,
                    'label'  => $status->label(),
                    'count'  => (int) $statusCounts->get($status->value, 0),
                ];
            })
            ->values()
            ->all();

        $paymentStatusBreakdown = (clone $baseQuery)
            ->selectRaw('payment_status, COUNT(*) as aggregate')
            ->whereNotNull('payment_status')
            ->groupBy('payment_status')
            ->get()
            ->map(static fn ($row): array => [
                'status' => (string) $row->payment_status,
                'count'  => (int) $row->aggregate,
            ])
            ->values()
            ->all();

        $currencyBreakdown = (clone $baseQuery)
            ->selectRaw('currency, COUNT(*) as orders_count, SUM(total) as revenue')
            ->groupBy('currency')
            ->get()
            ->map(static fn ($row): array => [
                'currency' => (string) $row->currency,
                'orders'   => (int) $row->orders_count,
                'revenue'  => round((float) $row->revenue, 2),
            ])
            ->values()
            ->all();

        $recentOrders = (clone $baseQuery)
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'number', 'status', 'total', 'currency', 'created_at'])
            ->map(static function (Order $order): array {
                $status = OrderStatus::tryFrom($order->getAttribute('status'));

                return [
                    'id'          => $order->getKey(),
                    'number'      => (string) $order->getAttribute('number'),
                    'status'      => (string) $order->getAttribute('status'),
                    'statusLabel' => $status?->label(),
                    'total'       => round((float) $order->getAttribute('total'), 2),
                    'currency'    => (string) $order->getAttribute('currency'),
                    'createdAt'   => $order->getAttribute('created_at')?->toISOString(),
                ];
            })
            ->all();

        $abilities = (array) $request->attributes->get('partner_api_abilities', []);

        return response()->json([
            'data' => [
                'timeframe' => [
                    'from' => $from->toIso8601String(),
                    'to'   => $to->toIso8601String(),
                ],
                'totals' => [
                    'orders'              => $ordersCount,
                    'revenue'             => round($revenue, 2),
                    'average_order_value' => $averageOrderValue,
                ],
                'status_breakdown'   => $statusBreakdown,
                'payment_statuses'   => $paymentStatusBreakdown,
                'currency_breakdown' => $currencyBreakdown,
                'recent_orders'      => $recentOrders,
                'filters'            => [
                    'partner_id' => $partnerId,
                ],
            ],
            'meta' => [
                'scopes' => array_values($abilities),
            ],
        ]);
    }
}
