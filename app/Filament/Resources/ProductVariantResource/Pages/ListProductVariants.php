<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ProductVariantResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

final class ListProductVariants extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('product_variants.tabs.all'))
                ->icon('heroicon-o-list-bullet')
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),

            'in_stock' => WidgetTab::make(__('product_variants.tabs.in_stock'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query->where(
                        function (Builder $stockQuery): void {
                            $stockQuery
                                ->where('track_inventory', false)
                                ->orWhere('available_quantity', '>', 0);
                        }
                    )
                ),

            'low_stock' => WidgetTab::make(__('product_variants.tabs.low_stock'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->where('track_inventory', true)
                        ->whereColumn('available_quantity', '<=', 'low_stock_threshold')
                ),

            'out_of_stock' => WidgetTab::make(__('product_variants.tabs.out_of_stock'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(
                    fn (Builder $query) => $query
                        ->where('track_inventory', true)
                        ->where('available_quantity', '<=', 0)
                ),

            'size_variants' => WidgetTab::make(__('product_variants.tabs.size_variants'))
                ->icon('heroicon-o-cube')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('variant_type', 'size'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('variant_type', 'size')->count()),

            'color_variants' => WidgetTab::make(__('product_variants.tabs.color_variants'))
                ->icon('heroicon-o-swatch')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('variant_type', 'color'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('variant_type', 'color')->count()),

            'default_variants' => WidgetTab::make(__('product_variants.tabs.default_variants'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_default_variant', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_default_variant', true)->count()),
        ];
    }
}
