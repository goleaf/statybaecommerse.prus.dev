<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Resources\RecommendationBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
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
        $resource = $this->getResource();

        return [
            'all' => Tab::make(__('recommendation_blocks.tabs.all'))
                ->badge(fn () => $resource::getEloquentQuery()->count()),

            'active' => Tab::make(__('recommendation_blocks.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $resource::getEloquentQuery()->where('is_active', true)->count()),

            'featured' => Tab::make(__('recommendation_blocks.tabs.featured'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'featured'))
                ->badge(fn () => $resource::getEloquentQuery()->where('type', 'featured')->count()),

            'related' => Tab::make(__('recommendation_blocks.tabs.related'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'related'))
                ->badge(fn () => $resource::getEloquentQuery()->where('type', 'related')->count()),

            'similar' => Tab::make(__('recommendation_blocks.tabs.similar'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'similar'))
                ->badge(fn () => $resource::getEloquentQuery()->where('type', 'similar')->count()),

            'trending' => Tab::make(__('recommendation_blocks.tabs.trending'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'trending'))
                ->badge(fn () => $resource::getEloquentQuery()->where('type', 'trending')->count()),

            'recent' => Tab::make(__('recommendation_blocks.tabs.recent'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'recent'))
                ->badge(fn () => $resource::getEloquentQuery()->where('type', 'recent')->count()),
        ];
    }
}
