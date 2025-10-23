<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

/**
 * ProductStatsWidget
 *
 * Comprehensive statistics widget for products showing key metrics and insights
 */
final class ProductStatsWidget extends BaseWidget
{
    use InteractsWithDateFilter;

    protected static ?string $heading = 'Product insights';

    protected static ?string $badge = 'Catalogue';

    protected static ?string $badgeColor = 'primary';

    public ?string $filter = 'last_30_days';

    protected function getFilters(): ?array
    {
        return [
            'last_30_days' => __('Last 30 days'),
            'month'        => __('Last month'),
            'quarter'      => __('This quarter'),
            'year'         => __('This year'),
        ];
    }

    protected function getStats(): array
    {
        /** @var array{
         *     total_products: int,
         *     active_products: int,
         *     featured_products: int,
         *     low_stock_products: int,
         *     avg_rating: float,
         *     total_reviews: int,
         *     total_revenue: float
         * } $metrics
         */
        $metrics = Cache::remember(
            'filament:widgets:product-stats',
            now()->addMinutes(10),
            static function (): array {
                return [
                    'total_products' => (int) Product::count(),
                    'active_products' => (int) Product::where('is_visible', true)->count(),
                    'featured_products' => (int) Product::where('is_featured', true)->count(),
                    'low_stock_products' => (int) Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
                    'avg_rating' => (float) (Product::avg('average_rating') ?? 0),
                    'total_reviews' => (int) (Product::sum('reviews_count') ?? 0),
                    'total_revenue' => (float) (Product::sum('revenue') ?? 0),
                ];
            }
        );

        return [
            Stat::make(__('products.widgets.total_products'), (int) $metrics['total_products'])
                ->description(__('products.widgets.total_products_description'))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),
            Stat::make(__('products.widgets.active_products'), (int) $metrics['active_products'])
                ->description(__('products.widgets.active_products_description'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),
            Stat::make(__('products.widgets.featured_products'), (int) $metrics['featured_products'])
                ->description(__('products.widgets.featured_products_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            Stat::make(__('products.widgets.low_stock_products'), (int) $metrics['low_stock_products'])
                ->description(__('products.widgets.low_stock_products_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($metrics['low_stock_products'] > 0 ? 'danger' : 'success'),
            Stat::make(__('products.widgets.average_rating'), number_format((float) $metrics['avg_rating'], 2))
                ->description(__('products.widgets.average_rating_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('info'),
            Stat::make(__('products.widgets.total_reviews'), number_format((float) $metrics['total_reviews']))
                ->description(__('products.widgets.total_reviews_description'))
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color('info'),
            Stat::make(__('products.widgets.total_revenue'), '€'.number_format((float) $metrics['total_revenue'], 2))
                ->description(__('products.widgets.total_revenue_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
        ];
    }
}
