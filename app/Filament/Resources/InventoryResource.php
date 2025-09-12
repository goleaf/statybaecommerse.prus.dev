<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Actions\Action as TablesAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

final class InventoryResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cube-transparent';


    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.inventory');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.inventory_management');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.inventory_management');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Product Information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Product Name'))
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('sku')
                            ->label(__('SKU'))
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                Section::make(__('Inventory Management'))
                    ->schema([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label(__('Current Stock'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->helperText(__('Current quantity in stock')),
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->label(__('Low Stock Threshold'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(5)
                            ->helperText(__('Alert when stock falls below this level')),
                        Forms\Components\Toggle::make('manage_stock')
                            ->label(__('Track Inventory'))
                            ->helperText(__('Enable inventory tracking for this product'))
                            ->default(true),
                        Forms\Components\Toggle::make('allow_backorders')
                            ->label(__('Allow Backorders'))
                            ->helperText(__('Allow orders when out of stock'))
                            ->default(false),
                    ])
                    ->columns(2),
                Section::make(__('Stock Adjustments'))
                    ->schema([
                        Forms\Components\TextInput::make('adjustment_quantity')
                            ->label(__('Adjustment Quantity'))
                            ->numeric()
                            ->helperText(__('Positive to add stock, negative to remove')),
                        Forms\Components\Select::make('adjustment_reason')
                            ->label(__('Adjustment Reason'))
                            ->options([
                                'restock' => __('Restock'),
                                'damaged' => __('Damaged'),
                                'lost' => __('Lost'),
                                'returned' => __('Returned'),
                                'correction' => __('Correction'),
                                'promotion' => __('Promotion'),
                                'other' => __('Other'),
                            ])
                            ->helperText(__('Reason for stock adjustment')),
                        Forms\Components\Textarea::make('adjustment_notes')
                            ->label(__('Adjustment Notes'))
                            ->rows(3)
                            ->helperText(__('Optional notes about this adjustment'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->hidden(fn(?Product $record) => !$record),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['brand', 'categories'])
                    ->where('is_visible', true)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('Image'))
                    ->getStateUsing(fn(Product $record) => $record->getFirstMediaUrl('images', 'thumb'))
                    ->defaultImageUrl(asset('images/placeholder-product.png'))
                    ->circular()
                    ->size(60),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Product'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                Tables\Columns\TextColumn::make('sku')
                    ->label(__('SKU'))
                    ->searchable()
                    ->copyable()
                    ->weight('mono'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->label(__('Brand'))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('Stock'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('low_stock_threshold')
                    ->label(__('Threshold'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('manage_stock')
                    ->label(__('Tracked'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stock_status')
                    ->label(__('Status'))
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('stock_status')
                    ->label(__('Stock Status'))
                    ->options([
                        'in_stock' => __('In Stock'),
                        'low_stock' => __('Low Stock'),
                        'out_of_stock' => __('Out of Stock'),
                        'not_tracked' => __('Not Tracked'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'in_stock' => $query
                                ->where('manage_stock', true)
                                ->where('stock_quantity', '>', DB::raw('low_stock_threshold')),
                            'low_stock' => $query
                                ->where('manage_stock', true)
                                ->where('stock_quantity', '>', 0)
                                ->where('stock_quantity', '<=', DB::raw('low_stock_threshold')),
                            'out_of_stock' => $query
                                ->where('manage_stock', true)
                                ->where('stock_quantity', '<=', 0),
                            'not_tracked' => $query->where('manage_stock', false),
                            default => $query,
                        };
                    }),
                Tables\Filters\SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('manage_stock')
                    ->label(__('Track Inventory'))
                    ->placeholder(__('All'))
                    ->trueLabel(__('Tracked'))
                    ->falseLabel(__('Not Tracked')),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('Adjust Stock')),
                TablesAction::make('quick_restock')
                    ->label(__('Quick Restock'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('Add Quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(2),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $record->increment('stock_quantity', $data['quantity']);

                        // Log the adjustment (if you have an inventory history model)
                        // InventoryHistory::create([
                        //     'product_id' => $record->id,
                        //     'type' => 'adjustment',
                        //     'quantity' => $data['quantity'],
                        //     'reason' => 'restock',
                        //     'notes' => $data['notes'] ?? null,
                        //     'user_id' => auth()->id(),
                        // ]);
                    })
                    ->requiresConfirmation()
                    ->modalDescription(__('Add stock to this product'))
                    ->successNotificationTitle(__('Stock updated successfully')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_restock')
                        ->label(__('Bulk Restock'))
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->label(__('Add Quantity'))
                                ->numeric()
                                ->required()
                                ->minValue(1),
                        ])
                        ->action(function (array $data, $records): void {
                            foreach ($records as $record) {
                                $record->increment('stock_quantity', $data['quantity']);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalDescription(__('Add stock to selected products')),
                ]),
            ])
            ->poll('60s')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return __('Inventory Management');
    }
}
