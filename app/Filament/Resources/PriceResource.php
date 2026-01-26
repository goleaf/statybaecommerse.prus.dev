<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\PriceResource\Pages;
use App\Models\Price;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
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

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;

    protected static ?int $navigationSort = 15;

    public static function getNavigationLabel(): string
    {
        return __('messages.admin_prices');
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
                            Select::make('product_id')
                                ->label(__('messages.product'))
                                ->relationship('product', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
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
