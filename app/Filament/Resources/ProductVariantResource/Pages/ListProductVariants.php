<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListProductVariants extends ListRecords
{
    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('product_variants.tabs.all'))
                ->icon('heroicon-o-list-bullet'),

            'in_stock' => Tab::make(__('product_variants.tabs.in_stock'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('track_inventory', false)->orWhere('quantity', '>', 0)),

            'low_stock' => Tab::make(__('product_variants.tabs.low_stock'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('track_inventory', true)->whereRaw('quantity <= low_stock_threshold')),

            'out_of_stock' => Tab::make(__('product_variants.tabs.out_of_stock'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('track_inventory', true)->where('quantity', '<=', 0)),

            'size_variants' => Tab::make(__('product_variants.tabs.size_variants'))
                ->icon('heroicon-o-cube')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('variant_type', 'size')),

            'color_variants' => Tab::make(__('product_variants.tabs.color_variants'))
                ->icon('heroicon-o-swatch')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('variant_type', 'color')),

            'default_variants' => Tab::make(__('product_variants.tabs.default_variants'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default_variant', true)),
        ];
    }
}