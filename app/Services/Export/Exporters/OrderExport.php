<?php

declare(strict_types=1);

namespace App\Services\Export\Exporters;

use App\Data\Pricing\PriceBreakdown;
use App\Models\Export;
use App\Models\Order;
use App\Services\Export\Contracts\Exportable;
use App\Services\Export\ExportColumn;
use App\Services\Pricing\PriceCalculator;
use App\Services\Pricing\PriceConfiguration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class OrderExport implements Exportable
{
    public function __construct(private readonly PriceCalculator $priceCalculator) {}

    public function name(): string
    {
        return __('Orders Export');
    }

    public function columns(): array
    {
        return [
            'number'         => new ExportColumn('number', __('orders.fields.order_number'), 'number'),
            'status'         => new ExportColumn('status', __('orders.fields.status'), 'status'),
            'payment_status' => new ExportColumn('payment_status', __('orders.fields.payment_status'), 'payment_status'),
            'total'          => new ExportColumn('total', __('orders.fields.total'), resolver: function (Order $order): string {
                $configuration = app(PriceConfiguration::class);
                $breakdown = PriceBreakdown::fromOrder($order, $configuration);

                return $breakdown->toSummary()['formatted_total'];
            }),
            'customer_name'  => new ExportColumn('customer_name', __('orders.fields.customer'), resolver: fn (Order $order): string => $order->user?->name ?? ''),
            'customer_email' => new ExportColumn('customer_email', __('orders.fields.customer_email'), resolver: fn (Order $order): string => $order->user?->email ?? ''),
            'created_at'     => new ExportColumn('created_at', __('orders.fields.created_at'), 'created_at'),
        ];
    }

    public function defaultColumns(): array
    {
        return ['number', 'status', 'total', 'created_at'];
    }

    public function query(array $options = []): Builder
    {
        return Order::query()->with('user');
    }

    public function fileName(Export $export): string
    {
        return 'orders-export';
    }

    public function map(Model $model, array $columns): array
    {
        /** @var Order $model */
        return collect($columns)
            ->map(fn (ExportColumn $column): string => $column->resolve($model))
            ->values()
            ->all();
    }
}
