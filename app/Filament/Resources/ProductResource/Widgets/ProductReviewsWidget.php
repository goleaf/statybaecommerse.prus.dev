<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Widgets;

use App\Models\Product;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class ProductReviewsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalReviews = Review::count();
        $averageRating = Review::avg('rating');
        $productsWithReviews = Product::has('reviews')->count();
        $productsWithoutReviews = Product::doesntHave('reviews')->count();
        $recentReviews = Review::where('created_at', '>=', now()->subDays(30))->count();
        $approvedReviews = Review::where('status', 'approved')->count();
        $pendingReviews = Review::where('status', 'pending')->count();

        return [
            Stat::make(__('translations.total_reviews'), $totalReviews)
                ->description(__('translations.all_product_reviews'))
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('primary'),

            Stat::make(__('translations.average_rating'), number_format($averageRating, 1) . '/5')
                ->description(__('translations.mean_rating_across_products'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make(__('translations.products_with_reviews'), $productsWithReviews)
                ->description(__('translations.products_having_reviews'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('translations.products_without_reviews'), $productsWithoutReviews)
                ->description(__('translations.products_no_reviews'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('gray'),

            Stat::make(__('translations.recent_reviews'), $recentReviews)
                ->description(__('translations.reviews_last_30_days'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make(__('translations.approved_reviews'), $approvedReviews)
                ->description(__('translations.reviews_published'))
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make(__('translations.pending_reviews'), $pendingReviews)
                ->description(__('translations.reviews_awaiting_approval'))
                ->descriptionIcon('heroicon-m-hourglass-half')
                ->color('warning'),
        ];
    }
}
