<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

/**
 * InventoryResource
 *
 * Filament v4 resource for Inventory management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'product.name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.inventory.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Inventory';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.inventory.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.inventory.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('inventory_tabs')
                ->tabs([
                    Tab::make(__('admin.inventory.form.tabs.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('admin.inventory.form.sections.basic_information'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('product_id')
                                                ->label(__('admin.inventory.form.fields.product'))
                                                ->relationship('product', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->sku})")
                                                ->columnSpan(1),
                                            Select::make('location_id')
                                                ->label(__('admin.inventory.form.fields.location'))
                                                ->relationship('location', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->code})")
                                                ->columnSpan(1),
                                        ]),
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('quantity')
                                                ->label(__('admin.inventory.form.fields.quantity'))
                                                ->numeric()
                                                ->default(0)
                                                ->required()
                                                ->minValue(0)
                                                ->columnSpan(1),
                                            TextInput::make('reserved')
                                                ->label(__('admin.inventory.form.fields.reserved'))
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0)
                                                ->columnSpan(1),
                                            TextInput::make('incoming')
                                                ->label(__('admin.inventory.form.fields.incoming'))
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0)
                                                ->columnSpan(1),
                                            TextInput::make('threshold')
                                                ->label(__('admin.inventory.form.fields.threshold'))
                                                ->numeric()
                                                ->default(10)
                                                ->required()
                                                ->minValue(0)
                                                ->columnSpan(1),
                                            Toggle::make('is_tracked')
                                                ->label(__('admin.inventory.form.fields.is_tracked'))
                                                ->default(true)
                                                ->columnSpan(2),
                                        ])
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('admin.inventory.form.tabs.product_details'))
                        ->icon('heroicon-o-cube')
                        ->schema([
                            Section::make(__('admin.inventory.form.sections.product_details'))
                                ->schema([
                                    Placeholder::make('product_name')
                                        ->label(__('admin.inventory.form.fields.product_name'))
                                        ->content(fn($record) => $record?->product?->name ?? '-'),
                                    Placeholder::make('product_sku')
                                        ->label(__('admin.inventory.form.fields.product_sku'))
                                        ->content(fn($record) => $record?->product?->sku ?? '-'),
                                    Placeholder::make('product_price')
                                        ->label(__('admin.inventory.form.fields.product_price'))
                                        ->content(fn($record) => $record?->product ? '€' . number_format($record->product->price, 2) : '-'),
                                    Placeholder::make('product_stock_status')
                                        ->label(__('admin.inventory.form.fields.product_stock_status'))
                                        ->content(fn($record) => $record?->product?->stock_status ?? '-'),
                                ])
                                ->columns(2),
                        ]),
                    Tab::make(__('admin.inventory.form.tabs.location_details'))
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Section::make(__('admin.inventory.form.sections.location_details'))
                                ->schema([
                                    Placeholder::make('location_name')
                                        ->label(__('admin.inventory.form.fields.location_name'))
                                        ->content(fn($record) => $record?->location?->name ?? '-'),
                                    Placeholder::make('location_code')
                                        ->label(__('admin.inventory.form.fields.location_code'))
                                        ->content(fn($record) => $record?->location?->code ?? '-'),
                                    Placeholder::make('location_address')
                                        ->label(__('admin.inventory.form.fields.location_address'))
                                        ->content(fn($record) => $record?->location?->address_line_1 ?? '-'),
                                    Placeholder::make('location_city')
                                        ->label(__('admin.inventory.form.fields.location_city'))
                                        ->content(fn($record) => $record?->location?->city ?? '-'),
                                ])
                                ->columns(2),
                        ]),
                    Tab::make(__('admin.inventory.form.tabs.stock_analysis'))
                        ->icon('heroicon-o-chart-bar')
                        ->schema([
                            Section::make(__('admin.inventory.form.sections.stock_analysis'))
                                ->schema([
                                    Placeholder::make('available_quantity')
                                        ->label(__('admin.inventory.form.fields.available_quantity'))
                                        ->content(fn($record) => $record?->available_quantity ?? 0),
                                    Placeholder::make('stock_status')
                                        ->label(__('admin.inventory.form.fields.stock_status'))
                                        ->content(fn($record) => match (true) {
                                            $record?->isOutOfStock() => __('admin.inventory.stock_status.out_of_stock'),
                                            $record?->isLowStock() => __('admin.inventory.stock_status.low_stock'),
                                            default => __('admin.inventory.stock_status.in_stock'),
                                        }),
                                    Placeholder::make('stock_ratio')
                                        ->label(__('admin.inventory.form.fields.stock_ratio'))
                                        ->content(fn($record) => $record
                                            ? round(($record->available_quantity / max($record->threshold, 1)) * 100, 1) . '%'
                                            : '0%'),
                                ])
                                ->columns(3),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('admin.inventory.form.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->product?->sku ?? ''),
                TextColumn::make('location.name')
                    ->label(__('admin.inventory.form.fields.location'))
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->location?->code ?? ''),
                TextColumn::make('quantity')
                    ->label(__('admin.inventory.form.fields.quantity'))
                    ->numeric()
                    ->sortable()
                    ->color(fn($record) => match (true) {
                        $record->quantity <= 0 => 'danger',
                        $record->quantity <= $record->threshold => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('reserved')
                    ->label(__('admin.inventory.form.fields.reserved'))
                    ->numeric()
                    ->sortable()
                    ->color('warning'),
                TextColumn::make('incoming')
                    ->label(__('admin.inventory.form.fields.incoming'))
                    ->numeric()
                    ->sortable()
                    ->color('info'),
                TextColumn::make('available_quantity')
                    ->label(__('admin.inventory.form.fields.available_quantity'))
                    ->getStateUsing(fn($record) => $record->available_quantity)
                    ->numeric()
                    ->sortable()
                    ->color(fn($record) => match (true) {
                        $record->available_quantity <= 0 => 'danger',
                        $record->available_quantity <= $record->threshold => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('threshold')
                    ->label(__('admin.inventory.form.fields.threshold'))
                    ->numeric()
                    ->sortable(),
                BadgeColumn::make('stock_status')
                    ->label(__('admin.inventory.form.fields.stock_status'))
                    ->getStateUsing(function (Inventory $record): string {
                        $available = $record->quantity - $record->reserved;
                        if ($available <= 0)
                            return 'out_of_stock';
                        if ($available <= $record->threshold)
                            return 'low_stock';
                        return 'in_stock';
                    })
                    ->colors([
                        'success' => 'in_stock',
                        'warning' => 'low_stock',
                        'danger' => 'out_of_stock',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in_stock' => __('admin.inventory.stock_status.in_stock'),
                        'low_stock' => __('admin.inventory.stock_status.low_stock'),
                        'out_of_stock' => __('admin.inventory.stock_status.out_of_stock'),
                        default => $state,
                    }),
                IconColumn::make('is_tracked')
                    ->label(__('admin.inventory.form.fields.is_tracked'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('admin.inventory.form.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('location')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_tracked')
                    ->label(__('admin.inventory.form.fields.is_tracked')),
                SelectFilter::make('stock_status')
                    ->options([
                        'in_stock' => __('admin.inventory.stock_status.in_stock'),
                        'low_stock' => __('admin.inventory.stock_status.low_stock'),
                        'out_of_stock' => __('admin.inventory.stock_status.out_of_stock'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            function (Builder $query, $value): Builder {
                                return match ($value) {
                                    'out_of_stock' => $query->whereRaw('quantity - reserved <= 0'),
                                    'low_stock' => $query->whereRaw('quantity - reserved <= threshold AND quantity - reserved > 0'),
                                    'in_stock' => $query->whereRaw('quantity - reserved > threshold'),
                                    default => $query,
                                };
                            }
                        );
                    }),
                Filter::make('low_stock_only')
                    ->label(__('admin.inventory.filters.low_stock_only'))
                    ->query(fn(Builder $query): Builder => $query->whereRaw('quantity - reserved <= threshold AND quantity - reserved > 0')),
                Filter::make('out_of_stock_only')
                    ->label(__('admin.inventory.filters.out_of_stock_only'))
                    ->query(fn(Builder $query): Builder => $query->whereRaw('quantity - reserved <= 0')),
                Filter::make('tracked_only')
                    ->label(__('admin.inventory.filters.tracked_only'))
                    ->query(fn(Builder $query): Builder => $query->where('is_tracked', true)),
                DateFilter::make('created_at')
                    ->label(__('admin.inventory.filters.created_at')),
                Filter::make('quantity_range')
                    ->form([
                        TextInput::make('quantity_from')
                            ->label(__('admin.inventory.filters.quantity_from'))
                            ->numeric(),
                        TextInput::make('quantity_to')
                            ->label(__('admin.inventory.filters.quantity_to'))
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['quantity_from'],
                                fn(Builder $query, $value): Builder => $query->where('quantity', '>=', $value)
                            )
                            ->when(
                                $data['quantity_to'],
                                fn(Builder $query, $value): Builder => $query->where('quantity', '<=', $value)
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                \Filament\Actions\Action::make('adjust_stock')
                    ->label(__('admin.inventory.actions.adjust_stock'))
                    ->icon('heroicon-o-plus-minus')
                    ->color('warning')
                    ->form([
                        TextInput::make('quantity')
                            ->label(__('admin.inventory.form.fields.quantity'))
                            ->numeric()
                            ->required()
                            ->default(fn($record) => $record->quantity),
                        TextInput::make('reserved')
                            ->label(__('admin.inventory.form.fields.reserved'))
                            ->numeric()
                            ->default(fn($record) => $record->reserved),
                        TextInput::make('incoming')
                            ->label(__('admin.inventory.form.fields.incoming'))
                            ->numeric()
                            ->default(fn($record) => $record->incoming),
                        TextInput::make('threshold')
                            ->label(__('admin.inventory.form.fields.threshold'))
                            ->numeric()
                            ->default(fn($record) => $record->threshold),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->update($data);
                        FilamentNotification::make()
                            ->title(__('admin.inventory.stock_adjusted_successfully'))
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\Action::make('add_stock')
                    ->label(__('admin.inventory.actions.add_stock'))
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        TextInput::make('add_quantity')
                            ->label(__('admin.inventory.form.fields.add_quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->increment('quantity', $data['add_quantity']);
                        FilamentNotification::make()
                            ->title(__('admin.inventory.stock_added_successfully'))
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\Action::make('remove_stock')
                    ->label(__('admin.inventory.actions.remove_stock'))
                    ->icon('heroicon-o-minus')
                    ->color('danger')
                    ->form([
                        TextInput::make('remove_quantity')
                            ->label(__('admin.inventory.form.fields.remove_quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(fn($record) => $record->quantity),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->decrement('quantity', $data['remove_quantity']);
                        FilamentNotification::make()
                            ->title(__('admin.inventory.stock_removed_successfully'))
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\Action::make('reserve_stock')
                    ->label(__('admin.inventory.actions.reserve_stock'))
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->form([
                        TextInput::make('reserve_quantity')
                            ->label(__('admin.inventory.form.fields.reserve_quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(fn($record) => $record->available_quantity),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        $record->increment('reserved', $data['reserve_quantity']);
                        FilamentNotification::make()
                            ->title(__('admin.inventory.stock_reserved_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('adjust_stock')
                        ->label(__('admin.inventory.actions.adjust_stock'))
                        ->icon('heroicon-o-plus-minus')
                        ->color('warning')
                        ->form([
                            TextInput::make('quantity')
                                ->label(__('admin.inventory.form.fields.quantity'))
                                ->numeric()
                                ->required(),
                            TextInput::make('reserved')
                                ->label(__('admin.inventory.form.fields.reserved'))
                                ->numeric(),
                            TextInput::make('incoming')
                                ->label(__('admin.inventory.form.fields.incoming'))
                                ->numeric(),
                            TextInput::make('threshold')
                                ->label(__('admin.inventory.form.fields.threshold'))
                                ->numeric(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Inventory $record) use ($data): void {
                                $record->update($data);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.inventory.stock_adjusted_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('add_stock')
                        ->label(__('admin.inventory.actions.add_stock'))
                        ->icon('heroicon-o-plus')
                        ->color('success')
                        ->form([
                            TextInput::make('add_quantity')
                                ->label(__('admin.inventory.form.fields.add_quantity'))
                                ->numeric()
                                ->required()
                                ->minValue(1),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Inventory $record) use ($data): void {
                                $record->increment('quantity', $data['add_quantity']);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.inventory.stock_added_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('toggle_tracking')
                        ->label(__('admin.inventory.actions.toggle_tracking'))
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->form([
                            Toggle::make('is_tracked')
                                ->label(__('admin.inventory.form.fields.is_tracked'))
                                ->default(true),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Inventory $record) use ($data): void {
                                $record->update(['is_tracked' => $data['is_tracked']]);
                            });
                            FilamentNotification::make()
                                ->title(__('admin.inventory.tracking_updated_successfully'))
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'view' => Pages\ViewInventory::route('/{record}'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
