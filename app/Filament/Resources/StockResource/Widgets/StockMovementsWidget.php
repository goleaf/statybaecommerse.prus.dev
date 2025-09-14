<?php

declare (strict_types=1);
namespace App\Filament\Resources\StockResource\Widgets;

use App\Models\VariantInventory;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
/**
 * StockMovementsWidget
 * 
 * Filament v4 resource for StockMovementsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property VariantInventory|null $record
 * @property int|string|array $columnSpan
 * @property string|null $heading
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class StockMovementsWidget extends BaseWidget
{
    public ?VariantInventory $record = null;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'inventory.recent_stock_movements';
    /**
     * Handle getHeading functionality with proper error handling.
     * @return string
     */
    public function getHeading(): string
    {
        return __('inventory.recent_stock_movements');
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query($this->record?->stockMovements()->with('user')->latest('moved_at')->limit(10) ?? VariantInventory::query()->whereRaw('1 = 0'))->columns([TextColumn::make('moved_at')->label(__('inventory.moved_at'))->dateTime()->sortable()->weight('bold'), BadgeColumn::make('type')->label(__('inventory.movement_type'))->colors(['success' => 'in', 'danger' => 'out'])->formatStateUsing(fn(string $state): string => __('inventory.' . $state)), TextColumn::make('quantity')->label(__('inventory.quantity'))->sortable()->alignEnd()->weight('bold')->color(fn($record): string => $record->type === 'in' ? 'success' : 'danger'), BadgeColumn::make('reason')->label(__('inventory.reason'))->colors(['primary' => 'sale', 'success' => 'return', 'warning' => 'adjustment', 'info' => 'manual_adjustment', 'success' => 'restock', 'danger' => 'damage', 'danger' => 'theft', 'info' => 'transfer'])->formatStateUsing(fn(string $state): string => __('inventory.reason_' . $state)), TextColumn::make('reference')->label(__('inventory.reference'))->searchable()->limit(30)->placeholder(__('inventory.no_reference')), TextColumn::make('user.name')->label(__('inventory.user'))->searchable()->sortable()->badge()->color('gray')->placeholder(__('inventory.system')), TextColumn::make('notes')->label(__('inventory.notes'))->limit(50)->placeholder(__('inventory.no_notes'))->toggleable(isToggledHiddenByDefault: true)])->defaultSort('moved_at', 'desc')->poll('30s')->deferLoading()->striped();
    }
}