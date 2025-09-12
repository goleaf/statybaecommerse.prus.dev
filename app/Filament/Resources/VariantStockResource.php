<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantStockResource\Pages;
use App\Models\Location;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use BackedEnum;
use UnitEnum;

final class VariantStockResource extends Resource
{
    protected static ?string $model = VariantInventory::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-squares-2x2';


    protected static ?int $navigationSort = 3;

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
                Section::make(__('translations.variant_inventory_entry'))
                    ->schema([
                        Forms\Components\Select::make('variant_id')
                            ->label(__('translations.variant'))
                            ->options(fn() => ProductVariant::query()->orderBy('sku')->pluck('sku', 'id')->filter(fn($label) => filled($label))->toArray())
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('location_id')
                            ->label(__('translations.location'))
                            ->options(fn() => Location::query()->orderBy('code')->pluck('code', 'id')->filter(fn($label) => filled($label))->toArray())
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('stock')
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
                VariantInventory::query()
            )
            ->columns([
                Tables\Columns\TextColumn::make('variant.sku')
                    ->label(__('translations.variant'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.code')
                    ->label(__('translations.location'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label(__('translations.quantity'))
                    ->numeric()
                    ->badge()
                    ->color(fn(int $state, VariantInventory $record) => $record->isLowStock() ? 'warning' : 'success')
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
            'index' => Pages\ListVariantStocks::route('/'),
            'create' => Pages\CreateVariantStock::route('/create'),
            'edit' => Pages\EditVariantStock::route('/{record}/edit'),
        ];
    }
}
