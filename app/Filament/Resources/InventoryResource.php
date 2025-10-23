<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use App\Models\Product;
use App\Support\Filament\SearchableComponentHelper;
use App\Support\Search\ProductSearch;
use App\Support\Search\SearchResultPayload;
use BackedEnum;
use Closure;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use UnitEnum;
use Filament\Schemas\Schema;

final class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    /**
     * Navigation icon for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('Inventory');
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Inventory';
    }

    public static function getPluralModelLabel(): string
    {
        return __('Inventories');
    }

    public static function getModelLabel(): string
    {
        return __('Inventory');
    }

    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form->schema([
            Section::make(__('Inventory Details'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            SearchableInput::make('product_id')
                                ->label(__('Product'))
                                ->placeholder('SKU / EAN / name')
                                ->required()
                                ->searchUsing(fn (string $search): array => ProductSearch::complex($search))
                                ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null ? (int) $state : null)
                                ->afterStateHydrated(function (SearchableInput $component, ?int $state, ?Inventory $record): void {
                                    // Use the shared helper so the searchable input always restores the canonical search result
                                    // payload, ensuring the label and metadata stay aligned with ProductSearch::label().
                                    SearchableComponentHelper::hydrate(
                                        component: $component,
                                        state: $state,
                                        resolveResult: function (int|string $identifier) use ($record): ?SearchResult {
                                            $product = $record?->product ?? Product::query()
                                                ->select(['id', 'sku', 'name', 'price'])
                                                ->find((int) $identifier);

                                            if (! $product instanceof Product) {
                                                return null;
                                            }

                                            $rawName = $product->getAttribute('name');
                                            $name = is_array($rawName)
                                                ? (string) ($rawName[app()->getLocale()] ?? reset($rawName) ?? '')
                                                : (string) ($rawName ?? '');
                                            $rawPrice = $product->getAttribute('price');
                                            $price = is_numeric($rawPrice) ? (float) $rawPrice : 0.0;

                                            return SearchResultPayload::normalise(
                                                SearchResult::make(
                                                    value: (string) $product->getKey(),
                                                    label: ProductSearch::label($product),
                                                ),
                                                [
                                                    'product_id' => $product->getKey(),
                                                    'sku'        => (string) ($product->getAttribute('sku') ?? ''),
                                                    'name'       => $name,
                                                    'price'      => $price,
                                                ],
                                            );
                                        },
                                    );
                                })
                                // Behaviour documented at docs/forms/SEARCHABLE_INPUT_METADATA.md to keep helper usage aligned.
                                ->afterStateUpdated(function (SearchableInput $component, ?string $state, callable $set): void {
                                    SearchableComponentHelper::sync(
                                        component: $component,
                                        state: $state,
                                        set: $set,
                                        targetField: 'product_id',
                                        resolveResult: static function (int|string $identifier): ?SearchResult {
                                            $product = Product::query()
                                                ->select(['id', 'sku', 'name', 'price'])
                                                ->find((int) $identifier);

                                            if (! $product instanceof Product) {
                                                return null;
                                            }

                                            $rawName = $product->getAttribute('name');
                                            $name = is_array($rawName)
                                                ? (string) ($rawName[app()->getLocale()] ?? reset($rawName) ?? '')
                                                : (string) ($rawName ?? '');
                                            $rawPrice = $product->getAttribute('price');
                                            $price = is_numeric($rawPrice) ? (float) $rawPrice : 0.0;

                                            return SearchResultPayload::normalise(
                                                SearchResult::make(
                                                    value: (string) $product->getKey(),
                                                    label: ProductSearch::label($product),
                                                ),
                                                [
                                                    'product_id' => $product->getKey(),
                                                    'sku'        => (string) ($product->getAttribute('sku') ?? ''),
                                                    'name'       => $name,
                                                    'price'      => $price,
                                                ],
                                            );
                                        },
                                    );
                                }),
                            Select::make('location_id')
                                ->label(__('Location'))
                                ->relationship('location', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Quantity::make('quantity')
                                ->label(__('Quantity'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0)
                                ->required(),
                            Quantity::make('reserved')
                                ->label(__('Reserved'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Quantity::make('incoming')
                                ->label(__('Incoming'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0),
                            Quantity::make('threshold')
                                ->label(__('Threshold'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0),
                        ]),
                    Toggle::make('is_tracked')
                        ->label(__('Tracked'))
                        ->default(true),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('Product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.name')
                    ->label(__('Location'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('Quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reserved')
                    ->label(__('Reserved'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('incoming')
                    ->label(__('Incoming'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label(__('Available'))
                    ->numeric(),
                TextColumn::make('threshold')
                    ->label(__('Threshold'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_tracked')
                    ->label(__('Tracked'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->label(__('Product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('location')
                    ->label(__('Location'))
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('stock_status')
                    ->label(__('Stock Status'))
                    ->options([
                        'in_stock'     => __('In Stock'),
                        'low_stock'    => __('Low Stock'),
                        'out_of_stock' => __('Out of Stock'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'out_of_stock' => $query->whereRaw('quantity - reserved <= 0'),
                            'low_stock'    => $query->whereRaw('quantity - reserved > 0 AND quantity - reserved <= threshold'),
                            'in_stock'     => $query->whereRaw('quantity - reserved > threshold'),
                            default        => $query,
                        };
                    }),
                SelectFilter::make('is_tracked')
                    ->label(__('Tracked'))
                    ->options([
                        '1' => __('Tracked'),
                        '0' => __('Not Tracked'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! array_key_exists('value', $data)) {
                            return $query;
                        }

                        $value = $data['value'];

                        if ($value === null || $value === '') {
                            return $query;
                        }

                        return $query->where('is_tracked', (bool) (int) $value);
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label(__('Export'))
                    ->exports([
                        self::makeInventoryExport(),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('adjust_stock')
                    ->label(__('Adjust Stock'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->form([
                        Quantity::make('quantity')
                            ->label(__('Quantity'))
                            ->minValue(0)
                            ->steps(1)
                            ->required(),
                        Quantity::make('reserved')
                            ->label(__('Reserved'))
                            ->minValue(0)
                            ->steps(1)
                            ->default(0),
                        Quantity::make('incoming')
                            ->label(__('Incoming'))
                            ->minValue(0)
                            ->steps(1)
                            ->default(0),
                        Quantity::make('threshold')
                            ->label(__('Threshold'))
                            ->minValue(0)
                            ->steps(1)
                            ->default(0),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->update([
                            'quantity'  => (int) $data['quantity'],
                            'reserved'  => (int) ($data['reserved'] ?? 0),
                            'incoming'  => (int) ($data['incoming'] ?? 0),
                            'threshold' => (int) ($data['threshold'] ?? 0),
                        ]);

                        Notification::make()
                            ->title(__('Inventory updated'))
                            ->success()
                            ->send();
                    }),
                Action::make('add_stock')
                    ->label(__('Add Stock'))
                    ->icon('heroicon-o-plus')
                    ->form([
                        Quantity::make('add_quantity')
                            ->label(__('Quantity to Add'))
                            ->minValue(1)
                            ->steps(1)
                            ->default(1)
                            ->required(),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->increment('quantity', (int) $data['add_quantity']);

                        Notification::make()
                            ->title(__('Stock added'))
                            ->success()
                            ->send();
                    }),
                Action::make('remove_stock')
                    ->label(__('Remove Stock'))
                    ->icon('heroicon-o-minus')
                    ->form([
                        Quantity::make('remove_quantity')
                            ->label(__('Quantity to Remove'))
                            ->minValue(1)
                            ->steps(1)
                            ->default(1)
                            ->required()
                            ->rule(function (Inventory $record): Closure {
                                return static function (string $attribute, $value, Closure $fail) use ($record): void {
                                    if ((int) $value > $record->available_quantity) {
                                        $fail(__('Cannot remove more stock than available.'));
                                    }
                                };
                            }),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->update([
                            'quantity' => max(0, $record->quantity - (int) $data['remove_quantity']),
                        ]);

                        Notification::make()
                            ->title(__('Stock removed'))
                            ->success()
                            ->send();
                    }),
                Action::make('reserve_stock')
                    ->label(__('Reserve Stock'))
                    ->icon('heroicon-o-shield-check')
                    ->form([
                        Quantity::make('reserve_quantity')
                            ->label(__('Quantity to Reserve'))
                            ->minValue(1)
                            ->steps(1)
                            ->default(1)
                            ->required()
                            ->rule(function (Inventory $record): Closure {
                                return static function (string $attribute, $value, Closure $fail) use ($record): void {
                                    if ((int) $value > $record->available_quantity) {
                                        $fail(__('Cannot reserve more stock than available.'));
                                    }
                                };
                            }),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->update([
                            'reserved' => $record->reserved + (int) $data['reserve_quantity'],
                        ]);

                        Notification::make()
                            ->title(__('Stock reserved'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label(__('Export selected'))
                        ->exports([
                            self::makeInventoryExport(),
                        ]),
                    DeleteBulkAction::make(),
                    BulkAction::make('adjust_stock')
                        ->label(__('Adjust Stock'))
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->form([
                            Quantity::make('quantity')
                                ->label(__('Quantity'))
                                ->minValue(0)
                                ->steps(1)
                                ->required(),
                            Quantity::make('reserved')
                                ->label(__('Reserved'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0),
                            Quantity::make('incoming')
                                ->label(__('Incoming'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0),
                            Quantity::make('threshold')
                                ->label(__('Threshold'))
                                ->minValue(0)
                                ->steps(1)
                                ->default(0),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Inventory $inventory) use ($data): void {
                                $inventory->update([
                                    'quantity'  => (int) $data['quantity'],
                                    'reserved'  => (int) ($data['reserved'] ?? 0),
                                    'incoming'  => (int) ($data['incoming'] ?? 0),
                                    'threshold' => (int) ($data['threshold'] ?? 0),
                                ]);
                            });

                            Notification::make()
                                ->title(__('Inventory updated'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('add_stock')
                        ->label(__('Add Stock'))
                        ->icon('heroicon-o-plus')
                        ->form([
                            Quantity::make('add_quantity')
                                ->label(__('Quantity to Add'))
                                ->minValue(1)
                                ->steps(1)
                                ->default(1)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Inventory $inventory) use ($data): void {
                                $inventory->increment('quantity', (int) $data['add_quantity']);
                            });

                            Notification::make()
                                ->title(__('Stock added'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('toggle_tracking')
                        ->label(__('Toggle Tracking'))
                        ->icon('heroicon-o-eye')
                        ->form([
                            Toggle::make('is_tracked')
                                ->label(__('Tracked'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Inventory $inventory) use ($data): void {
                                $inventory->update([
                                    'is_tracked' => (bool) $data['is_tracked'],
                                ]);
                            });

                            Notification::make()
                                ->title(__('Tracking updated'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function makeInventoryExport(): ExcelExport
    {
        return ExcelExport::make('stock_snapshot')
            ->fromTable()
            ->queue()
            ->withChunkSize(500)
            ->withColumns([
                Column::make('product.sku')
                    ->heading(__('SKU')),
                Column::make('product.name')
                    ->heading(__('Product')),
                Column::make('location.name')
                    ->heading(__('Location')),
                Column::make('quantity')
                    ->heading(__('Quantity'))
                    ->formatStateUsing(static fn (?int $state): string => number_format((int) $state)),
                Column::make('reserved')
                    ->heading(__('Reserved'))
                    ->formatStateUsing(static fn (?int $state): string => number_format((int) $state)),
                Column::make('available_quantity')
                    ->heading(__('Available'))
                    ->formatStateUsing(static fn (?int $state): string => number_format((int) $state)),
                Column::make('incoming')
                    ->heading(__('Incoming'))
                    ->formatStateUsing(static fn (?int $state): string => number_format((int) $state)),
                Column::make('threshold')
                    ->heading(__('Threshold'))
                    ->formatStateUsing(static fn (?int $state): string => number_format((int) $state)),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'view'   => Pages\ViewInventory::route('/{record}'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
