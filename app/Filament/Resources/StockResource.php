<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class StockResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = \App\Enums\NavigationGroup::Catalog;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.inventory');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Section::make(__('translations.inventory_entry'))
                    ->schema([
                        Forms\Components\Hidden::make('inventoriable_type')
                            ->default(Product::class),
                        Forms\Components\Select::make('inventoriable_id')
                            ->label(__('translations.product'))
                            ->options(fn() => Product::query()->orderBy('name')->pluck('name', 'id')->filter(fn($label) => filled($label))->toArray())
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('location_id')
                            ->label(__('translations.location'))
                            ->options(fn() => Location::query()->orderBy('code')->pluck('code', 'id')->filter(fn($label) => filled($label))->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('translations.quantity'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('reserved')
                            ->label(__('translations.reserved'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('incoming')
                            ->label(__('translations.incoming'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\TextInput::make('threshold')
                            ->label(__('translations.threshold'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        Forms\Components\Toggle::make('is_tracked')
                            ->label(__('translations.tracked'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Inventory::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('inventoriable_id')
                    ->label(__('translations.product'))
                    ->getStateUsing(fn(Inventory $record) => optional(Product::find($record->inventoriable_id))->name)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.code')
                    ->label(__('translations.location'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('translations.quantity'))
                    ->numeric()
                    ->badge()
                    ->color(fn(int $state, Inventory $record) => $record->isLowStock() ? 'warning' : 'success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reserved')
                    ->label(__('translations.reserved'))
                    ->numeric()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('incoming')
                    ->label(__('translations.incoming'))
                    ->numeric()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_tracked')
                    ->label(__('translations.tracked'))
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_tracked')
                    ->label(__('translations.tracked')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
