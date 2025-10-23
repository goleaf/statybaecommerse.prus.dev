<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\RecommendationBlockResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListRecommendationBlocks extends BaseListRecords
{
    use HasResizableColumns;
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
        return [
            'all' => WidgetTab::make(__('recommendation_blocks.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'active' => WidgetTab::make(__('recommendation_blocks.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),

            'featured' => WidgetTab::make(__('recommendation_blocks.tabs.featured'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_featured', true)->count()),

            'product' => WidgetTab::make(__('recommendation_blocks.tabs.product'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'product')->count()),

            'category' => WidgetTab::make(__('recommendation_blocks.tabs.category'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'category'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'category')->count()),

            'cross_sell' => WidgetTab::make(__('recommendation_blocks.tabs.cross_sell'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'cross_sell'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'cross_sell')->count()),

            'upsell' => WidgetTab::make(__('recommendation_blocks.tabs.upsell'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'upsell'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'upsell')->count()),

            'trending' => WidgetTab::make(__('recommendation_blocks.tabs.trending'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'trending'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'trending')->count()),
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
