<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Support\Stats\OrderMetrics;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class OrderResourceStats extends BaseWidget
{
    use InteractsWithDateFilter;

    protected static ?string $heading = 'Order performance';

    protected static ?string $badge = 'Orders';

    protected static ?string $badgeColor = 'primary';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today'   => __('Today'),
            'week'    => __('Last week'),
            'month'   => __('Last month'),
            'quarter' => __('This quarter'),
            'year'    => __('This year'),
            'ytd'     => __('Year to date'),
        ];
    }

    protected function getStats(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);
        $metrics = OrderMetrics::forRange($from, $to);

        return [
            Stat::make(__('Orders'), Number::format($metrics['orders']))
                ->icon('heroicon-o-shopping-bag')
                ->iconBackgroundColor('primary')
                ->description(__('Orders captured')),
            Stat::make(__('Revenue'), Number::currency($metrics['revenue'], 'EUR'))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->valueColor('success')
                ->description(__('Total revenue')),
            Stat::make(__('Average order value'), Number::currency($metrics['aov'], 'EUR'))
                ->icon('heroicon-o-calculator')
                ->iconBackgroundColor('info')
                ->valueColor('info')
                ->description(__('Average basket size')),
            Stat::make(__('Refund rate'), Number::format($metrics['refundRate'], 2) . ' %')
                ->icon('heroicon-o-arrow-uturn-left')
                ->iconBackgroundColor('danger')
                ->valueColor('danger')
                ->description(__('Refunded orders')),
        ];
    }
}
