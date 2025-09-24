<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Resources\RecommendationBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListRecommendationBlocks extends ListRecords
{
    protected static string $resource = RecommendationBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('recommendation_blocks.tabs.all')),

            'active' => Tab::make(__('recommendation_blocks.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),

            'featured' => Tab::make(__('recommendation_blocks.tabs.featured'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_featured', true)->count()),

            'product' => Tab::make(__('recommendation_blocks.tabs.product'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'product')->count()),

            'category' => Tab::make(__('recommendation_blocks.tabs.category'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'category'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'category')->count()),

            'cross_sell' => Tab::make(__('recommendation_blocks.tabs.cross_sell'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'cross_sell'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'cross_sell')->count()),

            'upsell' => Tab::make(__('recommendation_blocks.tabs.upsell'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'upsell'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'upsell')->count()),

            'trending' => Tab::make(__('recommendation_blocks.tabs.trending'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'trending'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'trending')->count()),
        ];
    }
}
