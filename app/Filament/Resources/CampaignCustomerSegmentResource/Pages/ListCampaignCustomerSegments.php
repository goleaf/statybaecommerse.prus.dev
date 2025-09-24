<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignCustomerSegmentResource\Pages;

use App\Filament\Resources\CampaignCustomerSegmentResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCampaignCustomerSegments extends ListRecords
{
    protected static string $resource = CampaignCustomerSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('campaign_customer_segments.tabs.all')),
            'demographic' => Tab::make(__('campaign_customer_segments.tabs.demographic'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('segment_type', 'demographic'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('segment_type', 'demographic')->count()),
            'behavioral' => Tab::make(__('campaign_customer_segments.tabs.behavioral'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('segment_type', 'behavioral'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('segment_type', 'behavioral')->count()),
            'geographic' => Tab::make(__('campaign_customer_segments.tabs.geographic'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('segment_type', 'geographic'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('segment_type', 'geographic')->count()),
            'psychographic' => Tab::make(__('campaign_customer_segments.tabs.psychographic'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('segment_type', 'psychographic'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('segment_type', 'psychographic')->count()),
            'active' => Tab::make(__('campaign_customer_segments.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
        ];
    }
}
