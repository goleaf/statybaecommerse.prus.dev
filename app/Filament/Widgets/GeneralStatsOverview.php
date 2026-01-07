<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Support\Stats\OrderMetrics;
use App\Support\Stats\TrafficMetrics;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Support\Number;

final class GeneralStatsOverview extends BaseWidget
{
    use InteractsWithDateFilter;

    protected ?string $heading = 'Store KPIs';

    protected static ?string $label = 'Key metrics';

    protected static ?string $icon = 'heroicon-o-chart-bar';

    protected static ?string $iconColor = 'info';

    protected static ?string $iconBackgroundColor = 'info';

    protected static ?string $badge = 'Live';

    protected static ?string $badgeColor = 'success';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today'        => __('Today'),
            'week'         => __('Last week'),
            'month'        => __('Last month'),
            'quarter'      => __('This quarter'),
            'year'         => __('This year'),
            'ytd'          => __('Year to date'),
            'last_30_days' => __('Last 30 days'),
        ];
    }

    protected function getStats(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);

        $orderMetrics = OrderMetrics::forRange($from, $to);
        $trafficMetrics = TrafficMetrics::forRange($from, $to);

        $revenueChange = $orderMetrics['revenueChange'];
        $ordersChange = $orderMetrics['ordersChange'];

        return [
            Stat::make(__('Revenue'), Number::currency($orderMetrics['revenue'], 'EUR'))
                ->description($this->formatChangeDescription($revenueChange))
                ->descriptionIcon($this->changeIcon($revenueChange), 'before')
                ->descriptionColor($this->changeColor($revenueChange))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('info')
                ->valueColor('success')
                ->chart($orderMetrics['revenueSparkline'])
                ->chartColor('success'),
            Stat::make(__('Orders'), Number::format($orderMetrics['orders']))
                ->description($this->formatChangeDescription($ordersChange))
                ->descriptionIcon($this->changeIcon($ordersChange), 'before')
                ->descriptionColor($this->changeColor($ordersChange))
                ->icon('heroicon-o-shopping-cart')
                ->iconBackgroundColor('primary')
                ->valueColor('primary'),
            Stat::make(__('Average order value'), Number::currency($orderMetrics['aov'], 'EUR'))
                ->description(__('Basket size'))
                ->icon('heroicon-o-calculator')
                ->iconBackgroundColor('warning')
                ->valueColor('warning'),
            Stat::make(__('Conversion rate'), Number::format($orderMetrics['conversionRate'], 2) . ' %')
                ->description(__('Sessions → orders'))
                ->icon('heroicon-o-bolt')
                ->iconBackgroundColor('success')
                ->valueColor('success'),
            Stat::make(__('Refund rate'), Number::format($orderMetrics['refundRate'], 2) . ' %')
                ->description(__('Refunded orders'))
                ->icon('heroicon-o-arrow-uturn-left')
                ->iconBackgroundColor('danger')
                ->valueColor('danger'),
            Stat::make(__('New customers'), Number::format($orderMetrics['newCustomers']))
                ->description(__('New sign-ups: :count users', ['count' => Number::format($trafficMetrics['newUsers'])]))
                ->icon('heroicon-o-user-plus')
                ->iconBackgroundColor('secondary')
                ->valueColor('secondary'),
        ];
    }

    private function formatChangeDescription(?float $change): string
    {
        if ($change === null) {
            return __('No prior period');
        }

        if ($change === 0.0) {
            return __('No change');
        }

        return ($change > 0 ? '+' : '') . Number::format($change, 2) . '%';
    }

    private function changeIcon(?float $change): string
    {
        if ($change === null || $change === 0.0) {
            return 'heroicon-o-arrows-right-left';
        }

        return $change > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down';
    }

    private function changeColor(?float $change): string
    {
        if ($change === null || $change === 0.0) {
            return 'secondary';
        }

        return $change > 0 ? 'success' : 'danger';
    }
}
