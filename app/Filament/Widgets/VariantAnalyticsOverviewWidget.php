<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\VariantAnalytics;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

final class VariantAnalyticsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $lastWeek = now()->subWeek()->toDateString();
        $lastMonth = now()->subMonth()->toDateString();

        return [
            Stat::make(__('filament::variant_analytics.total_views'), $this->getTotalViews())
                ->description(__('filament::variant_analytics.total_views_description'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('primary'),

            Stat::make(__('filament::variant_analytics.total_clicks'), $this->getTotalClicks())
                ->description(__('filament::variant_analytics.total_clicks_description'))
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('success'),

            Stat::make(__('filament::variant_analytics.total_purchases'), $this->getTotalPurchases())
                ->description(__('filament::variant_analytics.total_purchases_description'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('warning'),

            Stat::make(__('filament::variant_analytics.total_revenue'), $this->getTotalRevenue())
                ->description(__('filament::variant_analytics.total_revenue_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),

            Stat::make(__('filament::variant_analytics.avg_conversion_rate'), $this->getAverageConversionRate())
                ->description(__('filament::variant_analytics.avg_conversion_rate_description'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('danger'),

            Stat::make(__('filament::variant_analytics.top_performing_variants'), $this->getTopPerformingVariantsCount())
                ->description(__('filament::variant_analytics.top_performing_variants_description'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),

            Stat::make(__('filament::variant_analytics.today_views'), $this->getTodayViews())
                ->description($this->getViewsChangeDescription())
                ->descriptionIcon($this->getViewsChangeIcon())
                ->color($this->getViewsChangeColor()),

            Stat::make(__('filament::variant_analytics.this_week_revenue'), $this->getThisWeekRevenue())
                ->description($this->getRevenueChangeDescription())
                ->descriptionIcon($this->getRevenueChangeIcon())
                ->color($this->getRevenueChangeColor()),
        ];
    }

    private function getTotalViews(): string
    {
        $total = VariantAnalytics::sum('views');
        return number_format($total);
    }

    private function getTotalClicks(): string
    {
        $total = VariantAnalytics::sum('clicks');
        return number_format($total);
    }

    private function getTotalPurchases(): string
    {
        $total = VariantAnalytics::sum('purchases');
        return number_format($total);
    }

    private function getTotalRevenue(): string
    {
        $total = VariantAnalytics::sum('revenue');
        return '€' . number_format($total, 2);
    }

    private function getAverageConversionRate(): string
    {
        $avg = VariantAnalytics::avg('conversion_rate');
        return number_format($avg ?? 0, 2) . '%';
    }

    private function getTopPerformingVariantsCount(): string
    {
        $count = VariantAnalytics::where('conversion_rate', '>=', 5.0)->count();
        return number_format($count);
    }

    private function getTodayViews(): string
    {
        $today = VariantAnalytics::whereDate('date', now())->sum('views');
        return number_format($today);
    }

    private function getViewsChangeDescription(): string
    {
        $today = VariantAnalytics::whereDate('date', now())->sum('views');
        $yesterday = VariantAnalytics::whereDate('date', now()->subDay())->sum('views');
        
        if ($yesterday == 0) {
            return __('filament::variant_analytics.no_previous_data');
        }
        
        $change = (($today - $yesterday) / $yesterday) * 100;
        $direction = $change >= 0 ? 'up' : 'down';
        
        return __('filament::variant_analytics.change_from_yesterday', [
            'direction' => $direction,
            'percentage' => abs(round($change, 1))
        ]);
    }

    private function getViewsChangeIcon(): string
    {
        $today = VariantAnalytics::whereDate('date', now())->sum('views');
        $yesterday = VariantAnalytics::whereDate('date', now()->subDay())->sum('views');
        
        if ($today >= $yesterday) {
            return 'heroicon-m-arrow-trending-up';
        }
        
        return 'heroicon-m-arrow-trending-down';
    }

    private function getViewsChangeColor(): string
    {
        $today = VariantAnalytics::whereDate('date', now())->sum('views');
        $yesterday = VariantAnalytics::whereDate('date', now()->subDay())->sum('views');
        
        if ($today >= $yesterday) {
            return 'success';
        }
        
        return 'danger';
    }

    private function getThisWeekRevenue(): string
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        
        $revenue = VariantAnalytics::whereBetween('date', [$weekStart, $weekEnd])->sum('revenue');
        return '€' . number_format($revenue, 2);
    }

    private function getRevenueChangeDescription(): string
    {
        $thisWeek = VariantAnalytics::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('revenue');
        $lastWeek = VariantAnalytics::whereBetween('date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->sum('revenue');
        
        if ($lastWeek == 0) {
            return __('filament::variant_analytics.no_previous_data');
        }
        
        $change = (($thisWeek - $lastWeek) / $lastWeek) * 100;
        $direction = $change >= 0 ? 'up' : 'down';
        
        return __('filament::variant_analytics.change_from_last_week', [
            'direction' => $direction,
            'percentage' => abs(round($change, 1))
        ]);
    }

    private function getRevenueChangeIcon(): string
    {
        $thisWeek = VariantAnalytics::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('revenue');
        $lastWeek = VariantAnalytics::whereBetween('date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->sum('revenue');
        
        if ($thisWeek >= $lastWeek) {
            return 'heroicon-m-arrow-trending-up';
        }
        
        return 'heroicon-m-arrow-trending-down';
    }

    private function getRevenueChangeColor(): string
    {
        $thisWeek = VariantAnalytics::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->sum('revenue');
        $lastWeek = VariantAnalytics::whereBetween('date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->sum('revenue');
        
        if ($thisWeek >= $lastWeek) {
            return 'success';
        }
        
        return 'danger';
    }
}
