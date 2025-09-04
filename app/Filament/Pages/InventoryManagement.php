<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class InventoryManagement extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cube-transparent';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';
    protected static ?int $navigationSort = 10;

    public ?string $stockFilter = 'all';

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.inventory_management');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with(['brand', 'media', 'variants'])
                    ->withCount('variants')
                    ->when($this->stockFilter === 'low', fn (Builder $query) => $query->where('stock_quantity', '<=', 10))
                    ->when($this->stockFilter === 'out', fn (Builder $query) => $query->where('stock_quantity', '<=', 0))
                    ->when($this->stockFilter === 'good', fn (Builder $query) => $query->where('stock_quantity', '>', 10))
            )
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('media')
                    ->label('')
                    ->collection('images')
                    ->conversion('thumb')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('sku')
                    ->label(__('admin.table.sku'))
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label(__('admin.table.brand'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('admin.table.current_stock'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        $state <= 50 => 'info',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('low_stock_threshold')
                    ->label(__('admin.table.threshold'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('variants_count')
                    ->label(__('admin.table.variants'))
                    ->numeric()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('track_inventory')
                    ->label(__('admin.table.tracked'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.table.last_updated'))
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('stock_filter')
                    ->label(__('admin.actions.filter_stock'))
                    ->icon('heroicon-o-funnel')
                    ->form([
                        Forms\Components\Select::make('stock_filter')
                            ->label(__('admin.fields.stock_filter'))
                            ->options([
                                'all' => __('admin.stock_filters.all'),
                                'good' => __('admin.stock_filters.good_stock'),
                                'low' => __('admin.stock_filters.low_stock'),
                                'out' => __('admin.stock_filters.out_of_stock'),
                            ])
                            ->default($this->stockFilter),
                    ])
                    ->action(function (array $data): void {
                        $this->stockFilter = $data['stock_filter'];
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('update_stock')
                    ->label(__('admin.actions.update_stock'))
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label(__('admin.fields.stock_quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(0),
                            
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->label(__('admin.fields.low_stock_threshold'))
                            ->numeric()
                            ->minValue(0),
                            
                        Forms\Components\Textarea::make('note')
                            ->label(__('admin.fields.inventory_note'))
                            ->maxLength(500),
                    ])
                    ->fillForm(fn (Product $record): array => [
                        'stock_quantity' => $record->stock_quantity,
                        'low_stock_threshold' => $record->low_stock_threshold,
                    ])
                    ->action(function (array $data, Product $record): void {
                        $record->update([
                            'stock_quantity' => $data['stock_quantity'],
                            'low_stock_threshold' => $data['low_stock_threshold'] ?? $record->low_stock_threshold,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title(__('admin.notifications.stock_updated'))
                            ->body(__('admin.notifications.stock_updated_for', ['name' => $record->name]))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('view_variants')
                    ->label(__('admin.actions.view_variants'))
                    ->icon('heroicon-o-squares-2x2')
                    ->color('info')
                    ->visible(fn (Product $record): bool => $record->variants_count > 0)
                    ->url(fn (Product $record): string => 
                        route('filament.admin.resources.products.view', ['record' => $record, 'activeTab' => 'variants'])
                    )
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_stock_update')
                        ->label(__('admin.actions.bulk_stock_update'))
                        ->icon('heroicon-m-pencil-square')
                        ->form([
                            Forms\Components\Select::make('operation')
                                ->label(__('admin.fields.operation'))
                                ->options([
                                    'set' => __('admin.stock_operations.set_to'),
                                    'increase' => __('admin.stock_operations.increase_by'),
                                    'decrease' => __('admin.stock_operations.decrease_by'),
                                ])
                                ->required(),
                                
                            Forms\Components\TextInput::make('quantity')
                                ->label(__('admin.fields.quantity'))
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function (array $data, $records): void {
                            foreach ($records as $record) {
                                $newStock = match ($data['operation']) {
                                    'set' => $data['quantity'],
                                    'increase' => $record->stock_quantity + $data['quantity'],
                                    'decrease' => max(0, $record->stock_quantity - $data['quantity']),
                                };
                                
                                $record->update(['stock_quantity' => $newStock]);
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('admin.notifications.bulk_stock_updated'))
                                ->body(__('admin.notifications.updated_items', ['count' => count($records)]))
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('enable_tracking')
                        ->label(__('admin.actions.enable_tracking'))
                        ->icon('heroicon-m-eye')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn($record) => $record->update(['track_inventory' => true]))),

                    Tables\Actions\BulkAction::make('disable_tracking')
                        ->label(__('admin.actions.disable_tracking'))
                        ->icon('heroicon-m-eye-slash')
                        ->color('danger')
                        ->action(fn ($records) => $records->each(fn($record) => $record->update(['track_inventory' => false]))),
                ]),
            ])
            ->defaultSort('stock_quantity', 'asc');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_inventory')
                ->label(__('admin.actions.export_inventory'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (): void {
                    // Export inventory to CSV
                    $products = Product::with('brand')->get();
                    $csv = "Name,SKU,Brand,Stock,Threshold,Price\n";
                    
                    foreach ($products as $product) {
                        $csv .= sprintf(
                            '"%s","%s","%s",%d,%d,%.2f' . "\n",
                            $product->name,
                            $product->sku,
                            $product->brand?->name ?? 'N/A',
                            $product->stock_quantity,
                            $product->low_stock_threshold,
                            $product->price
                        );
                    }
                    
                    $filename = 'inventory-' . now()->format('Y-m-d-H-i-s') . '.csv';
                    \Storage::disk('public')->put('exports/' . $filename, $csv);
                    
                    \Filament\Notifications\Notification::make()
                        ->title(__('admin.notifications.inventory_exported'))
                        ->body(__('admin.notifications.file_saved', ['filename' => $filename]))
                        ->success()
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('download')
                                ->label(__('admin.actions.download'))
                                ->url(asset('storage/exports/' . $filename))
                                ->openUrlInNewTab(),
                        ])
                        ->send();
                }),

            Action::make('low_stock_alert')
                ->label(__('admin.actions.low_stock_alert'))
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->action(function (): void {
                    $lowStockCount = Product::where('stock_quantity', '<=', 10)->count();
                    
                    \Filament\Notifications\Notification::make()
                        ->title(__('admin.notifications.low_stock_check'))
                        ->body(__('admin.notifications.low_stock_items', ['count' => $lowStockCount]))
                        ->warning()
                        ->send();
                }),
        ];
    }

    public function clearCache(): void
    {
        \Artisan::call('optimize:clear');
        $this->loadSystemStats();
        
        \Filament\Notifications\Notification::make()
            ->title(__('admin.notifications.cache_cleared'))
            ->success()
            ->send();
    }

    public function optimizeSystem(): void
    {
        \Artisan::call('optimize');
        $this->loadSystemStats();
        
        \Filament\Notifications\Notification::make()
            ->title(__('admin.notifications.system_optimized'))
            ->success()
            ->send();
    }
}
