<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use App\Models\Product;
use App\Support\Filament\Components\SearchableInput;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ProductSearch;
use App\Support\Search\SearchResult;
use App\Support\Search\SearchResultPayload;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class PriceResource extends BaseResource
{
    protected static ?string $model = Price::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-euro';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 15;

    public static function getNavigationLabel(): string
    {
        return __('admin.prices.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.prices.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.prices.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.prices.basic_information'))
                ->description(__('admin.prices.basic_information_description'))
                ->schema([
                    SchemaGrid::make(2)
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
                                        static function (int $value): ?SearchResult {
                                            $product = Product::query()
                                                ->select(['id', 'sku', 'name', 'price'])
                                                ->find($value);

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
                                }),
                            TextInput::make('price')
                                ->label(__('messages.price'))
                                ->required()
                                ->numeric()
                                ->minValue(0)
                                ->step(0.01),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            DateTimePicker::make('valid_from')
                                ->label(__('admin.prices.valid_from'))
                                ->default(now()),
                            DateTimePicker::make('valid_until')
                                ->label(__('admin.prices.valid_until')),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label(__('messages.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('valid_from')
                    ->label(__('admin.prices.valid_from'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->label(__('admin.prices.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('admin.prices.no_expiry')),
                TextColumn::make('created_at')
                    ->label(__('admin.prices.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('valid_from', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'view'   => Pages\ViewPrice::route('/{record}'),
            'edit'   => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
