<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Forms\Components\Flatpickr;
use App\Filament\Resources\VariantInventoryResource\Pages;
use App\Models\VariantInventory;
use App\Support\Filament\Components\Flatpickr;
use App\Support\Filament\SearchableInputHelper;
use App\Support\Search\LocationSearch;
use App\Support\Search\PartnerSearch;
use App\Support\Search\ProductVariantSearch;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentNumber;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * VariantInventoryResource
 *
 * Filament v4 resource for VariantInventory management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class VariantInventoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = VariantInventory::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'variant_id';

    /**
     * @var string|BackedEnum|null Navigation icon configured for the inventory module.
     */
    protected static $navigationIcon = 'heroicon-o-archive-box';

    /**
     * Navigation group for Filament navigation.
     *
     * @var string|\BackedEnum|\UnitEnum|\UnitEnum|null
     */
    protected static $navigationGroup = 'Inventory';

    public static function getNavigationLabel(): string
    {
        return __('admin.variant_inventory.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.variant_inventory.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.variant_inventory.model_label');
    }

    /**
     * Configure the Variant Inventory form schema for Filament administrators.
     */
    public static function form(Form $form): Form
    {
        // Filament 4 expects returning the Form builder instance.
        return $form
            ->schema([
                Section::make(__('admin.variant_inventory.basic_information'))
                    ->columns(2)
                    ->schema([
                        // Row 1: searchable selectors align with the two-column section layout.
                        SearchableInput::make('variant_id')
                            ->label(__('admin.variant_inventory.variant'))
                            ->placeholder(__('admin.variant_inventory.variant_placeholder'))
                            ->required()
                            ->searchUsing(fn (string $value): array => ProductVariantSearch::results($value))
                            ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                            ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                // Hydrate via helper for metadata consistency.
                                SearchableInputHelper::hydrate(
                                    $component,
                                    $state,
                                    static function (int $value): ?array {
                                        $variant = ProductVariant::query()
                                            ->select(['id', 'product_id', 'sku', 'name', 'price'])
                                            ->with(['product:id,sku,name'])
                                            ->find($value);

                                        if (! $variant instanceof ProductVariant) {
                                            return null;
                                        }

                                        return [
                                            'value' => $variant->getKey(),
                                            'label' => ProductVariantSearch::label($variant),
                                        ];
                                    },
                                );
                            })
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if ($state === null || $state === '') {
                                    SearchableInputHelper::clear($set, ['variant_id' => null]);

                                    return;
                                }

                                $set('variant_id', (int) $state);
                            }),
                        SearchableInput::make('location_id')
                            ->label(__('admin.variant_inventory.location'))
                            ->placeholder(__('admin.variant_inventory.location_placeholder'))
                            ->required()
                            ->searchUsing(fn (string $value): array => LocationSearch::results($value))
                            ->dehydrateStateUsing(fn (?string $state): ?int => $state !== null && $state !== '' ? (int) $state : null)
                            ->afterStateHydrated(function (SearchableInput $component, ?int $state): void {
                                // Hydrate via helper to mirror docs/forms/SEARCHABLE_INPUT_METADATA.md lifecycle.
                                SearchableInputHelper::hydrate(
                                    $component,
                                    $state,
                                    static function (int $value): ?array {
                                        $location = Location::query()
                                            ->select(['id', 'name', 'code', 'city', 'country_code'])
                                            ->find($value);

                                        if (! $location instanceof Location) {
                                            return null;
                                        }

                                        return [
                                            'value' => $location->getKey(),
                                            'label' => LocationSearch::label($location),
                                        ];
                                    },
                                );
                            })
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if ($state === null || $state === '') {
                                    SearchableInputHelper::clear($set, ['location_id' => null]);

                                    return;
                                }

                                $set('location_id', (int) $state);
                            }),
                        // Row 2: warehouse and batch identifiers reuse the same column count for clarity.
                        TextInput::make('warehouse_code')
                            ->label(__('admin.variant_inventory.warehouse_code'))
                            ->maxLength(50),
                        TextInput::make('batch_number')
                            ->label(__('admin.variant_inventory.batch_number'))
                            ->maxLength(100),
                    ]),
                Section::make(__('admin.variant_inventory.stock_levels'))
                    ->columns(3)
                    ->schema([
                        // Row 1: live stock metrics distribute across three columns for parity with the list view.
                        TextInput::make('stock')
                            ->label(__('admin.variant_inventory.stock'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('reserved')
                            ->label(__('admin.variant_inventory.reserved'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('available')
                            ->label(__('admin.variant_inventory.available'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        // Row 2: planning metrics stay aligned with the same three-column rhythm.
                        TextInput::make('incoming')
                            ->label(__('admin.variant_inventory.incoming'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('threshold')
                            ->label(__('admin.variant_inventory.threshold'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('reorder_point')
                            ->label(__('admin.variant_inventory.reorder_point'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ]),
                Section::make(__('admin.variant_inventory.pricing'))
                    ->columns(2)
                    ->schema([
                        // Row 1: core pricing fields remain paired for quick comparison.
                        TextInput::make('cost_per_unit')
                            ->label(__('admin.variant_inventory.cost_per_unit'))
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),
                        TextInput::make('reorder_quantity')
                            ->label(__('admin.variant_inventory.reorder_quantity'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        // Row 2: supplier scheduling data follows the same alignment pattern.
                        Flatpickr::makeDate('expiry_date')
                            ->label(__('admin.variant_inventory.expiry_date')),
                        TextInput::make('supplier_id')
                            ->label(__('admin.variant_inventory.supplier_id'))
                            ->numeric(),
                    ]),
                Section::make(__('admin.variant_inventory.additional_info'))
                    ->columns(2)
                    ->schema([
                        // Row 1: tracking toggle with status select for operational state management.
                        Toggle::make('is_tracked')
                            ->label(__('admin.variant_inventory.is_tracked'))
                            ->default(true),
                        Select::make('status')
                            ->label(__('admin.variant_inventory.status'))
                            ->options([
                                'active'       => __('admin.variant_inventory.status_active'),
                                'inactive'     => __('admin.variant_inventory.status_inactive'),
                                'discontinued' => __('admin.variant_inventory.status_discontinued'),
                            ])
                            ->default('active'),
                        // Row 2: notes span the full section width to encourage longer narratives when needed.
                        Textarea::make('notes')
                            ->label(__('admin.variant_inventory.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                        // Row 3: restock timestamps stay paired in the shared column layout.
                        Flatpickr::makeDate('last_restocked_at')
                            ->label(__('admin.variant_inventory.last_restocked_at')),
                        Flatpickr::makeDate('last_sold_at')
                            ->label(__('admin.variant_inventory.last_sold_at')),
                    ]),
                Section::make(__('admin.variant_inventory.calculated_fields'))
                    ->columns(3)
                    ->schema([
                        // Calculated data points mirror the display table column trio for consistency.
                        Placeholder::make('is_low_stock')
                            ->label(__('admin.variant_inventory.is_low_stock'))
                            ->content(fn (?VariantInventory $record): string => $record ? ($record->is_low_stock ? __('admin.variant_inventory.yes') : __('admin.variant_inventory.no')) : '-'),
                        Placeholder::make('is_out_of_stock')
                            ->label(__('admin.variant_inventory.is_out_of_stock'))
                            ->content(fn (?VariantInventory $record): string => $record ? ($record->is_out_of_stock ? __('admin.variant_inventory.yes') : __('admin.variant_inventory.no')) : '-'),
                        Placeholder::make('stock_status')
                            ->label(__('admin.variant_inventory.stock_status'))
                            ->content(fn (?VariantInventory $record): string => $record ? __('admin.variant_inventory.status_' . $record->stock_status) : '-'),
                    ])
                    ->hidden(fn (?VariantInventory $record): bool => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Filament 4 expects returning the Table builder instance.
        return $table
            ->columns([
                TextColumn::make('variant.name')
                    ->label(__('admin.variant_inventory.variant'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location.name')
                    ->label(__('admin.variant_inventory.location'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('warehouse_code')
                    ->label(__('admin.variant_inventory.warehouse_code'))
                    ->toggleable(),
                TextColumn::make('stock')
                    ->label(__('admin.variant_inventory.stock'))
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state < 10 ? 'danger' : ($state < 50 ? 'warning' : 'success')),
                TextColumn::make('reserved')
                    ->label(__('admin.variant_inventory.reserved'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('available')
                    ->label(__('admin.variant_inventory.available'))
                    ->numeric()
                    ->sortable()
                    ->color(fn ($state) => $state < 10 ? 'danger' : ($state < 50 ? 'warning' : 'success')),
                TextColumn::make('threshold')
                    ->label(__('admin.variant_inventory.threshold'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('cost_per_unit')
                    ->label(__('admin.variant_inventory.cost_per_unit'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expiry_date')
                    ->label(__('admin.variant_inventory.expiry_date'))
                    ->date()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_tracked')
                    ->label(__('admin.variant_inventory.is_tracked'))
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('admin.variant_inventory.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'       => 'success',
                        'inactive'     => 'warning',
                        'discontinued' => 'danger',
                        default        => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('batch_number')
                    ->label(__('admin.variant_inventory.batch_number'))
                    ->toggleable(),
                TextColumn::make('supplier_id')
                    ->label(__('admin.variant_inventory.supplier_id'))
                    ->toggleable(),
                IconColumn::make('is_low_stock')
                    ->label(__('admin.variant_inventory.is_low_stock'))
                    ->boolean()
                    ->color(fn ($state) => $state ? 'warning' : 'success')
                    ->toggleable(),
                IconColumn::make('is_out_of_stock')
                    ->label(__('admin.variant_inventory.is_out_of_stock'))
                    ->boolean()
                    ->color(fn ($state) => $state ? 'danger' : 'success')
                    ->toggleable(),
                TextColumn::make('utilization_percentage')
                    ->label(__('admin.variant_inventory.utilization_percentage'))
                    ->formatStateUsing(static function ($state): string {
                        // Use FilamentNumber so percentage formatting respects panel-wide locale overrides.
                        return FilamentNumber::format((float) ($state ?? 0), 2) . '%';
                    })
                    ->color(fn ($state) => $state > 80 ? 'warning' : 'success')
                    ->toggleable(),
                TextColumn::make('last_restocked_at')
                    ->label(__('admin.variant_inventory.last_restocked_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.variant_inventory.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('variant_id')
                    ->label(__('admin.variant_inventory.variant'))
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('location_id')
                    ->label(__('admin.variant_inventory.location'))
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label(__('admin.variant_inventory.status'))
                    ->options([
                        'active'       => __('admin.variant_inventory.status_active'),
                        'inactive'     => __('admin.variant_inventory.status_inactive'),
                        'discontinued' => __('admin.variant_inventory.status_discontinued'),
                    ]),
                TernaryFilter::make('is_tracked')
                    ->label(__('admin.variant_inventory.is_tracked'))
                    ->boolean()
                    ->trueLabel(__('admin.variant_inventory.tracked'))
                    ->falseLabel(__('admin.variant_inventory.not_tracked')),
                Filter::make('low_stock')
                    ->label(__('admin.variant_inventory.low_stock'))
                    ->query(fn (Builder $query): Builder => $query->whereRaw('available <= reorder_point'))
                    ->toggle(),
                Filter::make('out_of_stock')
                    ->label(__('admin.variant_inventory.out_of_stock'))
                    ->query(fn (Builder $query): Builder => $query->where('available', '<=', 0))
                    ->toggle(),
                Filter::make('expiring_soon')
                    ->label(__('admin.variant_inventory.expiring_soon'))
                    ->query(fn (Builder $query): Builder => $query->where('expiry_date', '<=', now()->addDays(30)))
                    ->toggle(),
                Filter::make('needs_reorder')
                    ->label(__('admin.variant_inventory.needs_reorder'))
                    ->query(fn (Builder $query): Builder => $query->whereRaw('available <= reorder_point'))
                    ->toggle(),
                Filter::make('high_utilization')
                    ->label(__('admin.variant_inventory.high_utilization'))
                    ->query(fn (Builder $query): Builder => $query->whereRaw('(reserved / stock) * 100 > 80'))
                    ->toggle(),
            ])
            ->groups([
                Group::make('variant.name')
                    ->label(__('admin.variant_inventory.group_by_variant'))
                    ->collapsible(),
                Group::make('location.name')
                    ->label(__('admin.variant_inventory.group_by_location'))
                    ->collapsible(),
                Group::make('status')
                    ->label(__('admin.variant_inventory.group_by_status'))
                    ->collapsible(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('adjust_stock')
                    ->label(__('admin.variant_inventory.adjust_stock'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('admin.variant_inventory.quantity'))
                            ->numeric()
                            ->required(),
                        Select::make('adjustment_type')
                            ->label(__('admin.variant_inventory.adjustment_type'))
                            ->options([
                                'add'      => __('admin.variant_inventory.add_stock'),
                                'subtract' => __('admin.variant_inventory.subtract_stock'),
                                'set'      => __('admin.variant_inventory.set_stock'),
                            ])
                            ->required(),
                        Textarea::make('reason')
                            ->label(__('admin.variant_inventory.reason'))
                            ->rows(2),
                    ])
                    ->action(function (array $data, ListRecords $livewire): void {
                        /** @var VariantInventory $record */
                        $record = $livewire->getMountedTableActionRecord();
                        $quantity = (int) ($data['quantity'] ?? 0);
                        $type = $data['adjustment_type'] ?? 'add';

                        switch ($type) {
                            case 'add':
                                $record->addStock($quantity);
                                break;
                            case 'subtract':
                                $record->removeStock($quantity);
                                break;
                            case 'set':
                                $record->stock = $quantity;
                                $record->updateAvailableStock();
                                break;
                        }

                        $record->save();
                        Notification::make()
                            ->title(__('admin.variant_inventory.stock_adjusted_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('reserve_stock')
                    ->label(__('admin.variant_inventory.reserve_stock'))
                    ->icon('heroicon-o-lock-closed')
                    ->color('info')
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('admin.variant_inventory.quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Textarea::make('reason')
                            ->label(__('admin.variant_inventory.reason'))
                            ->rows(2),
                    ])
                    ->action(function (array $data, ListRecords $livewire): void {
                        /** @var VariantInventory $record */
                        $record = $livewire->getMountedTableActionRecord();
                        $quantity = (int) ($data['quantity'] ?? 0);

                        if ($record->reserveStock($quantity)) {
                            Notification::make()->title(__('admin.variant_inventory.stock_reserved_successfully'))->success()->send();
                        } else {
                            Notification::make()->title(__('admin.variant_inventory.insufficient_stock'))->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('bulk_adjust_stock')
                        ->label(__('admin.variant_inventory.bulk_adjust_stock'))
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('warning')
                        ->form([
                            TextInput::make('quantity')
                                ->label(__('admin.variant_inventory.quantity'))
                                ->numeric()
                                ->required(),
                            Select::make('adjustment_type')
                                ->label(__('admin.variant_inventory.adjustment_type'))
                                ->options([
                                    'add'      => __('admin.variant_inventory.add_stock'),
                                    'subtract' => __('admin.variant_inventory.subtract_stock'),
                                    'set'      => __('admin.variant_inventory.set_stock'),
                                ])
                                ->required(),
                            Textarea::make('reason')
                                ->label(__('admin.variant_inventory.reason'))
                                ->rows(2),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $quantity = (int) $data['quantity'];
                            $type = $data['adjustment_type'];
                            $count = 0;

                            foreach ($records as $record) {
                                switch ($type) {
                                    case 'add':
                                        $record->addStock($quantity);
                                        break;
                                    case 'subtract':
                                        $record->removeStock($quantity);
                                        break;
                                    case 'set':
                                        $record->stock = $quantity;
                                        $record->updateAvailableStock();
                                        break;
                                }
                                $record->save();
                                $count++;
                            }
                            Notification::make()
                                ->title(__('admin.variant_inventory.bulk_stock_adjusted_successfully', ['count' => $count]))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('bulk_update_status')
                        ->label(__('admin.variant_inventory.bulk_update_status'))
                        ->icon('heroicon-o-check-circle')
                        ->color('info')
                        ->form([
                            Select::make('status')
                                ->label(__('admin.variant_inventory.status'))
                                ->options([
                                    'active'       => __('admin.variant_inventory.status_active'),
                                    'inactive'     => __('admin.variant_inventory.status_inactive'),
                                    'discontinued' => __('admin.variant_inventory.status_discontinued'),
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $status = $data['status'];
                            $count = $records->count();

                            $records->each(function ($record) use ($status): void {
                                $record->update(['status' => $status]);
                            });

                            Notification::make()
                                ->title(__('admin.variant_inventory.bulk_status_updated_successfully', ['count' => $count]))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('export_inventory')
                        ->label(__('admin.variant_inventory.export_inventory'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Export logic here
                            Notification::make()
                                ->title(__('admin.variant_inventory.exported_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Provide a reusable percentage formatter for table output to keep display consistent.
     */
    protected static function formatPercentage(float|int|null $value): string
    {
        // Guard against null while preserving decimal precision for inventory metrics.
        return number_format((float) $value, 2) . '%';
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
            'index'  => Pages\ListVariantInventories::route('/'),
            'create' => Pages\CreateVariantInventory::route('/create'),
            'view'   => Pages\ViewVariantInventory::route('/{record}'),
            'edit'   => Pages\EditVariantInventory::route('/{record}/edit'),
        ];
    }
}
