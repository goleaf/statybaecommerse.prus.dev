<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantAttributeValue;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;

final class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('ecommerce.variants'))
                ->schema([
                    TableRepeatableEntry::make('variants')
                        ->label(__('ecommerce.variants'))
                        ->translateLabel()
                        ->state(function (Product $record): array {
                            $record->loadMissing(['variants.variantAttributeValues']);

                            return $record->variants
                                ->map(fn (ProductVariant $variant): array => [
                                    'name' => $variant->display_name,
                                    'sku' => $variant->sku,
                                    'price' => $variant->price,
                                    'stock' => $variant->available_quantity ?? $variant->stock_quantity,
                                    'attributes' => $variant->variantAttributeValues
                                        ->map(fn (VariantAttributeValue $value): string => sprintf('%s: %s', $value->attribute_name, $value->display_value))
                                        ->filter()
                                        ->implode(', '),
                                ])
                                ->values()
                                ->all();
                        })
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('ecommerce.name'))
                                ->translateLabel(),
                            TextEntry::make('sku')
                                ->label(__('ecommerce.sku'))
                                ->translateLabel(),
                            TextEntry::make('price')
                                ->label(__('ecommerce.price'))
                                ->translateLabel()
                                ->money(fn () => config('shared.localization.default_currency', 'EUR'), decimalPlaces: 2),
                            TextEntry::make('stock')
                                ->label(__('ecommerce.stock'))
                                ->translateLabel()
                                ->numeric(),
                            TextEntry::make('attributes')
                                ->label(__('ecommerce.attributes'))
                                ->translateLabel(),
                        ])
                        ->striped()
                        ->showIndex(),
                ])
                ->columns(1),
        ]);
    }
}
