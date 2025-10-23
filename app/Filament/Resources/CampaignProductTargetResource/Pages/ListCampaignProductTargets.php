<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignProductTargetResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CampaignProductTargetResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Illuminate\Database\Eloquent\Builder;

class ListCampaignProductTargets extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = CampaignProductTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('campaign_product_targets.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'product' => WidgetTab::make(__('campaign_product_targets.tabs.product'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'product'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('target_type', 'product')->count()),
            'category' => WidgetTab::make(__('campaign_product_targets.tabs.category'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'category'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('target_type', 'category')->count()),
            'brand' => WidgetTab::make(__('campaign_product_targets.tabs.brand'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'brand'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('target_type', 'brand')->count()),
            'collection' => WidgetTab::make(__('campaign_product_targets.tabs.collection'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('target_type', 'collection'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('target_type', 'collection')->count()),
            'active' => WidgetTab::make(__('campaign_product_targets.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
        ];
    }
}
