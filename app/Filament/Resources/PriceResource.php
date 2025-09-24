<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceResource\Pages;
use App\Models\Currency;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * PriceResource
 *
 * Filament v4 resource for Price management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class PriceResource extends Resource
{
    protected static UnitEnum|string|null $navigationGroup = 'Products';

    protected static ?string $model = Price::class;

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'priceable_type';

    public static function getNavigationLabel(): string
    {
        return __('prices.title');
    }

    // getNavigationGroup inherited from static property typing

    public static function getPluralModelLabel(): string
    {
        return __('prices.plural');
    }

    public static function getModelLabel(): string
    {
        return __('prices.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('prices.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('priceable_type')
                                ->label(__('prices.priceable_type'))
                                ->options([
                                    'product' => __('prices.types.product'),
                                    'product_variant' => __('prices.types.product_variant'),
                                ])
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                    $set('priceable_id', null);
                                }),
                            Select::make('priceable_id')
                                ->label(__('prices.priceable_item'))
                                ->options(function (Forms\Get $get) {
                                    $type = $get('priceable_type');
                                    if ($type === 'product') {
                                        return Product::pluck('name', 'id');
                                    } elseif ($type === 'product_variant') {
                                        return ProductVariant::pluck('name', 'id');
                                    }

                                    return [];
                                })
                                ->searchable()
                                ->preload()
                                ->live(),
                        ]),
                    TextInput::make('price')
                        ->label(__('prices.price'))
                        ->numeric()
                        ->prefix('€')
                        ->step(0.01)
                        ->minValue(0),
                    Select::make('currency_id')
                        ->label(__('prices.currency'))
                        ->relationship('currency', 'code')
                        ->default(fn () => Currency::where('is_default', true)->first()?->id),
                ]),
            Section::make(__('prices.pricing_details'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('compare_price')
                                ->label(__('prices.compare_price'))
                                ->numeric()
                                ->prefix('€'),
                            TextInput::make('cost_price')
                                ->label(__('prices.cost_price'))
                                ->numeric()
                                ->prefix('€'),
                            TextInput::make('sale_price')
                                ->label(__('prices.sale_price'))
                                ->numeric()
                                ->prefix('€'),
                            TextInput::make('wholesale_price')
                                ->label(__('prices.wholesale_price'))
                                ->numeric()
                                ->prefix('€'),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('priceable_type')
                    ->label(__('prices.priceable_type'))
                    ->badge(),
                TextColumn::make('priceable.name')
                    ->label(__('prices.priceable_item'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('prices.is_active'))
                    ->boolean(),
                TextColumn::make('price')
                    ->label(__('prices.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label(__('prices.currency'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('currency_id')
                    ->label(__('prices.currency'))
                    ->relationship('currency', 'code'),
                TernaryFilter::make('is_active')
                    ->label(__('prices.is_active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrices::route('/'),
            'create' => Pages\CreatePrice::route('/create'),
            'edit' => Pages\EditPrice::route('/{record}/edit'),
        ];
    }
}
