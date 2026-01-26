<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use BackedEnum;
use Filament\Forms\Components\Select;
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

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;

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
                            Select::make('product_id')
                                ->label(__('messages.product'))
                                ->relationship('product', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
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
