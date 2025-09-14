<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\ReviewResource\Widgets;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final /**
 * ListReviews
 * 
 * Filament resource for admin panel management.
 */
class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_dashboard')
                ->label(__('common.back_to_dashboard'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url('/admin')
                ->tooltip(__('common.back_to_dashboard_tooltip')),
            Actions\CreateAction::make()
                ->label(__('admin.reviews.actions.create_review')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\ReviewStatsWidget::class,
            Widgets\ReviewRatingDistributionWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            Widgets\RecentReviewsWidget::class,
            Widgets\ReviewApprovalWidget::class,
        ];
    }
}
