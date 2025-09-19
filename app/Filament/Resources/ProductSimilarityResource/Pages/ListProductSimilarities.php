<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarityResource\Pages;

use App\Filament\Resources\ProductSimilarityResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListProductSimilarities extends ListRecords
{
    protected static string $resource = ProductSimilarityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('product_similarities.tabs.all'))
                ->icon('heroicon-m-list-bullet'),
            'high_similarity' => Tab::make(__('product_similarities.tabs.high_similarity'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('similarity_score', '>=', 0.8))
                ->icon('heroicon-m-star'),
            'medium_similarity' => Tab::make(__('product_similarities.tabs.medium_similarity'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereBetween('similarity_score', [0.6, 0.8]))
                ->icon('heroicon-m-star'),
            'low_similarity' => Tab::make(__('product_similarities.tabs.low_similarity'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('similarity_score', '<', 0.6))
                ->icon('heroicon-m-star'),
            'recent' => Tab::make(__('product_similarities.tabs.recent'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('calculated_at', '>=', now()->subDays(7)))
                ->icon('heroicon-m-clock'),
        ];
    }
}
