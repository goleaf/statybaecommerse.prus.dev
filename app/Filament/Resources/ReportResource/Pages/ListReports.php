<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use App\Filament\Resources\ReportResource\Widgets;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_dashboard')
                ->label(__('common.back_to_dashboard'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url('/admin')
                ->tooltip(__('common.back_to_dashboard_tooltip')),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\ReportStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            Widgets\ReportTypesWidget::class,
            Widgets\RecentReportsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.reports.tabs.all'))
                ->icon('heroicon-o-document-chart-bar'),
            'active' => Tab::make(__('admin.reports.tabs.active'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),
            'public' => Tab::make(__('admin.reports.tabs.public'))
                ->icon('heroicon-o-globe-alt')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true)),
            'scheduled' => Tab::make(__('admin.reports.tabs.scheduled'))
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_scheduled', true)),
            'popular' => Tab::make(__('admin.reports.tabs.popular'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('view_count', 'desc')),
            'recent' => Tab::make(__('admin.reports.tabs.recent'))
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->latest()),
        ];
    }
}
