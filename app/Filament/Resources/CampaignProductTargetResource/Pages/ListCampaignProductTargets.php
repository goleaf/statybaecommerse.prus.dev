<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignProductTargetResource\Pages;

use App\Filament\Resources\CampaignProductTargetResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCampaignProductTargets extends ListRecords
{
    protected static string $resource = CampaignProductTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('campaign_product_targets.tabs.all')),
            'product' => Tab::make(__('campaign_product_targets.tabs.product'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'product'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('target_type', 'product')->count()),
            'category' => Tab::make(__('campaign_product_targets.tabs.category'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'category'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('target_type', 'category')->count()),
            'brand' => Tab::make(__('campaign_product_targets.tabs.brand'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'brand'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('target_type', 'brand')->count()),
            'collection' => Tab::make(__('campaign_product_targets.tabs.collection'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'collection'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('target_type', 'collection')->count()),
            'active' => Tab::make(__('campaign_product_targets.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
        ];
    }
}
