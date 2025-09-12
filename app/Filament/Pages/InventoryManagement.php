<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class InventoryManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';
    protected static ?string $navigationLabel = 'Inventory Management';
    protected static ?string $title = 'Inventory Management';
    protected static ?string $slug = 'inventory-management';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.inventory-management';

    public function getNavigationGroup(): ?string
    {
        return __('navigation.groups.catalog');
    }

    public function table(Table $table): Table
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
                    ->color(fn(Product $record) => match($record->getStockStatus()) {
                        'in_stock' => 'success',
                        'low_stock' => 'warning',
                        'out_of_stock' => 'danger',
                        'not_tracked' => 'gray',
                        default => 'primary',
                    }),
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
                    ->color(fn(Product $record) => match($record->getStockStatus()) {
                        'in_stock' => 'success',
                        'low_stock' => 'warning',
                        'out_of_stock' => 'danger',
                        'not_tracked' => 'gray',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn(Product $record) => match($record->getStockStatus()) {
                        'in_stock' => __('In Stock'),
                        'low_stock' => __('Low Stock'),
                        'out_of_stock' => __('Out of Stock'),
                        'not_tracked' => __('Not Tracked'),
                        default => __('Unknown'),
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Last Updated'))
                    ->date('Y-m-d H:i')
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
                                ->whereRaw('stock_quantity > low_stock_threshold'),
                            'low_stock' => $query
                                ->where('manage_stock', true)
                                ->where('stock_quantity', '>', 0)
                                ->whereRaw('stock_quantity <= low_stock_threshold'),
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
            ->actions([
                Tables\Actions\Action::make('adjust_stock')
                    ->label(__('Adjust Stock'))
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('Adjustment Quantity'))
                            ->numeric()
                            ->required()
                            ->helperText(__('Positive to add stock, negative to remove')),
                        Forms\Components\Select::make('reason')
                            ->label(__('Reason'))
                            ->options([
                                'restock' => __('Restock'),
                                'damaged' => __('Damaged'),
                                'lost' => __('Lost'),
                                'returned' => __('Returned'),
                                'correction' => __('Correction'),
                                'promotion' => __('Promotion'),
                                'other' => __('Other'),
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(3),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $inventoryService = app(InventoryService::class);
                        $inventoryService->adjustProductStock(
                            $record,
                            (int) $data['quantity'],
                            $data['reason'],
                            $data['notes'] ?? null
                        );
                    })
                    ->requiresConfirmation()
                    ->modalDescription(__('Adjust stock quantity for this product')),
                Tables\Actions\Action::make('quick_restock')
                    ->label(__('Quick Restock'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label(__('Add Quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(10),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('Notes'))
                            ->rows(2),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $inventoryService = app(InventoryService::class);
                        $inventoryService->adjustProductStock(
                            $record,
                            (int) $data['quantity'],
                            'restock',
                            $data['notes'] ?? null
                        );
                    })
                    ->requiresConfirmation()
                    ->modalDescription(__('Add stock to this product')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_restock')
                        ->label(__('Bulk Restock'))
                        ->icon('heroicon-o-plus-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('quantity')
                                ->label(__('Add Quantity'))
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(10),
                            Forms\Components\Select::make('reason')
                                ->label(__('Reason'))
                                ->options([
                                    'restock' => __('Restock'),
                                    'correction' => __('Correction'),
                                    'promotion' => __('Promotion'),
                                ])
                                ->default('restock'),
                        ])
                        ->action(function (array $data, $records): void {
                            $inventoryService = app(InventoryService::class);
                            foreach ($records as $record) {
                                $inventoryService->adjustProductStock(
                                    $record,
                                    (int) $data['quantity'],
                                    $data['reason']
                                );
                            }
                        })
                        ->requiresConfirmation()
                        ->modalDescription(__('Add stock to selected products')),
                ]),
            ])
            ->poll('30s')
            ->striped();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('inventory_summary')
                ->label(__('Inventory Summary'))
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalContent(function (): string {
                    $inventoryService = app(InventoryService::class);
                    $summary = $inventoryService->getInventorySummary();
                    
                    return view('filament.pages.inventory-summary', compact('summary'))->render();
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(__('Close')),
        ];
    }
}