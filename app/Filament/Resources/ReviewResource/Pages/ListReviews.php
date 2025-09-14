<?php

declare (strict_types=1);
namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\ReviewResource\Widgets;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
/**
 * ListReviews
 * 
 * Filament v4 resource for ListReviews management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_dashboard')->label(__('common.back_to_dashboard'))->icon('heroicon-o-arrow-left')->color('gray')->url('/admin')->tooltip(__('common.back_to_dashboard_tooltip')), Actions\CreateAction::make()->label(__('admin.reviews.actions.create_review'))];
    }
    /**
     * Handle getHeaderWidgets functionality with proper error handling.
     * @return array
     */
    protected function getHeaderWidgets(): array
    {
        return [Widgets\ReviewStatsWidget::class, Widgets\ReviewRatingDistributionWidget::class];
    }
    /**
     * Handle getFooterWidgets functionality with proper error handling.
     * @return array
     */
    protected function getFooterWidgets(): array
    {
        return [Widgets\RecentReviewsWidget::class, Widgets\ReviewApprovalWidget::class];
    }
}