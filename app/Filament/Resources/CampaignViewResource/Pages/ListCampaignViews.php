<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignViewResource\Pages;

use App\Filament\Resources\CampaignViewResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCampaignViews extends ListRecords
{
    protected static string $resource = CampaignViewResource::class;

    public function getTabs(): array
    {
        return [
            'all'   => Tab::make(__('campaign_views.tabs.all')),
            'today' => Tab::make(__('campaign_views.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereDate('created_at', today())->count()),
            'this_week' => Tab::make(__('campaign_views.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
            'this_month' => Tab::make(__('campaign_views.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count()),
            'registered_customers' => Tab::make(__('campaign_views.tabs.registered_customers'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('customer_id'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereNotNull('customer_id')->count()),
            'guests' => Tab::make(__('campaign_views.tabs.guests'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('customer_id'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereNull('customer_id')->count()),
        ];
    }
}
