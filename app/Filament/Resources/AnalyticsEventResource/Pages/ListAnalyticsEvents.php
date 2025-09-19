<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsEventResource\Pages;

use App\Filament\Resources\AnalyticsEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListAnalyticsEvents extends ListRecords
{
    protected static string $resource = AnalyticsEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('analytics_events.tabs.all')),
            
            'page_views' => Tab::make(__('analytics_events.tabs.page_views'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'page_view'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('event_type', 'page_view')->count()),
            
            'clicks' => Tab::make(__('analytics_events.tabs.clicks'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'click'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('event_type', 'click')->count()),
            
            'purchases' => Tab::make(__('analytics_events.tabs.purchases'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'purchase'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('event_type', 'purchase')->count()),
            
            'signups' => Tab::make(__('analytics_events.tabs.signups'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'signup'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('event_type', 'signup')->count()),
            
            'today' => Tab::make(__('analytics_events.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereDate('created_at', today())->count()),
            
            'this_week' => Tab::make(__('analytics_events.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
            
            'this_month' => Tab::make(__('analytics_events.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()),
        ];
    }
}
