<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListItemResource\Widgets;

use App\Models\PriceListItem;
use Filament\Widgets\ChartWidget;

final class PriceListItemChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Price List Items Overview';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = PriceListItem::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_items,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_items,
                SUM(CASE WHEN compare_amount IS NOT NULL AND compare_amount > net_amount THEN 1 ELSE 0 END) as items_with_discount
            ')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.price_list_items.charts.total_items'),
                    'data' => $data->pluck('total_items')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => __('admin.price_list_items.charts.active_items'),
                    'data' => $data->pluck('active_items')->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
                [
                    'label' => __('admin.price_list_items.charts.items_with_discount'),
                    'data' => $data->pluck('items_with_discount')->toArray(),
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
