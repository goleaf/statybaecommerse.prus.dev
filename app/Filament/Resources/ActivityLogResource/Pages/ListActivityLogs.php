<?php declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

final class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('activity_logs.tabs.all')),
            'created' => Tab::make(__('activity_logs.tabs.created'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('event', 'created'))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->where('event', 'created')->count()),
            'updated' => Tab::make(__('activity_logs.tabs.updated'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('event', 'updated'))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->where('event', 'updated')->count()),
            'deleted' => Tab::make(__('activity_logs.tabs.deleted'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('event', 'deleted'))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->where('event', 'deleted')->count()),
            'login' => Tab::make(__('activity_logs.tabs.login'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('event', 'login'))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->where('event', 'login')->count()),
            'logout' => Tab::make(__('activity_logs.tabs.logout'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('event', 'logout'))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->where('event', 'logout')->count()),
            'today' => Tab::make(__('activity_logs.tabs.today'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->whereDate('created_at', today())->count()),
            'this_week' => Tab::make(__('activity_logs.tabs.this_week'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn() => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
        ];
    }
}
