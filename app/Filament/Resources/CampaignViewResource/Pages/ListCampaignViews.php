<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignViewResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CampaignViewResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Illuminate\Database\Eloquent\Builder;

class ListCampaignViews extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = CampaignViewResource::class;

    public function getWidgetTabs(): array
    {
        return [
            'all'   => Tab::make(__('campaign_views.tabs.all')),
            'today' => Tab::make(__('campaign_views.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('viewed_at', today()))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereDate('viewed_at', today())->count()),
            'this_week' => Tab::make(__('campaign_views.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
            'this_month' => Tab::make(__('campaign_views.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereMonth('viewed_at', now()->month)->whereYear('viewed_at', now()->year))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereMonth('viewed_at', now()->month)->whereYear('viewed_at', now()->year)->count()),
            'registered_users' => Tab::make(__('campaign_views.tabs.registered_users'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('customer_id'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereNotNull('customer_id')->count()),
            'guests' => Tab::make(__('campaign_views.tabs.guests'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('customer_id'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereNull('customer_id')->count()),
        ];
    }
}
