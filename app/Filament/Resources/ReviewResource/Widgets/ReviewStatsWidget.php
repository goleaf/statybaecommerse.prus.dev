<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReviewResource\Widgets;

use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ReviewStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalReviews = Review::count();
        $approvedReviews = Review::where('is_approved', true)->count();
        $pendingReviews = Review::where('is_approved', false)->whereNull('rejected_at')->count();
        $featuredReviews = Review::where('is_featured', true)->count();
        $averageRating = Review::where('is_approved', true)->avg('rating') ?? 0;

        return [
            Stat::make(__('admin.reviews.stats.total_reviews'), $totalReviews)
                ->description(__('admin.reviews.stats.total_reviews_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),

            Stat::make(__('admin.reviews.stats.approved_reviews'), $approvedReviews)
                ->description(__('admin.reviews.stats.approved_reviews_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.reviews.stats.pending_reviews'), $pendingReviews)
                ->description(__('admin.reviews.stats.pending_reviews_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('admin.reviews.stats.featured_reviews'), $featuredReviews)
                ->description(__('admin.reviews.stats.featured_reviews_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('info'),

            Stat::make(__('admin.reviews.stats.average_rating'), number_format($averageRating, 1))
                ->description(__('admin.reviews.stats.average_rating_description'))
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
        ];
    }
}
