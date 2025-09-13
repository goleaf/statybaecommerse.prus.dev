<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class CurrencyUsageChartWidget extends ChartWidget
{
    protected ?string $heading = 'Currency Usage in Orders';

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $currencyUsage = Order::select('currency_id', DB::raw('count(*) as count'))
            ->with('currency:id,name,code')
            ->groupBy('currency_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $labels = $currencyUsage->map(fn ($item) => $item->currency ? $item->currency->code : 'Unknown')->toArray();
        $data = $currencyUsage->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => __('admin.currency.widgets.orders_count'),
                    'data' => $data,
                    'backgroundColor' => [
                        '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                        '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6B7280',
                    ],
                    'borderColor' => [
                        '#1E40AF', '#059669', '#D97706', '#DC2626', '#7C3AED',
                        '#0891B2', '#65A30D', '#EA580C', '#DB2777', '#4B5563',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": " + context.parsed + " " + "'.__('admin.currency.widgets.orders').'";
                        }',
                    ],
                ],
            ],
        ];
    }
}
