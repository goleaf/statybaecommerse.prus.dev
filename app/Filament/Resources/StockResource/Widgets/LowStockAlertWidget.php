<?php declare(strict_types=1);

namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\VariantInventory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Support\Enums\FontWeight;

class LowStockAlertWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'inventory.low_stock_alerts';

    public function getHeading(): string
    {
        return __('inventory.low_stock_alerts');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                VariantInventory::query()
                    ->with(['variant.product', 'location', 'supplier'])
                    ->lowStock()
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('variant.product.name')
                    ->label(__('inventory.product'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->limit(30),

                TextColumn::make('variant.display_name')
                    ->label(__('inventory.variant'))
                    ->searchable()
                    ->sortable()
                    ->limit(25),

                TextColumn::make('location.name')
                    ->label(__('inventory.location'))
                    ->badge()
                    ->color('info'),

                TextColumn::make('stock')
                    ->label(__('inventory.current_stock'))
                    ->sortable()
                    ->alignEnd()
                    ->weight(FontWeight::Bold)
                    ->color('danger'),

                TextColumn::make('threshold')
                    ->label(__('inventory.threshold'))
                    ->sortable()
                    ->alignEnd()
                    ->color('warning'),

                TextColumn::make('available_stock')
                    ->label(__('inventory.available'))
                    ->sortable()
                    ->alignEnd()
                    ->weight(FontWeight::Bold)
                    ->color('danger'),

                BadgeColumn::make('stock_status')
                    ->label(__('inventory.status'))
                    ->colors([
                        'warning' => 'low_stock',
                        'danger' => 'out_of_stock',
                        'info' => 'needs_reorder',
                    ])
                    ->formatStateUsing(fn (string $state): string => 
                        __('inventory.' . $state)
                    ),

                TextColumn::make('supplier.name')
                    ->label(__('inventory.supplier'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->placeholder(__('inventory.no_supplier')),
            ])
            ->actions([
                Action::make('restock')
                    ->label(__('inventory.restock'))
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('quantity')
                            ->label(__('inventory.restock_quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        
                        \Filament\Forms\Components\Select::make('supplier_id')
                            ->label(__('inventory.supplier'))
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload(),
                        
                        \Filament\Forms\Components\TextInput::make('cost_per_unit')
                            ->label(__('inventory.cost_per_unit'))
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        
                        \Filament\Forms\Components\TextInput::make('batch_number')
                            ->label(__('inventory.batch_number')),
                        
                        \Filament\Forms\Components\DatePicker::make('expiry_date')
                            ->label(__('inventory.expiry_date')),
                        
                        \Filament\Forms\Components\Textarea::make('notes')
                            ->label(__('inventory.notes'))
                            ->rows(3),
                    ])
                    ->action(function (VariantInventory $record, array $data): void {
                        // Update stock
                        $record->increment('stock', $data['quantity']);
                        
                        // Update cost per unit if provided
                        if (isset($data['cost_per_unit'])) {
                            $record->update(['cost_per_unit' => $data['cost_per_unit']]);
                        }
                        
                        // Update supplier if provided
                        if (isset($data['supplier_id'])) {
                            $record->update(['supplier_id' => $data['supplier_id']]);
                        }
                        
                        // Update batch number and expiry date if provided
                        if (isset($data['batch_number'])) {
                            $record->update(['batch_number' => $data['batch_number']]);
                        }
                        
                        if (isset($data['expiry_date'])) {
                            $record->update(['expiry_date' => $data['expiry_date']]);
                        }
                        
                        // Create stock movement
                        $record->stockMovements()->create([
                            'quantity' => $data['quantity'],
                            'type' => 'in',
                            'reason' => 'restock',
                            'reference' => 'restock_' . now()->format('Y-m-d_H-i-s'),
                            'notes' => $data['notes'] ?? __('inventory.restock_notes'),
                            'user_id' => auth()->id(),
                            'moved_at' => now(),
                        ]);
                        
                        // Update last restocked timestamp
                        $record->update(['last_restocked_at' => now()]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title(__('inventory.restock_successful'))
                            ->body(__('inventory.restock_successful_message', [
                                'quantity' => $data['quantity'],
                                'product' => $record->display_name
                            ]))
                            ->success()
                            ->send();
                    }),

                Action::make('view')
                    ->label(__('inventory.view_details'))
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (VariantInventory $record): string => 
                        \App\Filament\Resources\StockResource::getUrl('view', ['record' => $record])
                    ),
            ])
            ->defaultSort('stock', 'asc')
            ->poll('30s')
            ->deferLoading()
            ->striped();
    }
}
