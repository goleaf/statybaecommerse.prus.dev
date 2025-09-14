<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Filament\Resources\StockResource\RelationManagers;
use App\Models\Location;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Schemas\Components\DatePicker;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\BadgeEntry;
use Filament\Schemas\Components\RepeatableEntry;
use Filament\Schemas\Components\Section as InfolistSection;
use Filament\Schemas\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;


use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;
use App\Enums\NavigationGroup;
use UnitEnum;

/**
 * StockResource
 * 
 * Filament resource for admin panel management.
 */
class StockResource extends Resource
{
    protected static ?string $model = VariantInventory::class;

    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-cube';

    /** @var string|\BackedEnum|null */

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Inventory->label();
    }

    protected static ?string $navigationLabel = 'inventory.stock_management';

    protected static ?string $modelLabel = 'inventory.stock_item';

    protected static ?string $pluralModelLabel = 'inventory.stock_items';

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function getNavigationLabel(): string
    {
        return __('inventory.stock_management');
    }

    public static function getModelLabel(): string
    {
        return __('inventory.stock_item');
    }

    public static function getPluralModelLabel(): string
    {
        return __('inventory.stock_items');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::lowStock()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $lowStockCount = static::getModel()::lowStock()->count();

        return $lowStockCount > 0 ? 'warning' : 'success';
    }

    public static function form(Schema $schema): Schema {
        return $schema->schema([
                Section::make(__('inventory.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('variant_id')
                                    ->label(__('inventory.product_variant'))
                                    ->relationship('variant', 'display_name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Select::make('product_id')
                                            ->label(__('inventory.product'))
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('sku')
                                            ->label(__('inventory.sku'))
                                            ->required()
                                            ->unique(ProductVariant::class, 'sku', ignoreRecord: true),
                                        TextInput::make('name')
                                            ->label(__('inventory.variant_name'))
                                            ->required(),
                                        TextInput::make('price')
                                            ->label(__('inventory.price'))
                                            ->numeric()
                                            ->prefix('€')
                                            ->required(),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return ProductVariant::create($data)->getKey();
                                    }),
                                Select::make('location_id')
                                    ->label(__('inventory.location'))
                                    ->relationship('location', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label(__('inventory.location_name'))
                                            ->required(),
                                        TextInput::make('code')
                                            ->label(__('inventory.location_code'))
                                            ->required()
                                            ->unique(Location::class, 'code'),
                                        TextInput::make('address_line_1')
                                            ->label(__('inventory.address_line_1')),
                                        TextInput::make('city')
                                            ->label(__('inventory.city')),
                                        TextInput::make('postal_code')
                                            ->label(__('inventory.postal_code')),
                                        Toggle::make('is_enabled')
                                            ->label(__('inventory.is_enabled'))
                                            ->default(true),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return Location::create($data)->getKey();
                                    }),
                            ]),
                    ]),
                Section::make(__('inventory.stock_levels'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('stock')
                                    ->label(__('inventory.current_stock'))
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('available_stock', max(0, $state - $set('reserved', 0)));
                                    }),
                                TextInput::make('reserved')
                                    ->label(__('inventory.reserved_stock'))
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $set('available_stock', max(0, $get('stock') - $state));
                                    }),
                                TextInput::make('incoming')
                                    ->label(__('inventory.incoming_stock'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('threshold')
                                    ->label(__('inventory.low_stock_threshold'))
                                    ->numeric()
                                    ->default(0)
                                    ->helperText(__('inventory.low_stock_threshold_help')),
                                TextInput::make('reorder_point')
                                    ->label(__('inventory.reorder_point'))
                                    ->numeric()
                                    ->default(0)
                                    ->helperText(__('inventory.reorder_point_help')),
                                TextInput::make('max_stock_level')
                                    ->label(__('inventory.max_stock_level'))
                                    ->numeric()
                                    ->helperText(__('inventory.max_stock_level_help')),
                            ]),
                    ]),
                Section::make(__('inventory.additional_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('supplier_id')
                                    ->label(__('inventory.supplier'))
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('cost_per_unit')
                                    ->label(__('inventory.cost_per_unit'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('batch_number')
                                    ->label(__('inventory.batch_number')),
                                DatePicker::make('expiry_date')
                                    ->label(__('inventory.expiry_date')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label(__('inventory.status'))
                                    ->options([
                                        'active' => __('inventory.status_active'),
                                        'inactive' => __('inventory.status_inactive'),
                                        'discontinued' => __('inventory.status_discontinued'),
                                        'quarantine' => __('inventory.status_quarantine'),
                                    ])
                                    ->default('active'),
                                Toggle::make('is_tracked')
                                    ->label(__('inventory.track_inventory'))
                                    ->default(true)
                                    ->helperText(__('inventory.track_inventory_help')),
                            ]),
                        Textarea::make('notes')
                            ->label(__('inventory.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('inventory.timestamps'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('last_restocked_at')
                                    ->label(__('inventory.last_restocked_at'))
                                    ->disabled(),
                                DateTimePicker::make('last_sold_at')
                                    ->label(__('inventory.last_sold_at'))
                                    ->disabled(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variant.product.name')
                    ->label(__('inventory.product'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->url(fn (VariantInventory $record): string => ProductResource::getUrl('view', ['record' => $record->variant->product_id])),
                TextColumn::make('variant.display_name')
                    ->label(__('inventory.variant'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('location.name')
                    ->label(__('inventory.location'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('stock')
                    ->label(__('inventory.stock'))
                    ->sortable()
                    ->alignEnd()
                    ->weight(FontWeight::Bold)
                    ->color(fn (int $state, VariantInventory $record): string => match (true) {
                        $record->isOutOfStock() => 'danger',
                        $record->isLowStock() => 'warning',
                        default => 'success'
                    }),
                TextColumn::make('reserved')
                    ->label(__('inventory.reserved'))
                    ->sortable()
                    ->alignEnd()
                    ->color('warning'),
                TextColumn::make('available_stock')
                    ->label(__('inventory.available'))
                    ->sortable()
                    ->alignEnd()
                    ->weight(FontWeight::Bold)
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success'
                    }),
                BadgeColumn::make('stock_status')
                    ->label(__('inventory.status'))
                    ->colors([
                    'success' => 'in_stock',
                    'warning' => 'low_stock',
                    'danger' => 'out_of_stock',
                    'secondary' => 'not_tracked',
                    'info' => 'needs_reorder',
                    ])
                    ->formatStateUsing(fn (string $state): string => __('inventory.'.$state)),
                TextColumn::make('cost_per_unit')
                    ->label(__('inventory.cost_per_unit'))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('stock_value')
                    ->label(__('inventory.stock_value'))
                    ->money('EUR')
                    ->sortable()
                    ->alignEnd()
                    ->weight(FontWeight::Bold),
                TextColumn::make('supplier.name')
                    ->label(__('inventory.supplier'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expiry_date')
                    ->label(__('inventory.expiry_date'))
                    ->date()
                    ->sortable()
                    ->color(fn (?string $state): string => match (true) {
                        ! $state => 'gray',
                        strtotime($state) < strtotime('+30 days') => 'warning',
                        strtotime($state) < strtotime('+7 days') => 'danger',
                        default => 'success'
                    })
                    ->toggleable(),
                IconColumn::make('is_tracked')
                    ->label(__('inventory.tracked'))
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('last_restocked_at')
                    ->label(__('inventory.last_restocked'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_sold_at')
                    ->label(__('inventory.last_sold'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('inventory.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('location_id')
                    ->label(__('inventory.location'))
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('supplier_id')
                    ->label(__('inventory.supplier'))
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label(__('inventory.status'))
                    ->options([
                    'active' => __('inventory.status_active'),
                    'inactive' => __('inventory.status_inactive'),
                    'discontinued' => __('inventory.status_discontinued'),
                    'quarantine' => __('inventory.status_quarantine'),
                    ]),
                TernaryFilter::make('is_tracked')
                    ->label(__('inventory.tracked'))
                    ->placeholder(__('inventory.all_items'))
                    ->trueLabel(__('inventory.tracked_only'))
                    ->falseLabel(__('inventory.not_tracked_only')),
                Filter::make('low_stock')
                    ->label(__('inventory.low_stock'))
                    ->query(fn (Builder $query): Builder => $query->lowStock()),
                Filter::make('out_of_stock')
                    ->label(__('inventory.out_of_stock'))
                    ->query(fn (Builder $query): Builder => $query->outOfStock()),
                Filter::make('needs_reorder')
                    ->label(__('inventory.needs_reorder'))
                    ->query(fn (Builder $query): Builder => $query->needsReorder()),
                Filter::make('expiring_soon')
                    ->label(__('inventory.expiring_soon'))
                    ->query(fn (Builder $query): Builder => $query->expiringSoon()),
                Filter::make('expired')
                    ->label(__('inventory.expired'))
                    ->query(fn (Builder $query): Builder => $query
                    ->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now())),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Action::make('adjust_stock')
                    ->label(__('inventory.adjust_stock'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('inventory.adjustment_quantity'))
                            ->numeric()
                            ->required()
                            ->helperText(__('inventory.adjustment_quantity_help')),
                        Select::make('reason')
                            ->label(__('inventory.adjustment_reason'))
                            ->options([
                                'manual_adjustment' => __('inventory.reason_manual_adjustment'),
                                'damage' => __('inventory.reason_damage'),
                                'theft' => __('inventory.reason_theft'),
                                'return' => __('inventory.reason_return'),
                                'restock' => __('inventory.reason_restock'),
                                'transfer' => __('inventory.reason_transfer'),
                            ])
                            ->required(),
                        Textarea::make('notes')
                            ->label(__('inventory.adjustment_notes'))
                            ->rows(3),
                    ])
                    ->action(function (VariantInventory $record, array $data): void {
                        $record->adjustStock($data['quantity'], $data['reason']);

                        Notification::make()
                            ->title(__('inventory.stock_adjusted'))
                            ->body(__('inventory.stock_adjusted_message', [
                                'quantity' => $data['quantity'],
                                'product' => $record->display_name,
                            ]))
                            ->success()
                            ->send();
                    }),
                    Action::make('reserve_stock')
                    ->label(__('inventory.reserve_stock'))
                    ->icon('heroicon-o-lock-closed')
                    ->color('info')
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('inventory.reserve_quantity'))
                            ->numeric()
                            ->required()
                            ->maxValue(fn (VariantInventory $record): int => $record->available_stock),
                        Textarea::make('notes')
                            ->label(__('inventory.reserve_notes'))
                            ->rows(3),
                    ])
                    ->action(function (VariantInventory $record, array $data): void {
                        if ($record->reserve($data['quantity'])) {
                            Notification::make()
                                ->title(__('inventory.stock_reserved'))
                                ->body(__('inventory.stock_reserved_message', [
                                    'quantity' => $data['quantity'],
                                    'product' => $record->display_name,
                                ]))
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('inventory.reserve_failed'))
                                ->body(__('inventory.reserve_failed_message'))
                                ->danger()
                                ->send();
                        }
                    }),
                    Action::make('unreserve_stock')
                    ->label(__('inventory.unreserve_stock'))
                    ->icon('heroicon-o-lock-open')
                    ->color('gray')
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('inventory.unreserve_quantity'))
                            ->numeric()
                            ->required()
                            ->maxValue(fn (VariantInventory $record): int => $record->reserved),
                        Textarea::make('notes')
                            ->label(__('inventory.unreserve_notes'))
                            ->rows(3),
                    ])
                    ->action(function (VariantInventory $record, array $data): void {
                        $record->unreserve($data['quantity']);

                        Notification::make()
                            ->title(__('inventory.stock_unreserved'))
                            ->body(__('inventory.stock_unreserved_message', [
                                'quantity' => $data['quantity'],
                                'product' => $record->display_name,
                            ]))
                            ->success()
                            ->send();
                    }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('bulk_adjust_stock')
                    ->label(__('inventory.bulk_adjust_stock'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('inventory.adjustment_quantity'))
                            ->numeric()
                            ->required(),
                        Select::make('reason')
                            ->label(__('inventory.adjustment_reason'))
                            ->options([
                                'manual_adjustment' => __('inventory.reason_manual_adjustment'),
                                'damage' => __('inventory.reason_damage'),
                                'theft' => __('inventory.reason_theft'),
                                'return' => __('inventory.reason_return'),
                                'restock' => __('inventory.reason_restock'),
                                'transfer' => __('inventory.reason_transfer'),
                            ])
                            ->required(),
                        Textarea::make('notes')
                            ->label(__('inventory.adjustment_notes'))
                            ->rows(3),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        $adjustedCount = 0;

                        foreach ($records as $record) {
                            $record->adjustStock($data['quantity'], $data['reason']);
                            $adjustedCount++;
                        }

                        Notification::make()
                            ->title(__('inventory.bulk_stock_adjusted'))
                            ->body(__('inventory.bulk_stock_adjusted_message', [
                                'count' => $adjustedCount,
                                'quantity' => $data['quantity'],
                            ]))
                            ->success()
                            ->send();
                    }),
                    BulkAction::make('export_stock')
                    ->label(__('inventory.export_stock'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (Collection $records): void {
                        // Export logic would go here
                        Notification::make()
                            ->title(__('inventory.export_started'))
                            ->body(__('inventory.export_started_message', [
                                'count' => $records->count(),
                            ]))
                            ->success()
                            ->send();
                    }),
                ]),
            ])
            ->groups([
                Group::make('location.name')
                    ->label(__('inventory.group_by_location'))
                    ->collapsible(),
                Group::make('variant.product.name')
                    ->label(__('inventory.group_by_product'))
                    ->collapsible(),
                Group::make('supplier.name')
                    ->label(__('inventory.group_by_supplier'))
                    ->collapsible(),
                Group::make('status')
                    ->label(__('inventory.group_by_status'))
                    ->collapsible(),
            ])
            ->defaultGroup('location.name')
            ->poll('30s')
            ->deferLoading()
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $infolist
            ->schema([
                InfolistSection::make(__('inventory.basic_information'))
                    ->schema([
                        TextEntry::make('variant.product.name')
                            ->label(__('inventory.product'))
                            ->url(fn (VariantInventory $record): string => ProductResource::getUrl('view', ['record' => $record->variant->product_id])),
                        TextEntry::make('variant.display_name')
                            ->label(__('inventory.variant')),
                        TextEntry::make('location.name')
                            ->label(__('inventory.location'))
                            ->badge()
                            ->color('info'),
                        TextEntry::make('supplier.name')
                            ->label(__('inventory.supplier'))
                            ->badge()
                            ->color('success'),
                    ])
                    ->columns(2),
                InfolistSection::make(__('inventory.stock_levels'))
                    ->schema([
                        TextEntry::make('stock')
                            ->label(__('inventory.current_stock'))
                            ->badge()
                            ->color(fn (int $state, VariantInventory $record): string => match (true) {
                                $record->isOutOfStock() => 'danger',
                                $record->isLowStock() => 'warning',
                                default => 'success'
                            }),
                        TextEntry::make('reserved')
                            ->label(__('inventory.reserved_stock'))
                            ->badge()
                            ->color('warning'),
                        TextEntry::make('available_stock')
                            ->label(__('inventory.available_stock'))
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state <= 0 => 'danger',
                                $state <= 10 => 'warning',
                                default => 'success'
                            }),
                        TextEntry::make('incoming')
                            ->label(__('inventory.incoming_stock'))
                            ->badge()
                            ->color('info'),
                        TextEntry::make('threshold')
                            ->label(__('inventory.low_stock_threshold')),
                        TextEntry::make('reorder_point')
                            ->label(__('inventory.reorder_point')),
                        TextEntry::make('max_stock_level')
                            ->label(__('inventory.max_stock_level')),
                        BadgeEntry::make('stock_status')
                            ->label(__('inventory.status'))
                            ->colors([
                            'success' => 'in_stock',
                            'warning' => 'low_stock',
                            'danger' => 'out_of_stock',
                            'secondary' => 'not_tracked',
                            'info' => 'needs_reorder',
                            ])
                            ->formatStateUsing(fn (string $state): string => __('inventory.'.$state)),
                    ])
                    ->columns(4),
                InfolistSection::make(__('inventory.financial_information'))
                    ->schema([
                        TextEntry::make('cost_per_unit')
                            ->label(__('inventory.cost_per_unit'))
                            ->money('EUR'),
                        TextEntry::make('stock_value')
                            ->label(__('inventory.stock_value'))
                            ->money('EUR')
                            ->weight(FontWeight::Bold),
                        TextEntry::make('reserved_value')
                            ->label(__('inventory.reserved_value'))
                            ->money('EUR'),
                        TextEntry::make('total_value')
                            ->label(__('inventory.total_value'))
                            ->money('EUR')
                            ->weight(FontWeight::Bold),
                    ])
                    ->columns(2),
                InfolistSection::make(__('inventory.additional_information'))
                    ->schema([
                        TextEntry::make('batch_number')
                            ->label(__('inventory.batch_number'))
                            ->placeholder(__('inventory.not_set')),
                        TextEntry::make('expiry_date')
                            ->label(__('inventory.expiry_date'))
                            ->date()
                            ->color(fn (?string $state): string => match (true) {
                                ! $state => 'gray',
                                strtotime($state) < strtotime('+30 days') => 'warning',
                                strtotime($state) < strtotime('+7 days') => 'danger',
                                default => 'success'
                            })
                            ->placeholder(__('inventory.no_expiry')),
                        TextEntry::make('is_tracked')
                            ->label(__('inventory.tracked'))
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                            ->formatStateUsing(fn (bool $state): string => $state ? __('inventory.yes') : __('inventory.no')),
                        TextEntry::make('notes')
                            ->label(__('inventory.notes'))
                            ->placeholder(__('inventory.no_notes'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                InfolistSection::make(__('inventory.timestamps'))
                    ->schema([
                        TextEntry::make('last_restocked_at')
                            ->label(__('inventory.last_restocked_at'))
                            ->dateTime()
                            ->placeholder(__('inventory.never')),
                        TextEntry::make('last_sold_at')
                            ->label(__('inventory.last_sold_at'))
                            ->dateTime()
                            ->placeholder(__('inventory.never')),
                        TextEntry::make('created_at')
                            ->label(__('inventory.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('inventory.updated_at'))
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
                InfolistSection::make(__('inventory.recent_movements'))
                    ->schema([
                        RepeatableEntry::make('stockMovements')
                            ->label('')
                            ->schema([
                            TextEntry::make('type')
                                ->label(__('inventory.movement_type'))
                                ->badge()
                                ->color(fn (string $state): string => $state === 'in' ? 'success' : 'danger')
                                ->formatStateUsing(fn (string $state): string => __('inventory.'.$state)),
                            TextEntry::make('quantity')
                                ->label(__('inventory.quantity'))
                                ->badge()
                                ->color(fn (int $state, $record): string => $record->type === 'in' ? 'success' : 'danger'),
                            TextEntry::make('reason')
                                ->label(__('inventory.reason'))
                                ->badge()
                                ->color('info'),
                            TextEntry::make('user.name')
                                ->label(__('inventory.user'))
                                ->badge()
                                ->color('gray'),
                            TextEntry::make('moved_at')
                                ->label(__('inventory.moved_at'))
                                ->dateTime(),
                            ])
                            ->columns(5)
                            ->limit(10),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StockMovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'view' => Pages\ViewStock::route('/{record}'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->with(['variant.product', 'location', 'supplier', 'stockMovements.user']);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['variant.product', 'location']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'variant.product.name',
            'variant.display_name',
            'variant.sku',
            'location.name',
            'supplier.name',
            'batch_number',
            'notes',
        ];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('inventory.product') => $record->variant->product->name,
            __('inventory.location') => $record->location->name,
            __('inventory.stock') => $record->stock,
            __('inventory.status') => $record->stock_status_label,
        ];
    }
}
