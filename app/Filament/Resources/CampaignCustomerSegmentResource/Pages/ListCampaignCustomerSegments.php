<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignCustomerSegmentResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CampaignCustomerSegmentResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListCampaignCustomerSegments extends BaseListRecords
{
    use HasResizableColumns;
    use HasWidgetTabs;

    protected static string $resource = CampaignCustomerSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all'         => Tab::make(__('campaign_customer_segments.tabs.all')),
            'demographic' => Tab::make(__('campaign_customer_segments.tabs.demographic'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('segment_type', 'demographic'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('segment_type', 'demographic')->count()),
            'behavioral' => WidgetTab::make(__('campaign_customer_segments.tabs.behavioral'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('segment_type', 'behavioral'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('segment_type', 'behavioral')->count()),
            'geographic' => WidgetTab::make(__('campaign_customer_segments.tabs.geographic'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('segment_type', 'geographic'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('segment_type', 'geographic')->count()),
            'psychographic' => WidgetTab::make(__('campaign_customer_segments.tabs.psychographic'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('segment_type', 'psychographic'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('segment_type', 'psychographic')->count()),
            'active' => WidgetTab::make(__('campaign_customer_segments.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
        ];
    }
}
