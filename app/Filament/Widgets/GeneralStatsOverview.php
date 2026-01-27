<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Support\Stats\OrderMetrics;
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
            'today'        => __('translations.new_customers_today'),
            'week'         => __('translations.added_this_week'),
            'month'        => __('translations.new_customers_this_month'),
            'quarter'      => __('This quarter'),
            'year'         => __('translations.date_this_year'),
            'ytd'          => __('Year to date'),
            'last_30_days' => __('translations.last_7_days'),
        ];
    }

    protected function getStats(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);

        $orderMetrics = OrderMetrics::forRange($from, $to);

        $revenueChange = $orderMetrics['revenueChange'];
        $ordersChange = $orderMetrics['ordersChange'];

        return [
            Stat::make(__('translations.total_revenue'), Number::currency($orderMetrics['revenue'], 'EUR'))
                ->description($this->formatChangeDescription($revenueChange))
                ->descriptionIcon($this->changeIcon($revenueChange), 'before')
                ->descriptionColor($this->changeColor($revenueChange))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('info')
                ->valueColor('success')
                ->chart($orderMetrics['revenueSparkline'])
                ->chartColor('success'),
            Stat::make(__('translations.total_orders'), Number::format($orderMetrics['orders']))
                ->description($this->formatChangeDescription($ordersChange))
                ->descriptionIcon($this->changeIcon($ordersChange), 'before')
                ->descriptionColor($this->changeColor($ordersChange))
                ->icon('heroicon-o-shopping-cart')
                ->iconBackgroundColor('primary')
                ->valueColor('primary'),
            Stat::make(__('translations.average_order_value'), Number::currency($orderMetrics['aov'], 'EUR'))
                ->description(__('translations.per_order'))
                ->icon('heroicon-o-calculator')
                ->iconBackgroundColor('warning')
                ->valueColor('warning'),
            Stat::make(__('translations.conversions'), Number::format($orderMetrics['conversionRate'], 2) . ' %')
                ->description(__('translations.customer_satisfaction'))
                ->icon('heroicon-o-bolt')
                ->iconBackgroundColor('success')
                ->valueColor('success'),
            Stat::make(__('Refund rate'), Number::format($orderMetrics['refundRate'], 2) . ' %')
                ->description(__('Refunded orders'))
                ->icon('heroicon-o-arrow-uturn-left')
                ->iconBackgroundColor('danger')
                ->valueColor('danger'),
            Stat::make(__('translations.new_users'), Number::format($orderMetrics['newCustomers']))
                ->description(__('translations.new_customers_this_month'))
                ->icon('heroicon-o-user-plus')
                ->iconBackgroundColor('secondary')
                ->valueColor('secondary'),
        ];
    }

    private function formatChangeDescription(?float $change): string
    {
        if ($change === null) {
            return __('translations.no');
        }

        if ($change === 0.0) {
            return __('translations.no');
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
