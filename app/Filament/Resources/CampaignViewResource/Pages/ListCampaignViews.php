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
    use HasResizableColumns;
    use HasWidgetTabs;

    protected static string $resource = CampaignViewResource::class;

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('campaign_views.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'today' => WidgetTab::make(__('campaign_views.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('viewed_at', today()))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereDate('viewed_at', today())->count()),
            'this_week' => WidgetTab::make(__('campaign_views.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereBetween('viewed_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
            'this_month' => WidgetTab::make(__('campaign_views.tabs.this_month'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereMonth('viewed_at', now()->month)->whereYear('viewed_at', now()->year))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereMonth('viewed_at', now()->month)->whereYear('viewed_at', now()->year)->count()),
            'registered_users' => WidgetTab::make(__('campaign_views.tabs.registered_users'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('customer_id'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereNotNull('customer_id')->count()),
            'guests' => WidgetTab::make(__('campaign_views.tabs.guests'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('customer_id'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereNull('customer_id')->count()),
        ];
    }
}
