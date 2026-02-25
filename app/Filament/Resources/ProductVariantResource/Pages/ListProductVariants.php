<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProductVariants extends ListRecords
{
    protected static string $resource = ProductVariantResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all'      => Tab::make(__('common.all')),
            'in_stock' => Tab::make(__('admin.product_variants.in_stock'))
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query->where(static function (Builder $stockQuery): Builder {
                    return $stockQuery
                        ->where('track_inventory', false)
                        ->orWhere('available_quantity', '>', 0);
                })),
            'low_stock' => Tab::make(__('admin.product_variants.low_stock'))
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query
                    ->where('track_inventory', true)
                    ->whereColumn('available_quantity', '<=', 'low_stock_threshold')),
            'out_of_stock' => Tab::make(__('admin.product_variants.out_of_stock'))
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query
                    ->where('track_inventory', true)
                    ->where('available_quantity', '<=', 0)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
