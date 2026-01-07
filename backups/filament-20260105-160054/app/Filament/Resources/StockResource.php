<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Quantity;
use App\Filament\Resources\StockResource\Pages;
use App\Models\Inventory;
use App\Models\Product;
use App\Support\Concerns\HasNav;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * StockResource
 *
 * Filament v4 resource for Stock management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class StockResource extends Resource
{
    use HasNav;

    protected static ?string $model = Inventory::class;

    /**
     * Keeps the navigation group compatible with Filament's enum-based sidebar metadata.
     */
    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'product_name';

    public static function getNavigationLabel(): string
    {
        return __('inventory.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inventory.plural');
    }

    public static function getModelLabel(): string
    {
        return __('inventory.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            SchemaSection::make(__('inventory.product_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('inventory.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set): void {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('product_name', $product->name);
                                        }
                                    }
                                }),
                            Select::make('location_id')
                                ->label(__('inventory.location'))
                                ->relationship('location', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                    TextInput::make('product_name')
                        ->label(__('inventory.product_name'))
                        ->maxLength(255)
                        ->dehydrated(false)
                        ->disabled(),
                ]),
            SchemaSection::make(__('inventory.stock_information'))
                ->schema([
                    SchemaGrid::make(3)
                        ->schema([
                            Quantity::make('quantity')
                                ->label(__('inventory.quantity'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0)
                                ->required(),
                            Quantity::make('reserved')
                                ->label(__('inventory.reserved'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0),
                            Quantity::make('incoming')
                                ->label(__('inventory.incoming'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            Quantity::make('threshold')
                                ->label(__('inventory.threshold'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0),
                            Toggle::make('is_tracked')
                                ->label(__('inventory.is_tracked'))
                                ->default(true),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Configure the table definition for the streamlined Filament v4 return type.
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('inventory.product'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('location.name')
                    ->label(__('inventory.location'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('quantity')
                    ->label(__('inventory.quantity'))
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state, $record) => $record->isLowStock() ? 'danger' : 'success'),
                TextColumn::make('reserved')
                    ->label(__('inventory.reserved'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('incoming')
                    ->label(__('inventory.incoming'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label(__('inventory.available'))
                    ->getStateUsing(fn ($record) => $record->available_quantity)
                    ->numeric()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('threshold')
                    ->label(__('inventory.threshold'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_tracked')
                    ->label(__('inventory.tracked'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('inventory.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('location')
                    ->relationship('location', 'name')
                    ->preload(),
                SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_tracked')
                    ->label(__('inventory.tracked_only'))
                    ->native(false),
                Filter::make('low_stock')
                    ->label(__('inventory.low_stock'))
                    ->form([
                        Toggle::make('is_low_stock')
                            ->label(__('inventory.low_stock_only')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! ($data['is_low_stock'] ?? false)) {
                            return $query;
                        }

                        return $query->whereColumn('quantity', '<=', 'threshold');
                    })
                    ->indicateUsing(fn (array $data): ?string => ($data['is_low_stock'] ?? false) ? __('inventory.low_stock_only') : null),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('adjust_stock')
                    ->label(__('inventory.adjust_stock'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        Quantity::make('adjustment_quantity')
                            ->label(__('inventory.adjustment_quantity'))
                            ->steps(1)
                            ->minValue(-1000000)
                            ->default(0)
                            ->required()
                            ->helperText(__('inventory.adjustment_help')),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->update([
                            'quantity' => $record->quantity + $data['adjustment_quantity'],
                        ]);
                        Notification::make()
                            ->title(__('inventory.stock_adjusted_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('track_stock')
                        ->label(__('inventory.track_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_tracked' => true]);
                            Notification::make()
                                ->title(__('inventory.bulk_tracked_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('untrack_stock')
                        ->label(__('inventory.untrack_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_tracked' => false]);
                            Notification::make()
                                ->title(__('inventory.bulk_untracked_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
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
            'index'  => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'view'   => Pages\ViewStock::route('/{record}'),
            'edit'   => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
