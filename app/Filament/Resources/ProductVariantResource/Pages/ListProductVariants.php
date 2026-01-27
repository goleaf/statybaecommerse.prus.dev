<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Resources\ProductVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
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
        $availableExpression = 'COALESCE(available_quantity, stock_quantity - COALESCE(reserved_quantity, 0))';

        return [
            'in_stock' => Tab::make()
                ->modifyQuery(fn (Builder $query): Builder => $query
                    ->where(function (Builder $builder) use ($availableExpression): void {
                        $builder
                            ->where('track_inventory', false)
                            ->orWhereRaw("{$availableExpression} > 0");
                    })),
            'low_stock' => Tab::make()
                ->modifyQuery(fn (Builder $query): Builder => $query
                    ->where('track_inventory', true)
                    ->whereRaw("{$availableExpression} <= COALESCE(low_stock_threshold, 0)")),
            'out_of_stock' => Tab::make()
                ->modifyQuery(fn (Builder $query): Builder => $query
                    ->where('track_inventory', true)
                    ->whereRaw("{$availableExpression} <= 0")),
        ];
    }
}
