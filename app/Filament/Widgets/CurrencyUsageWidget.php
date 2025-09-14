<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Currency;
use Filament\Widgets\ChartWidget;

final /**
 * CurrencyUsageWidget
 * 
 * Filament widget for admin panel dashboard.
 */
class CurrencyUsageWidget extends ChartWidget
{
    protected ?string $heading = 'Currency Usage Distribution';

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $currencyUsage = Currency::withCount(['zones', 'prices'])
            ->where('is_enabled', true)
            ->get()
            ->map(function ($currency) {
                return [
                    'name' => $currency->code.' ('.$currency->symbol.')',
                    'zones' => $currency->zones_count,
                    'prices' => $currency->prices_count,
                    'total' => $currency->zones_count + $currency->prices_count,
                ];
            })
            ->sortByDesc('total')
            ->take(8);

        return [
            'datasets' => [
                [
                    'label' => __('currency_zones_count'),
                    'data' => $currencyUsage->pluck('zones')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => __('currency_prices_count'),
                    'data' => $currencyUsage->pluck('prices')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => 'rgba(16, 185, 129, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $currencyUsage->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                ],
                'y' => [
                    'beginAtZero' => true,
                    'stacked' => true,
                ],
            ],
        ];
    }
}
