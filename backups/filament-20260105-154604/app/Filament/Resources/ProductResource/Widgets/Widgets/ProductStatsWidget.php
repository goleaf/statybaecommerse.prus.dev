<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Models\Product;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;

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
        [$from, $to] = $this->getDateRange($this->filter);

        /** @var array{
         *     total_products: int,
         *     active_products: int,
         *     featured_products: int,
         *     low_stock_products: int,
         *     avg_rating: float,
         *     total_reviews: int,
         *     total_revenue: float,
         *     new_products: int
         * } $metrics
         */
        $metrics = Cache::remember(
            sprintf('filament:widgets:product-stats:%s:%s:%s', $this->filter, $from->format('Ymd'), $to->format('Ymd')),
            now()->addMinutes(10),
            static function () use ($from, $to): array {
                return [
                    'total_products'     => (int) Product::count(),
                    'active_products'    => (int) Product::where('is_visible', true)->count(),
                    'featured_products'  => (int) Product::where('is_featured', true)->count(),
                    'low_stock_products' => (int) Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
                    'avg_rating'         => (float) (Product::avg('average_rating') ?? 0),
                    'total_reviews'      => (int) (Product::sum('reviews_count') ?? 0),
                    'total_revenue'      => (float) (Product::sum('revenue') ?? 0),
                    'new_products'       => (int) Product::whereBetween('created_at', [$from, $to])->count(),
                ];
            }
        );

        return [
            Stat::make(__('products.widgets.total_products'), Number::format($metrics['total_products']))
                ->icon('heroicon-o-cube')
                ->iconBackgroundColor('primary')
                ->description(__('products.widgets.total_products_description')),
            Stat::make(__('products.widgets.active_products'), Number::format($metrics['active_products']))
                ->icon('heroicon-o-eye')
                ->iconBackgroundColor('success')
                ->description(__('products.widgets.active_products_description')),
            Stat::make(__('products.widgets.featured_products'), Number::format($metrics['featured_products']))
                ->icon('heroicon-o-star')
                ->iconBackgroundColor('warning')
                ->description(__('products.widgets.featured_products_description')),
            Stat::make(__('products.widgets.low_stock_products'), Number::format($metrics['low_stock_products']))
                ->icon('heroicon-o-exclamation-triangle')
                ->iconBackgroundColor($metrics['low_stock_products'] > 0 ? 'danger' : 'success')
                ->description(__('products.widgets.low_stock_products_description')),
            Stat::make(__('products.widgets.average_rating'), Number::format($metrics['avg_rating'], 2))
                ->icon('heroicon-o-star')
                ->iconBackgroundColor('info')
                ->description(__('products.widgets.average_rating_description')),
            Stat::make(__('products.widgets.total_reviews'), Number::format($metrics['total_reviews']))
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->iconBackgroundColor('secondary')
                ->description(__('products.widgets.total_reviews_description')),
            Stat::make(__('products.widgets.total_revenue'), Number::currency($metrics['total_revenue'], 'EUR'))
                ->icon('heroicon-o-banknotes')
                ->iconBackgroundColor('success')
                ->description(__('products.widgets.total_revenue_description')),
            Stat::make(__('New products'), Number::format($metrics['new_products']))
                ->icon('heroicon-o-sparkles')
                ->iconBackgroundColor('info')
                ->description(__('Created during the selected period')),
        ];
    }
}
