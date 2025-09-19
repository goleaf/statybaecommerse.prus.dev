<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\StockResource\Pages;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
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
    protected static ?string $model = Stock::class;

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'product_name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('stocks.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Products->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('stocks.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('stocks.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Form $form
     * @return Form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make(__('stocks.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('stocks.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $product = Product::find($state);
                                        if ($product) {
                                            $set('product_name', $product->name);
                                            $set('product_sku', $product->sku);
                                        }
                                    }
                                }),
                            Select::make('product_variant_id')
                                ->label(__('stocks.product_variant'))
                                ->relationship('productVariant', 'name')
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $variant = ProductVariant::find($state);
                                        if ($variant) {
                                            $set('product_name', $variant->name);
                                            $set('product_sku', $variant->sku);
                                        }
                                    }
                                }),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('product_name')
                                ->label(__('stocks.product_name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('product_sku')
                                ->label(__('stocks.product_sku'))
                                ->maxLength(255),
                        ]),
                    Select::make('location_id')
                        ->label(__('stocks.location'))
                        ->relationship('location', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),
            Section::make(__('stocks.stock_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('quantity')
                                ->label(__('stocks.quantity'))
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->default(0),
                            TextInput::make('reserved_quantity')
                                ->label(__('stocks.reserved_quantity'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('available_quantity')
                                ->label(__('stocks.available_quantity'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->disabled(),
                            TextInput::make('low_stock_threshold')
                                ->label(__('stocks.low_stock_threshold'))
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
                        ]),
                ]),
            Section::make(__('stocks.settings'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_active')
                                ->label(__('stocks.is_active'))
                                ->default(true),
                            Toggle::make('track_stock')
                                ->label(__('stocks.track_stock'))
                                ->default(true),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('allow_backorder')
                                ->label(__('stocks.allow_backorder'))
                                ->default(false),
                            Toggle::make('manage_stock')
                                ->label(__('stocks.manage_stock'))
                                ->default(true),
                        ]),
                    Textarea::make('notes')
                        ->label(__('stocks.notes'))
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),
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
                TextColumn::make('product_name')
                    ->label(__('stocks.product_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                TextColumn::make('product_sku')
                    ->label(__('stocks.product_sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('location.name')
                    ->label(__('stocks.location'))
                    ->sortable()
                    ->badge()
                    ->color('blue'),
                TextColumn::make('quantity')
                    ->label(__('stocks.quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->weight('bold'),
                TextColumn::make('reserved_quantity')
                    ->label(__('stocks.reserved_quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('available_quantity')
                    ->label(__('stocks.available_quantity'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn($state): string => $state <= 0 ? 'danger' : ($state <= 10 ? 'warning' : 'success')),
                TextColumn::make('low_stock_threshold')
                    ->label(__('stocks.low_stock_threshold'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('stocks.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('track_stock')
                    ->label(__('stocks.track_stock'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('allow_backorder')
                    ->label(__('stocks.allow_backorder'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('manage_stock')
                    ->label(__('stocks.manage_stock'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('stocks.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('stocks.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('stocks.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('location_id')
                    ->label(__('stocks.location'))
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label(__('stocks.is_active'))
                    ->boolean()
                    ->trueLabel(__('stocks.active_only'))
                    ->falseLabel(__('stocks.inactive_only'))
                    ->native(false),
                TernaryFilter::make('track_stock')
                    ->label(__('stocks.track_stock'))
                    ->boolean()
                    ->trueLabel(__('stocks.tracked_only'))
                    ->falseLabel(__('stocks.not_tracked'))
                    ->native(false),
                TernaryFilter::make('allow_backorder')
                    ->label(__('stocks.allow_backorder'))
                    ->boolean()
                    ->trueLabel(__('stocks.backorder_allowed'))
                    ->falseLabel(__('stocks.backorder_not_allowed'))
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('adjust_stock')
                    ->label(__('stocks.adjust_stock'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('info')
                    ->form([
                        TextInput::make('adjustment_quantity')
                            ->label(__('stocks.adjustment_quantity'))
                            ->numeric()
                            ->required()
                            ->helperText(__('stocks.adjustment_quantity_help')),
                        Textarea::make('adjustment_reason')
                            ->label(__('stocks.adjustment_reason'))
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (Stock $record, array $data): void {
                        $record->update([
                            'quantity' => $record->quantity + $data['adjustment_quantity'],
                        ]);

                        Notification::make()
                            ->title(__('stocks.stock_adjusted_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('toggle_active')
                    ->label(fn(Stock $record): string => $record->is_active ? __('stocks.deactivate') : __('stocks.activate'))
                    ->icon(fn(Stock $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn(Stock $record): string => $record->is_active ? 'warning' : 'success')
                    ->action(function (Stock $record): void {
                        $record->update(['is_active' => !$record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? __('stocks.activated_successfully') : __('stocks.deactivated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('activate')
                        ->label(__('stocks.activate_selected'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);

                            Notification::make()
                                ->title(__('stocks.bulk_activated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('deactivate')
                        ->label(__('stocks.deactivate_selected'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);

                            Notification::make()
                                ->title(__('stocks.bulk_deactivated_success'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'view' => Pages\ViewStock::route('/{record}'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }
}
