<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use App\Models\Product;
use App\Support\Search\ProductSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PriceResource extends Resource
{
    protected static ?string $model = Price::class;

    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {

        $form = $schema; // Preserve legacy variable naming for existing schema definitions.

        return $form
            ->schema([
                Section::make(__('admin.prices.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                SearchableInput::make('product_id')
                                    ->label(__('admin.prices.product'))
                                    ->placeholder('SKU / EAN / name')
                                    ->required()
                                    ->searchUsing(fn (string $search): array => ProductSearch::complex($search))
                                    ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                                    ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                        if ($state === null) {
                                            return;
                                        }

                                        $product = Product::query()
                                            ->select(['id', 'sku', 'name'])
                                            ->find($state);

                                        if (! $product instanceof Product) {
                                            return;
                                        }

                                        $component
                                            ->state((string) $state)
                                            ->options([
                                                (string) $product->getKey() => ProductSearch::label($product),
                                            ]);
                                    })
                                    ->afterStateUpdated(function (?string $state, Set $set): void {
                                        if ($state === null || $state === '') {
                                            return;
                                        }

                                        $product = Product::query()
                                            ->select(['id'])
                                            ->find((int) $state);

                                        if (! $product instanceof Product) {
                                            return;
                                        }

                                        $set('product_id', $product->getKey());
                                    }),
                                TextInput::make('amount')
                                    ->label(__('admin.prices.amount'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('admin.prices.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('admin.prices.amount'))
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('admin.prices.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'edit'   => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
