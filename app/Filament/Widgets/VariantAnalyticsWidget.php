<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

final class VariantAnalyticsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalVariants = ProductVariant::count();
        $inStockVariants = ProductVariant::inStock()->count();
        $lowStockVariants = ProductVariant::lowStock()->count();
        $outOfStockVariants = ProductVariant::outOfStock()->count();

        $totalViews = (int) (ProductVariant::sum('views_count') ?? 0);
        $totalClicks = (int) (ProductVariant::sum('clicks_count') ?? 0);
        $totalConversions = (int) (ProductVariant::sum('sold_quantity') ?? 0);

        $avgConversionRate = $totalViews > 0 ? ($totalConversions / $totalViews) * 100 : 0;

        $topPerformingVariants = ProductVariant::orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        return [
            Stat::make(__('product_variants.stats.total_variants'), $totalVariants)
                ->description(__('product_variants.stats.all_variants'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make(__('product_variants.stats.in_stock'), $inStockVariants)
                ->description(__('product_variants.stats.available_variants'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('product_variants.stats.low_stock'), $lowStockVariants)
                ->description(__('product_variants.stats.need_restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            Stat::make(__('product_variants.stats.out_of_stock'), $outOfStockVariants)
                ->description(__('product_variants.stats.unavailable_variants'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make(__('product_variants.stats.total_views'), number_format($totalViews))
                ->description(__('product_variants.stats.product_page_views'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make(__('product_variants.stats.total_clicks'), number_format($totalClicks))
                ->description(__('product_variants.stats.variant_selections'))
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('info'),
            Stat::make(__('product_variants.stats.conversion_rate'), number_format($avgConversionRate, 2) . '%')
                ->description(__('product_variants.stats.views_to_sales'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($avgConversionRate > 5 ? 'success' : ($avgConversionRate > 2 ? 'warning' : 'danger')),
        ];
    }
}
