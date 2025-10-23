<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\RecommendationBlockResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use App\Support\Recommendations\RecommendationBlockOptions;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListRecommendationBlocks extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = RecommendationBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        $resource = $this->getResource();

        return [
            'all' => Tab::make(__('recommendation_blocks.tabs.all')),

        $tabs = [
            'all' => WidgetTab::make(__('recommendation_blocks.tabs.all'))
                // Cloning avoids leaking the additional constraints onto subsequent tabs.
                ->value(fn () => (clone $baseQuery)->count()),
            'active' => WidgetTab::make(__('recommendation_blocks.tabs.active'))
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

        foreach (RecommendationBlockOptions::tabLabels() as $type => $label) {
            $tabs[$type] = WidgetTab::make($label)
                // Keep the tab query aligned with the available type filter and selects.
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', $type))
                ->value(fn () => (clone $baseQuery)->where('type', $type)->count());
        }

        return $tabs;
    }
}
