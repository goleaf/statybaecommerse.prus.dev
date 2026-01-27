<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use App\Models\Product;
use App\Support\Filament\Components\SearchableInput;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\ProductSearch;
use App\Support\Search\SearchResult;
use App\Support\Search\SearchResultPayload;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class InventoryResource extends BaseResource
{
    protected static ?string $model = Inventory::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('admin.inventory.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.inventory.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.inventory.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.inventory.basic_information'))
                ->description(__('admin.inventory.basic_information_description'))
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
                            TextInput::make('quantity')
                                ->label(__('messages.quantity'))
                                ->required()
                                ->numeric()
                                ->minValue(0),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('reserved_quantity')
                                ->label(__('admin.inventory.reserved_quantity'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                            TextInput::make('low_stock_threshold')
                                ->label(__('admin.inventory.low_stock_threshold'))
                                ->numeric()
                                ->minValue(0)
                                ->default(10),
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
                TextColumn::make('quantity')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reserved_quantity')
                    ->label(__('admin.inventory.reserved_quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label(__('admin.inventory.available_quantity'))
                    ->getStateUsing(fn ($record) => $record->quantity - $record->reserved_quantity)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('low_stock_threshold')
                    ->label(__('admin.inventory.low_stock_threshold'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.inventory.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventory::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'view'   => Pages\ViewInventory::route('/{record}'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
