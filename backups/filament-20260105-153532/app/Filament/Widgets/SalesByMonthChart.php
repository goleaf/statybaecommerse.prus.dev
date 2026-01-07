<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Support\Stats\OrderMetrics;
use EightyNine\FilamentAdvancedWidget\AdvancedChartWidget;
use Illuminate\Support\Number;

final class SalesByMonthChart extends AdvancedChartWidget
{
    use InteractsWithDateFilter;

    protected static ?string $heading = 'Revenue';

    protected static string $color = 'info';

    protected static ?string $icon = 'heroicon-o-chart-bar';

    protected static ?string $label = 'Sales by period';

    protected static ?string $badgeColor = 'success';

    public ?string $filter = 'year';

    protected function getFilters(): ?array
    {
        return [
            'month'   => __('Last month'),
            'quarter' => __('This quarter'),
            'year'    => __('This year'),
            'ytd'     => __('Year to date'),
        ];
    }

    protected function getData(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);

        $series = OrderMetrics::salesSeriesMonthly($from, $to);

        return [
            'datasets' => [
                [
                    'label'           => __('Revenue'),
                    'data'            => $series['values'],
                    'backgroundColor' => 'rgba(79, 70, 229, 0.15)',
                    'borderColor'     => '#4f46e5',
                    'borderWidth'     => 3,
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getBadge(): ?string
    {
        [$from, $to] = $this->getDateRange($this->filter);
        $series = OrderMetrics::salesSeriesMonthly($from, $to);
        $change = $series['change'];

        if ($change === null) {
            return __('New dataset');
        }

        if ($change === 0.0) {
            return __('Flat');
        }

        return ($change > 0 ? '▲ ' : '▼ ') . Number::format(abs($change), 1) . '%';
    }
}
