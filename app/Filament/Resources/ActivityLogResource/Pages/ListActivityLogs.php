<?php declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

final class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_dashboard')
                ->label(__('common.back_to_dashboard'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url('/admin')
                ->tooltip(__('common.back_to_dashboard_tooltip')),
            Actions\Action::make('clear_old_logs')
                ->label(__('admin.activity_logs.actions.clear_old_logs'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading(__('admin.activity_logs.actions.clear_old_logs'))
                ->modalDescription(__('admin.activity_logs.actions.clear_old_logs_description'))
                ->action(function () {
                    $deletedCount = \Spatie\Activitylog\Models\Activity::where('created_at', '<', now()->subDays(30))->delete();
                    $this->notify('success', __('admin.activity_logs.notifications.logs_cleared', ['count' => $deletedCount]));
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.activity_logs.tabs.all'))
                ->icon('heroicon-o-clipboard-document-list'),
            'today' => Tab::make(__('admin.activity_logs.tabs.today'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('created_at', today()))
                ->icon('heroicon-o-calendar-days'),
            'this_week' => Tab::make(__('admin.activity_logs.tabs.this_week'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]))
                ->icon('heroicon-o-calendar'),
            'this_month' => Tab::make(__('admin.activity_logs.tabs.this_month'))
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year))
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
