<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationBlockResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\RecommendationBlockResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use App\Support\Recommendations\RecommendationBlockOptions;
use Filament\Actions;
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
        $baseQuery = $this->getResource()::getEloquentQuery();

        $tabs = [
            'all' => WidgetTab::make(__('recommendation_blocks.tabs.all'))
                // Cloning avoids leaking the additional constraints onto subsequent tabs.
                ->value(fn () => (clone $baseQuery)->count()),
            'active' => WidgetTab::make(__('recommendation_blocks.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => (clone $baseQuery)->where('is_active', true)->count()),
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
