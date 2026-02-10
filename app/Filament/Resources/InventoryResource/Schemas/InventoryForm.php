<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Schemas;

use App\Models\Product;
use App\Support\Filament\Components\SearchableInput;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ProductSearch;
use App\Support\Search\SearchResult;
use App\Support\Search\SearchResultPayload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use App\Models\ProductVariant;
use Filament\Forms\Components\Select;

class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.inventory.basic_information'))
                ->description(__('admin.inventory.basic_information_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            SearchableInput::make('product_id')
                                ->label(__('messages.product'))
                                ->required()
                                ->searchable()
                                ->searchUsing(static fn (string $search): array => ProductSearch::complex($search))
                                ->dehydrateStateUsing(static fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                    SearchableInputHelper::hydrate(
                                        $component,
                                        $state,
                                        static function (int|string $value): ?SearchResult {
                                            $product = Product::query()
                                                ->select(['id', 'sku', 'name', 'price'])
                                                ->find((int) $value);

                                            if (! $product instanceof Product) {
                                                return null;
                                            }

                                            $name = $product->getAttribute('name');
                                            if (is_array($name)) {
                                                $locale = app()->getLocale();
                                                $name = $name[$locale] ?? reset($name);
                                            }

                                            $result = SearchResult::make(
                                                (string) $product->getKey(),
                                                ProductSearch::label($product),
                                            );

                                            return SearchResultPayload::normalise($result, [
                                                'product_id' => $product->getKey(),
                                                'sku'        => (string) ($product->getAttribute('sku') ?? ''),
                                                'name'       => is_string($name) ? $name : '',
                                                'price'      => is_numeric($product->getAttribute('price')) ? (float) $product->getAttribute('price') : 0.0,
                                            ]);
                                        },
                                    );
                                })
                                ->live(),
                            Select::make('product_variant_id')
                                ->label(__('messages.Variant'))
                                ->options(fn (\Filament\Schemas\Components\Utilities\Get $get) => 
                                    $get('product_id') 
                                        ? ProductVariant::where('product_id', $get('product_id'))->pluck('sku', 'id')
                                        : []
                                )
                                ->searchable()
                                ->preload()
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get) => filled($get('product_id'))),
                            Select::make('warehouse_id')
                                ->label(__('messages.warehouse'))
                                ->relationship('warehouse', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                            TextInput::make('qty')
                                ->label(__('messages.quantity'))
                                ->required()
                                ->numeric()
                                ->minValue(0),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('reserved')
                                ->label(__('admin.inventory.reserved_quantity'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                            TextInput::make('threshold')
                                ->label(__('admin.inventory.low_stock_threshold'))
                                ->numeric()
                                ->minValue(0)
                                ->default(10),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
