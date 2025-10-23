<?php

declare(strict_types=1);

namespace App\Filament\Resources\AnalyticsEventResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\AnalyticsEventResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Illuminate\Database\Eloquent\Builder;

final class ListAnalyticsEvents extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = AnalyticsEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all'        => Tab::make(__('analytics_events.tabs.all')),
            'page_views' => Tab::make(__('analytics_events.tabs.page_views'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'page_view'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('event_type', 'page_view')->count()),
            'clicks' => WidgetTab::make(__('analytics_events.tabs.clicks'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'click'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('event_type', 'click')->count()),
            'purchases' => WidgetTab::make(__('analytics_events.tabs.purchases'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'purchase'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('event_type', 'purchase')->count()),
            'signups' => WidgetTab::make(__('analytics_events.tabs.signups'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event_type', 'signup'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('event_type', 'signup')->count()),
            'today' => WidgetTab::make(__('analytics_events.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereDate('created_at', today())->count()),
            'this_week' => WidgetTab::make(__('analytics_events.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
            'this_month' => WidgetTab::make(__('analytics_events.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()),
        ];
    }
}
