<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use App\Filament\Resources\ActivityLogResource\Widgets\ActivityLogStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('refresh')
                ->label(__('admin.activity_logs.actions.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->action('$refresh'),
            
            Actions\Action::make('export')
                ->label(__('admin.activity_logs.actions.export'))
                ->icon('heroicon-o-arrow-down-tray')
                ->action('$dispatch', 'export'),
            
            Actions\Action::make('clear_old_logs')
                ->label(__('admin.activity_logs.actions.clear_old_logs'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('admin.activity_logs.actions.clear_old_logs_confirm'))
                ->modalDescription(__('admin.activity_logs.actions.clear_old_logs_description'))
                ->action(function () {
                    \App\Models\Activity::where('created_at', '<', now()->subDays(30))->delete();
                    \Filament\Notifications\Notification::make()
                        ->title(__('admin.activity_logs.actions.logs_cleared'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ActivityLogStatsWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.activity_logs.tabs.all'))
                ->badge(\App\Models\Activity::count()),
            
            'today' => Tab::make(__('admin.activity_logs.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(\App\Models\Activity::whereDate('created_at', today())->count()),
            
            'this_week' => Tab::make(__('admin.activity_logs.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(\App\Models\Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
            
            'this_month' => Tab::make(__('admin.activity_logs.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereMonth('created_at', now()->month))
                ->badge(\App\Models\Activity::whereMonth('created_at', now()->month)->count()),
        ];
    }
}